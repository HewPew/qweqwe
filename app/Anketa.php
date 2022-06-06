<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Anketa extends Model
{
    public $fillable = [
        'id',
        'id_deal',
        'date',
        'created_at',
        'address',
        'directions',
        'types',
        'products',
        'name_job',
        'construct_system',
        'lvl',
        'height',
        'count_floors',
        'max_height_floor',
        'count_buildings',

        'sum_ks',
        'sum_kd',
        'kd',
        'kd_final',

        'user_name', 'user_id',
        'in_cart',
        'json_calc',
        'full_data',
        'type_anketa',
        'squere'
    ];

    public static $anketsKeys = [
       'protokol' => 'Реестр калькуляций'
    ];

    public static $fieldsGroupFirst = [ // Группа 1 (показываем сразу в HOME)
        'protokol' => [
            'id_deal' => 'ID сделки',
            'date' => 'Дата создания',
            'address' => 'Адрес объекта',
            'directions' => 'Направления',
            'types' => 'Типы',
            'products' => 'Виды',
            'name_job' => 'Назначение объекта',
            'construct_system' => 'Конструктивная система',
            'lvl' => 'Уровень сложности',
            'height' => 'Высота здания, м',
            'count_floors' => 'Число этажей, вкл. подвал',
            'max_height_floor' => 'Макс. высота этажа',
            'count_buildings' => 'Количество зданий',

            'sum_ks' => 'Сумма (с Ks)',
            'sum_kd' => 'Сумма (с Kd)',
            'kd' => 'Kd',
            'kd_final' => 'Финальное Kd',

            'user_name' => 'Сотрудник',
            'user_id' => 'Клиент'
        ]
    ];

    public static $fieldsKeys = [ // Группа 2 (скрыты по умолчанию)
        'protokol' => [
            'id_deal' => 'ID сделки',
            'date' => 'Дата создания',
            'address' => 'Адрес объекта',
            'directions' => 'Направления',
            'types' => 'Типы',
            'products' => 'Виды',
            'name_job' => 'Назначение объекта',
            'construct_system' => 'Конструктивная система',
            'lvl' => 'Уровень сложности',
            'height' => 'Высота здания, м',
            'count_floors' => 'Число этажей, вкл. подвал',
            'max_height_floor' => 'Макс. высота этажа',
            'count_buildings' => 'Количество зданий',

            'sum_ks' => 'Сумма (с Ks)',
            'sum_kd' => 'Сумма (с Kd)',
            'kd' => 'Kd',
            'kd_final' => 'Финальное Kd',

            'user_name' => 'Сотрудник',
            'user_id' => 'Клиент'
        ]

    ];
}
