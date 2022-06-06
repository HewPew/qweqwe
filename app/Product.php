<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public $fillable = [
        'name',
        'type_job_id',
        'num_indicator_id',
        'min_norm_numeric_coef_product',
        'norm_numeric_coef_product', // нормативный силенный показатель вида,
        'edin_rascenka',
        'blocked'
    ];

    public function getName ($id)
    {
        $id = explode(',', $id);

        $data = self::whereIn('id', $id)->get();

        if(!$data) {
            $data = '';
        } else {
            $newData = '';

            foreach($data as $dataItemKey => $dataItem) {
                $newData .= ($dataItemKey !== 0 ? ', ' : '') . $dataItem->name;
            }

            $data = $newData;
        }

        return $data;
    }
}
