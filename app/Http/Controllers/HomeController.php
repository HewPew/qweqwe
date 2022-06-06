<?php

namespace App\Http\Controllers;

use App\Anketa;
use App\Exports\AnketasExport;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;



class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public static function searchFieldsAnkets ($anketa, $anketaModel, $fieldsKeys)
    {
        $valueWhere = $anketa[$anketaModel['connectTo']];

        if(isset($anketaModel['connectItemProp'])) {
            $connectItemProp = $anketaModel['connectItemProp'];
            $connectModel = $fieldsKeys[$anketaModel['connectTo']];

            $valueWhere = app($connectModel['model'])
                ->where($connectItemProp['check'], $anketa[$connectModel['connectTo']])
                ->first()[$connectItemProp['get']];
        }

        return app($anketaModel['model'])
            ->where($anketaModel['key'], $valueWhere)
            ->first()[$anketaModel['resultKey']];
    }

    public function SaveCheckedFieldsFilter (Request $request)
    {
        $fields = $request->all();
        $type_ankets = $request->type_ankets;

        unset($fields['_token']);

        session(["fields_$type_ankets" => $fields]);

        return redirect( $_SERVER['HTTP_REFERER'] );
    }

    public function index(Request $request)
    {
        $queryString = '';

        $oKey = 'orderKey';
        $oBy = 'orderBy';

        /**
         * All Query String Without Params
         */
        foreach($_GET as $getK => $getV) {
            if($getK !== $oKey && $getK !== $oBy) {
                $queryString .= '&' . $getK . '=' . (is_array($getV) ? join(',', $getV) : $getV);
            }
        }

        $user = \Auth::user();

        $validTypeAnkets = User::$userRolesKeys[$user->role];
        $typeAnkets = $request->type_ankets;

        $anketasModel = new Anketa();
        $anketas = $anketasModel;

        $take = $request->get('take', 20);
        $orderKey = $request->get($oKey, 'id');
        $orderBy = $request->get($oBy, 'DESC');

        // Если пользователь менее менеджера - то показываем только свои анкеты, заполненные
        if(isset(Anketa::$anketsKeys[$typeAnkets])) {
            $validTypeAnkets = $typeAnkets;
        }

        /**
         * Фильтрация анкет
         */
        $filter_activated = !empty( $request->get('filter') );
        $filter_params = $request->all(); // ИСПРАВИЛ: array_diff($request->all(), array(''))
        $is_export = isset($_GET['export']);
        $trash = $request->get('trash', 0);

        if($is_export) {
            $take = 10000;
        }

        unset($filter_params['trash']);
        unset($filter_params['export']);
        unset($filter_params['filter']);
        unset($filter_params['take']);
        unset($filter_params['orderBy']);
        unset($filter_params['exportTable']);
        unset($filter_params['orderKey']);
        unset($filter_params['page']);
        unset($filter_params['getFormFilter']);

        // Уникальные и независимые поля
        $filterExcept = [
            'TO_created_at' => 'created_at',
            'TO_date' => 'date'
        ];

        // Фильтр
        if(count($filter_params) > 0) {
            foreach($filter_params as $fk => $fv) {
                $is_filter_except = isset($filterExcept[$fk]) ? $filterExcept[$fk] : null;

                if((in_array($fk, $anketasModel->fillable) || $is_filter_except)) {
                    // Поиск по дефолтным полям в таблице Anketas

                    // Проверяем пустые поля
                    if(!empty($fv)) {

                        if($is_filter_except) {
                            // Проверка, одинаковые ли данные
                            $fromFilterValue = $filter_params[$is_filter_except];
                            $isFromEqualToValue = $fromFilterValue === $fv;
                            $fromToValues = [$filter_params[$is_filter_except], $fv];

                            /**
                             * Поправил дату
                             */
                            $anketas = $isFromEqualToValue ?
                                  $anketas->whereDate($is_filter_except, $fromFilterValue)
                                : $anketas->whereRaw("($is_filter_except >= ? AND $is_filter_except <= ?)", [
                                    $fromToValues[0]." 00:00:00",
                                    $fromToValues[1]." 23:59:59"
                                ]);
                        } else if ($fk !== 'date' && $fk !== 'created_at') {
                            if(is_array($fv)) {
                                $anketas = $anketas->where(function ($q) use ($fv, $fk) {

                                    foreach($fv as $fvItemKey => $fvItemValue) {
                                        $q = $q->orWhere($fk, 'LIKE', '%' . $fvItemValue . '%');
                                    }

                                    return $q;
                                });
                            } else {
                                if(strpos($fk, '_id') || $fk === 'id' || $fk === 'id_deal') {
                                    $anketas = $anketas->where($fk, $fv);
                                } else {
                                    $anketas = $anketas->where($fk, 'LIKE', '%' . $fv . '%');
                                }

                            }
                        }

                    }
                } else if (!empty($fv)) {
                    $anketas = $anketas->where($fk, 'LIKE', '%' . $fv . '%');
                }
            }
        }

        $anketas = $anketas->where('type_anketa', $validTypeAnkets)->where('in_cart', $trash);

        // Сотрудник или Клиент
        if($user->role === 0 || $user->role === 1) {
            $anketas = $anketas->where('user_name', $user->name);
        }

        /**
         * </Измеряем количество Авто и Водителей (уникальные ID)>
         */

        $anketas = $anketas->orderBy($orderKey, $orderBy)->paginate($take);
        $anketasCountResult = $anketas->total();

        $fieldsKeys = Anketa::$fieldsKeys[$validTypeAnkets];
        $fieldsGroupFirst = isset(Anketa::$fieldsGroupFirst[$validTypeAnkets]) ? Anketa::$fieldsGroupFirst[$validTypeAnkets] : [];

        $anketsFields = array_keys($fieldsKeys);
        $anketasArray = [];


        /**
         * Если фильтр не активный - то возвращаем все анкеты
         */
        $anketasArray = $anketas;

        /**
         * Доп.параметры
         */
        $total_count_forms = new Anketa();
        $total_count_forms = $total_count_forms->where('type_anketa', $validTypeAnkets)->count();


        /**
         * Check Export
         */
        /*if($is_export) {
            return (new AnketasExport($anketasArray))->download('export-anketas.xlsx');
        }*/

        /**
         * Check VIEW
         */
        $_view = isset($_GET['getFormFilter']) ? 'home_filters' : 'home';

        return view($_view, [
            'title' => Anketa::$anketsKeys[$validTypeAnkets],
            'name' => $user->name,
            'ankets' => $anketasArray,
            'filter_activated' => $filter_activated,
            'type_ankets' => $validTypeAnkets,
            'anketsFields' => $anketsFields,
            'fieldsKeys' => $fieldsKeys,
            'fieldsGroupFirst' => $fieldsGroupFirst,
            'totalCountForms' => $total_count_forms,

            'isExport' => $is_export,

            'anketasCountResult' => $anketasCountResult,

            'currentRole' => $validTypeAnkets,

            'take' => $take,
            'orderBy' => $orderBy,
            'orderKey' => $orderKey,
            'queryString' => $queryString
        ]);
    }
}
