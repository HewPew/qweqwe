<?php

namespace App\Http\Controllers;

use App\Anketa;
use App\Car;
use App\Company;
use App\Driver;
use App\Imports\CarImport;
use App\Imports\CompanyImport;
use App\Imports\DriverImport;
use App\ListKs;
use App\Point;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App as FacadesApp;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Excel;
use PhpOffice\PhpSpreadsheet\Writer\Pdf;

class IndexController extends Controller
{
    public $elements = [

        'Product' => [
            'title' => 'Вид работ',
            'role' => 777,
            'popupTitle' => 'Вид работы',
            'editOnField' => 'name',

            'model' => 'Product',
            'fields' => [
                'name' => ['label' => 'Название вида', 'type' => 'text'],
                'type_job_id' => ['label' => 'Тип работ', 'multiple' => 1, 'type' => 'select', 'values' => 'TypeJob'],
                'num_indicator_id' => ['label' => 'Количественный показатель', 'type' => 'select', 'values' => 'NumIndicator'],
                'min_norm_numeric_coef_product' => ['label' => 'Мин. Нормативный количественный показатель Вида', 'type' => 'number', 'step' => '0.0001'],
                'norm_numeric_coef_product' => ['label' => 'Нормативный количественный показатель Вида', 'type' => 'number', 'step' => '0.0001'],
                'edin_rascenka' => ['label' => 'Единичная расценка', 'type' => 'number'],
                'blocked' => ['label' => 'Выключить', 'type' => 'select', 'values' => [
                    'Нет' => 'Нет',
                    'Да' => 'Да'
                ], 'defaultValue' => 'Нет']
            ]
        ],

        'Direction' => [
            'title' => 'Направления деятельности',
            'role' => 777,
            'popupTitle' => 'Направления деятельности',
            'editOnField' => 'name',

            'model' => 'Direction',
            'fields' => [
                'name' => ['label' => 'Название вида', 'type' => 'text']
            ]
        ],

        'ConstructSystem' => [
            'title' => 'Конструктивные системы',
            'role' => 777,
            'popupTitle' => 'Конструктивные системы',
            'editOnField' => 'name',

            'model' => 'ConstructSystem',
            'fields' => [
                'name' => ['label' => 'Название', 'type' => 'text']
            ]
        ],

        'Lvl' => [
            'title' => 'Усложняющие факторы',
            'role' => 777,
            'popupTitle' => 'Усложняющие факторы',
            'editOnField' => 'name',

            'model' => 'Lvl',
            'fields' => [
                'name' => ['label' => 'Название', 'type' => 'text']
            ]
        ],

        'Obj' => [
            'title' => 'Назначение объекта',
            'role' => 777,
            'popupTitle' => 'Назначение объекта',
            'editOnField' => 'name',

            'model' => 'Obj',
            'fields' => [
                'name' => ['label' => 'Название', 'type' => 'text']
            ]
        ],

        'Coef' => [
            'title' => 'Коэффициенты',
            'role' => 777,
            'popupTitle' => 'Коэффициенты',
            'editOnField' => 'name',

            'model' => 'Coef',
            'fields' => [
                'name' => ['label' => 'Название', 'type' => 'text'],
                'numerical' => ['label' => 'Значение', 'step' => '0.1', 'type' => 'number']
            ]
        ],

        'NumIndicator' => [
            'title' => 'Количественные показатели',
            'role' => 777,
            'popupTitle' => 'Количественный показатель',
            'editOnField' => 'name',

            'model' => 'NumIndicator',
            'fields' => [
                'name' => ['label' => 'Название', 'type' => 'text'],
                'unit' => ['label' => 'Единица измерения (русскими буквами, например м2 - "м" русская)', 'type' => 'text'],
                'round_ceil' => ['label' => 'Округлять до целого', 'type' => 'select', 'values' => [
                    1 => 'Да',
                    0 => 'Нет',
                ], 'defaultValue' => 0]
            ]
        ],

        'TypeJob' => [
            'title' => 'Тип работ',
            'role' => 777,
            'popupTitle' => 'Тип работ',
            'editOnField' => 'name',

            'model' => 'TypeJob',
            'fields' => [
                'name' => ['label' => 'Название Типа', 'type' => 'text'],
                'direction_id' => ['label' => 'Направление', 'multiple' => 1, 'type' => 'select', 'values' => 'Direction']
            ]
        ],

        'Template' => [
            'title' => 'Мои Шаблоны',
            'role' => 0,
            'popupTitle' => 'Мои Шаблоны',
            'editOnField' => 'name',
            'otherRoles' => ['expert', 'user'],
            'rolesDelete' => ['expert', 'user'],

            'model' => 'Template',
            'fields' => [
                'name' => ['label' => 'Название', 'type' => 'text'],
                'user_id' => ['label' => 'Пользователь', 'type' => 'select', 'values' => 'User', 'hidden' => 1],
                'data' => ['label' => 'Данные', 'type' => 'text', 'hidden' => 1]
            ]
        ],

        'ListKs' => [
            'title' => 'Реестр ед. расценок',
            'role' => 0,
            'popupTitle' => 'Реестр Ks',
            'editOnField' => 'product_name',

            'model' => 'ListKs',
            'fields' => [
                'anketa_id' => ['label' => 'ID калькуляции', 'type' => 'integer', 'noRequired' => 1],
                'product_id' => ['label' => 'ID вида работ', 'type' => 'integer'],
                'product_name' => ['label' => 'Название вида работ', 'type' => 'text'],
                'count_indicator' => ['label' => 'Количественный показатель', 'type' => 'integer', 'noRequired' => 1],
                //'edin_rascenka' => ['label' => 'Ед. расценка', 'type' => 'integer'],
                'ks_natur' => ['label' => 'Рекомендуемая Ед. расц', 'type' => 'integer', 'noRequired' => 1],

                'name_job' => ['label' => 'Назначение объекта', 'type' => 'text', 'noRequired' => 1],
                'types' => ['label' => 'Название типа работ', 'type' => 'text', 'noRequired' => 1],
                'products' => ['label' => 'Название вида работ', 'type' => 'text', 'noRequired' => 1],
                'construct_system' => ['label' => 'Конструктивная система', 'type' => 'text', 'noRequired' => 1],
                'lvl' => ['label' => 'Уровень сложности', 'type' => 'text', 'noRequired' => 1],
                'height' => ['label' => 'Высота здания', 'type' => 'integer', 'step' => '0.1', 'noRequired' => 1],
                'count_floors' => ['label' => 'Количество этажей', 'type' => 'integer', 'noRequired' => 1],
                'max_height_floor' => ['label' => 'Максимальная высота этажа', 'type' => 'integer', 'step' => '0.1', 'noRequired' => 1],
                'count_buildings' => ['label' => 'Количество зданий', 'type' => 'integer', 'noRequired' => 1],
                'active' => ['label' => 'Активно для алгоритма подбора', 'type' => 'select', 'values' => [
                    1 => 'Да',
                    0 => 'Нет',
                ], 'defaultValue' => 1],
                'user_id' => ['label' => 'Создатель', 'type' => 'select', 'values' => 'User']
            ]
        ]
    ];

