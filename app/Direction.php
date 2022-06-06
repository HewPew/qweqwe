<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Direction extends Model
{
    public $fillable = [
        'name'
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
                $newData .= ($dataItemKey !== 0 ? ',' : '') . $dataItem->name;
            }

            $data = $newData;
        }

        return $data;
    }
}
