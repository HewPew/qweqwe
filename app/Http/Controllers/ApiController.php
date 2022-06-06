<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    // Response
    public static function r($data = [], $action = 1) {
        $response = [];

        switch(strtoupper(trim($action))) {
            case 1: // SUCCESS ROUTE
                $response = ['success' => 1, 'error' => 0, 'data' => $data];
                break;

            case 0: // FAIL ROUTE
                $response = ['success' => 0, 'error' => 1, 'data' => $data];
                break;
        }

        return response()->json($response);
    }

    public function GetFields ($model)
    {
        $model_text = $model;
        $model = app("App\\$model_text");
        $fillable = $model->fillable;

        if($model_text == 'Template') {
            if(auth()->user()->isAdmin()) {
                $model = $model->all();
            } else {
                $model = $model->where('user_id', request()->user()->id)->get();
            }
        } else {
            if(in_array('blocked', $fillable)) {
                $model = $model->where('blocked', 'Нет')->get();
            } else {
                $model = $model->all();
            }
        }

        $data = [];

        foreach ($model as $itemKey => $itemValue) {
            foreach($fillable as $field) {
                if(isset($_GET[$field])) {
                    $value = trim($_GET[$field]);
                    $value = explode(',', $value);

                    $modelData = explode(',', $itemValue->$field);

                    $isValid = 0;

                    foreach($value as $val) {
                        if(in_array($val, $modelData)) {
                           $isValid = 1;
                        }
                    }

                    if($isValid) {
                        array_push($data, $itemValue);
                    }

                }
            }
        }

        if(count($data) > 0) {
            return response()->json($data);
        } else {
            return response()->json($model);
        }
    }

    public function UpdateProperty (Request $request) {
        $item_model = $request->item_model;
        $item_id = $request->item_id;
        $item_field = $request->item_field;

        $new_value = $request->get('new_value');
        $item_model = app("App\\$item_model");
        $item_model = $item_model->find($item_id);

        if($item_model) {
            $item_model[$item_field] = $new_value;

            if($item_model->save()) {
                return ApiController::r(['exists' => true, 'data' => $item_model, 'message' => 'Значение обновлено',0]);
            }
        }

        return ApiController::r(['exists' => false, 'data' => [], 'message' => 'Значение не обновлено',0]);
    }

    // Проверка свойства
    public function CheckProperty (Request $request)
    {
        $prop = $request->prop;
        $model = $request->model;
        $val = $request->val;
        $user = $request->user();

        $models = [
            'Car' => [
                'model' => 'App\Car',
                'fields' => ['hash_id', 'mark_model', 'gos_number', 'company_id']
            ],
            'Driver' => [
                'model' => 'App\Driver',
                'fields' => ['hash_id', 'fio', 'company_id']
            ],
            'Company' => [
                'model' => 'App\Company',
                'fields' => ['id', 'name', 'inn', 'payment_form']
            ]
        ];

        $blockedFields = [
            'old_id', 'photo', 'req_id', 'products_id'
        ];

        /**
         * Блокируем поля если НЕ АДМИН
         */
        if(!$user->hasRole('admin')) {
            array_push($blockedFields,
                'date_bdd', 'date_prmo',
                'date_report_driver',
                'time_card_driver',
                'date_prto', 'date_techview',
                'time_skzi', 'date_osago'
            );
        }

        if(isset($models[$model]) && !empty($val)) {
            $_model = $models[$model];
            $data = app($_model['model']);
            $fields = $data->fillable;
            array_push($fields, 'id');

            $fields = array_filter($fields, function ($item) use ($blockedFields) {
                return !in_array($item, $blockedFields);
            });

            $fieldsValues = new IndexController();
            $fieldsValues = $fieldsValues->elements[$model]['fields'];

            $data = $data->where($prop, $val)->get($fields)->first();

            if(isset($data)) {
                $data_exists = $data->count() > 0;
            } else {
                $data_exists = $data;
            }

            return ApiController::r(['exists' => $data_exists, 'message' => $data,  'fieldsValues' => $fieldsValues], 1);
        }

        return ApiController::r(['exists' => false, 'message' => ''], 0);
    }
}