    public function GetFieldHTML (Request $request)
    {
        $model = $request->model;
        $default_value = !empty($request->default_value) ? $request->default_value : 'Не установлено';
        $field_key = $request->field;
        $field = $this->elements[$model]['fields'][$field_key];

        if($field) {
            return view('templates.elements_field', [
                'k' => $field_key,
                'v' => $field,
                'is_required' => '',
                'model' => $model,
                'default_value' => $default_value
            ]);
        }

        return 'Поле не найдено';
    }

    public function SyncDataElement (Request $request)
    {
        $fieldFind = $request->fieldFind;
        $model = $request->model;
        $fieldSync = $request->fieldSync;
        $fieldSyncValue = $request->fieldSyncValue;
        $fieldFindId = $request->fieldFindId;

        $model = app("App\\$model");

        if($model) {
            $model = $model->where($fieldFind, $fieldFindId)->update([ $fieldSync => $fieldSyncValue ]);

            if($model) {
                return view('pages.success', [
                    'text' => "Поля успешно синхронизированы. Кол-во элементов: $model"
                ]);
            }
        }

        return abort(500, 'Не найдена модель');
    }

    public function getElements () {
        return $this->elements;
    }

    private $ankets = [
        'protokol' => [
            'title' => 'Ввод данных об объекте',
            'anketa_view' => 'profile.ankets.protokol'
        ],
    ];

    /**
     * POST-запросы
     */

    public function ImportElements (Request $request)
    {
        $model_type = $request->type;
        $file = $request->file('file');

        $objs = [
            'Company' => CompanyImport::class,
            'Driver' => DriverImport::class,
            'Car' => CarImport::class,
            'Town' => ''
        ];

        if($request->hasFile('file')) {
            //$file = $file->getRealPath();
            //print_r($file);

            $path1 = $request->file('file')->store('temp');
            $path= storage_path('app').'/'.$path1;

            $data = \Maatwebsite\Excel\Facades\Excel::import(new $objs[$model_type], $path);
        }

        return redirect($_SERVER['HTTP_REFERER']);
    }

