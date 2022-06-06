<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SmsController extends Controller
{
    public function sms ($to, $msg) {
        $api_key = '3FEC0ED9-C28B-F43A-8275-47A4A46403C8';

        if($api_key) {
            $body = file_get_contents("https://sms.ru/sms/send?api_id=$api_key&to=$to&msg=".urlencode($msg)."&json=1");

            return $body;
        }

        return '';
    }

    public function SendSmsCode (Request $request) {
        $data = $request->all();

        $msg = 'Ваш код подтверждения: ' . $data['code'];

        return $this->sms($data['to'], $msg);
    }
}

