<?php

namespace App\Http\Controllers;

use App\ListKs;
use App\Product;
use Illuminate\Http\Request;

class ListKsController extends Controller
{
    public function getRecommendKs (Request $request) {
        function toFixed($number, $decimals) {
            return number_format($number, $decimals, '.', "");
        }

        $x = (int) $request->count; // Вводимое количество
        $p_id = $request->product_id;

        // Дефолтный KS
        $ks = Product::find($p_id);

        if($ks) {
            $ks = $ks->edin_rascenka;
        } else {
            $ks = 0;
        }

        // X2 / Y2 (возвышенный)
        $ksTop = ListKs::where('product_id', $p_id)
            ->where('count_indicator', '>', $x)
            ->orderBy('count_indicator', 'asc')
            ->orderBy('id', 'desc')
            ->where('active', 1)
            ->first();

        // X1 / Y1 (заниженный)
        $ksBottom = ListKs::where('product_id', $p_id)
            ->where('count_indicator', '<=', $x)
            ->orderBy('count_indicator', 'desc')
            ->orderBy('id', 'desc')
            ->where('active', 1)
            ->first();

        // Случай №1
        if($ksTop && $ksBottom) {

            $x1 = $ksBottom->count_indicator;
            $y1 = $ksBottom->ks_natur;

            $x2 = $ksTop->count_indicator;
            $y2 = $ksTop->ks_natur;

            $ks = $y2 + (($y1-$y2)/($x1-$x2) * ($x - $x2));
            $ks = round($ks, 2);
        } // Случай №2
        else if ($ksTop) {
            $ks = $ksTop->ks_natur;
        }
        else if ($ksBottom) {
            $ks = $ksBottom->ks_natur;
        }

        return response()->json($ks);
    }
}