    public function AddElement (Request $request, $isApiRoute = 0)
    {
        $model_type = $request->type;

        $model = app("App\\$model_type");

        if($model) {
            $data = $request->all();

            unset($data['_token']);

            // Парсим файлы
            foreach($request->allFiles() as $file_key => $file) {
                if(isset($data[$file_key]) && !isset($data[$file_key . '_base64'])) {
                    $file_path = Storage::disk('public')->putFile('elements', $file);

                    $data[$file_key] = $file_path;
                }
            }

            // парсим данные
            foreach($data as $dataKey => $dataItem) {
                if(is_array($dataItem)) {
                    $data[$dataKey] = join(',', $dataItem);
                } else if(preg_match('/^data:image\/(\w+);base64,/', $dataItem)) {
                    unset($data[$dataKey]);
                    $dataKey = str_replace('_base64', '', $dataKey);

                    $base64_image = substr($dataItem, strpos($dataItem, ',') + 1);
                    $base64_image = base64_decode($base64_image);

                    $hash = sha1(time());
                    $path = "croppie/$hash.png";
                    $base64_image = Storage::disk('public')->put($path, $base64_image);

                    $data[$dataKey] = $path;
                }
            }

            if($model_type === 'Template') {
                $data['user_id'] = $request->user()->id;
            }

            $createdModel = $model::create($data);

            if($createdModel) {

                if($isApiRoute !== 1) {
                    return redirect( $_SERVER['HTTP_REFERER'] );
                } else {
                    return response()->json($createdModel);
                }

            }

        }
    }

    public function ApiAddElement (Request $request)
    {
        $addElement = $this->AddElement($request, 1);

        return response()->json($addElement);
    }

    public function RemoveElement (Request $request)
    {
        $model = $request->type;
        $id = $request->id;
        $model = app("App\\$model");

        if($model) {
            if($model::find($id)->delete()) {
                return redirect( $_SERVER['HTTP_REFERER'] );
            }
        }
    }

    public function UpdateElement (Request $request, $isApiRoute = 0)
    {
        $model = $request->type;
        $model = app("App\\$model");
        $id = $request->id;

        if($model) {
            $data = $request->all();
            $element = $model->find($id);

            unset($data['_token']);

            // Обновляем данные
            if($element) {
                // Парсим файлы
                foreach($request->allFiles() as $file_key => $file) {
                    if(isset($data[$file_key]) && !isset($data[$file_key . '_base64'])) {
                        $file_path = Storage::disk('public')->putFile('elements', $file);

                        Storage::disk('public')->delete($element[$file_key]);
                        $element[$file_key] = $file_path;
                    }
                }

                foreach($data as $k => $v) {
                    if(is_array($v)) {
                        $element[$k] = join(',', $v);
                    }
                    else if(preg_match('/^data:image\/(\w+);base64,/', $v)) {
                        $k = str_replace('_base64', '', $k);

                        $base64_image = substr($v, strpos($v, ',') + 1);
                        $base64_image = base64_decode($base64_image);

                        $hash = sha1(time());
                        $path = "elements/$hash.png";

                        $base64_image = Storage::disk('public')->put($path, $base64_image);

                        $element->$k = $path;
                    }
                    else {
                        if(isset($v) || $v === '') {
                            $element[$k] = $v;
                        }
                    }
                }
            }

            $savedElem = $element->save();

            if($savedElem && $isApiRoute !== 0) {
                return redirect( $_SERVER['HTTP_REFERER'] );
            } else {
                return response()->json($savedElem);
            }
        }

        return abort(500);
    }

    public function ApiEditListKs (Request $request)
    {
        $anketa_id = $request->anketa_id;
        $product_id = $request->product_id;

        $types = $request->types;
        $data = $request->all();
        $fields = new ListKs();
        $fields = $fields->fillable;
        $response = [];

        $listKs = ListKs::where('product_id', $product_id)
            ->where('types', 'LIKE', "%$types%")
            ->where('anketa_id', $anketa_id)->first();

        if($listKs) {
            foreach($data as $k => $v) {
                if(in_array($k, $fields)) {
                    $listKs[$k] = $v;
                }
            }

            $response = $listKs->save();
        } else {
            return response()->json([
                'success' => false,
                'status_code' => 404
            ]);
        }

        return response()->json([
            'success' => true,
            'status_code' => 200,
            'data' => $response
        ]);
    }

    /**
     * Рендеры страниц
     */
    public function RenderIndex (Request $request)
    {
        $user = Auth::user();

        if(!$user) {
            return view('auth.login');
        }

        return redirect()->route('forms');
    }

    /**
     * Рендер элементов для редактирования, добавления и удаления
     */
    public function RenderElements (Request $request)
    {
        $user = Auth::user();
        $type = $request->type;

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

        /**
         * Сортировка
         */
        $orderKey = $request->get($oKey, 'created_at');
        $orderBy = $request->get($oBy, 'DESC');
        $filter = $request->get('filter', 0);
        $isExport = $request->get('export', 0);

        $take = $request->get('take', 20);

        if($isExport) {
            $take = 10000;
        }

        if(isset($this->elements[$type])) {
            $element = $this->elements[$type];

            $model = $element['model'];
            $MODEL_ELEMENTS = app("App\\$model");
            $element['elements'] = $MODEL_ELEMENTS;
            $fieldsModel = $element['elements']->fillable;

            $element['type'] = $type;
            $element['orderBy'] = $orderBy;
            $element['orderKey'] = $orderKey;
            $element['take'] = $take;

            if($filter) {
                $allFilters = $request->all();
                unset($allFilters['filter']);
                unset($allFilters['take']);
                unset($allFilters['orderBy']);
                unset($allFilters['orderKey']);
                unset($allFilters['page']);
                unset($allFilters['export']);

                foreach($allFilters as $aFk => $aFv) {
                    if(!empty($aFv)) {
                        if(is_array($aFv)) {

                            $element['elements'] = $element['elements']->where(function ($q) use ($aFv, $aFk) {

                                foreach($aFv as $aFvItemKey => $aFvItemValue) {
                                    $q = $q->orWhere($aFk, 'LIKE', '%' . $aFvItemValue . '%');
                                }

                                return $q;
                            });

                        } else {
                            if(strpos($aFk, '_id') || $aFk === 'id') {
                                $element['elements'] = $element['elements']->where($aFk, trim($aFv));
                            } else {
                                $element['elements'] = $element['elements']->where($aFk, 'LIKE', '%' . trim($aFv) . '%');
                            }
                        }
                    }
                }
            }

            if(($model == 'Template') && !$user->isAdmin()) {
                $element['elements'] = $element['elements']->where('user_id', $user->id);
            }

            $element['elements_count_all'] = $MODEL_ELEMENTS->all()->count();
            $element['elements'] = $element['elements']->orderBy($orderKey, $orderBy);
            $element['max'] = isset($element['max']) ? $element['max'] : null;
            $element['isExport'] = $isExport;
            $element['queryString'] = $queryString;

            if($element['max']) {
                $element['elements'] = $element['elements']->take($element['max'])->get();
            } else {
                $element['elements'] = $element['elements']->paginate($take);
            }

            // Проверка прав доступа
            $roles = ['admin', 'manager'];
            if(isset($element['otherRoles'])) {
                foreach($roles as $roleOther) {
                    array_push($element['otherRoles'], $roleOther);
                }
            } else {
                $element['otherRoles'] = $roles;
            }

            // ДОСТУП: УДАЛЕНИЕ
            if(isset($element['rolesDelete'])) {
                foreach($roles as $roleOther) {
                    array_push($element['rolesDelete'], $roleOther);
                }
            } else {
                $element['rolesDelete'] = $roles;
            }

            return view('elements', $element);
        } else {
            return redirect( route('home') );
        }
    }

    /**
     * Рендер последовательного добавления Клиента
     */
    public function RenderAddClient (Request $request)
    {
        return view('pages.add_client', [
            'title' => 'Добавление клиента'
        ]);
    }

    /**
     * Рендер анкет
     */
    public function RenderForms (Request $request)
    {
        $user = Auth::user();

        $type = $request->get('type', 'protokol');
        $anketa_key = $type;

        // Отображаем данные
        $anketa = $this->ankets[$anketa_key];

        $time = time();
        // $time += $user->timezone * 3600; (Вывод текущего времени пользователя)
        $time = date('Y-m-d', $time);

        // Дефолтные значения
        $anketa['default_current_date'] = $time;
        $anketa['type_anketa'] = $anketa_key;

        return view('profile.anketa', $anketa);
    }
}
