<?php

namespace App\Http\Controllers;

use App\Anketa;
use App\Client;
use App\Http\Controllers\Auth\RegisterController;
use App\ListKs;
use App\Mail\SendProtokolMail;
use App\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AnketsController extends Controller
{
    public function Delete (Request $request)
    {
        $id = $request->id;

        if(Anketa::find($id)->delete()) {
            ListKs::where('anketa_id', $id)->delete();

            return redirect($_SERVER['HTTP_REFERER']);
        }

        return abort(403);
    }

    public function SavePdfProtokol (Request $request)
    {
        $data = $request->all();
        $id = $request->id;
        $calc = Anketa::find($id);

        $user = auth()->user();

        $phoneClient = isset($data['phone']) ? (!empty($data['phone']) ? $data['phone'] : '') : '';
        $emailClient = isset($data['email']) ? (!empty($data['email']) ? $data['email'] : '') : '';
        $nameClient = isset($data['fio']) ? (!empty($data['fio']) ? $data['fio'] : '') : '';
        $data['name'] = $nameClient;

        if($emailClient) {
            $data['password'] = Hash::make($phoneClient . '_' . $emailClient);
            $data['api_token'] = sha1(time());

            $clientCreated = User::create($data);

            if($clientCreated->id) {
                $calc->user_id = $clientCreated->id;
                $calc->save();

                $clientCreated->name = $clientCreated->name ? $clientCreated->name : $clientCreated->id;
                $clientCreated->save();
            }
        }

        if($phoneClient) {
            $smsController = new SmsController();

            $uriAnketa = route('forms.print', ['id' => $id, 'not_show_double_calc' => 1]);

            $sms = "Вам отправлена калькуляция на согласование: " . $uriAnketa;

            $smsController->sms($phoneClient, $sms);
        }

        if($id) {
            //$pdf = mb_convert_encoding(view('profile.ankets.anketa_print', $anketa)->render(), 'HTML-ENTITIES', 'UTF-8');
            /*$pdf = "<!DOCTYPE html><html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'/></head><body><style>h1 {font-size: 24px;} h3 {font-size:20px;} p {font-size: 16px;} table {width:100%;}  * { font-family: 'DejaVu Sans'; table, table * {font-size: 14px}, td,th {word-wrap: break-word; padding: 5px 0px;font-size: 14px; text-align: left;background:#ffffff;} }</style> $data[protokolHtml]</html></body>";
            $pdf = PDF::loadHtml($pdf)->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'dpi' => 120,
                'fontHeightRatio' => 0.8,
                'isHtml5ParserEnabled' => true,
                'isFontSubsettingEnabled' => true,
                'isJavascriptEnabled' => true,
                'isPhpEnabled' => true
            ]);
            $output = $pdf->output();
            $uri = 'public/protokols/' . $id . '.pdf';

            Storage::put($uri, $output);
            */

            $email = $emailClient ? $emailClient : $user->email;

            $subject = $calc ? "Калькуляция № $calc->id от $calc->date" : '';

            $uriAnketa = route('forms.print', ['id' => $id, 'not_show_double_calc' => 1, 'not_show_print_link' => 1]);

            if($calc) {
                Mail::send(new SendProtokolMail([
                    'to' => $email,
                    'subject' => $subject,
                    'calc' => $calc,
                    'uriAnketa' => $uriAnketa
                ]));

                return response()->json([
                    'success' => 1
                ]);
            }

            return response()->json([
                'success' => 0
            ]);
        }
    }

    public function PrintExport (Request $request)
    {
        $anketa = Anketa::find($request->id);
        $not_show_double_calc = $request->not_show_double_calc;

        if($anketa) {
            $anketa->title = 'Экспорт/печать калькуляции ID: ' . $anketa->id;
            $anketa->withoutHeader = 1;
            $anketa->not_show_double_calc = $not_show_double_calc;

            return view('profile.ankets.anketa_print', $anketa);
        }

        return abort(404);
    }

    public function GetApiForm (Request $request)
    {
        $id = $request->id;
        $anketa = Anketa::find($id);

        $anketa['json_calc'] = Storage::disk('public')->exists($anketa->json_calc) ? Storage::disk('public')->get($anketa->json_calc) : $anketa->json_calc;
        $anketa['full_data'] = Storage::disk('public')->exists($anketa->full_data) ? Storage::disk('public')->get($anketa->full_data) : $anketa->full_data;

        return response()->json($anketa);
    }

    public function Get (Request $request)
    {
        $id = $request->id;
        $anketa = Anketa::where('id', $id)->first();

        $data = [];

        if(!$anketa) return abort(404);

        foreach ($anketa->fillable as $f) {
            $data[$f] = $anketa[$f];
        }

        // Дефолтные значения
        $data['title'] = 'Редактирование калькуляции';
        $data['default_current_date'] = date('Y-m-d', strtotime($anketa->date)); // date('Y-m-d\TH:i')
        $data['anketa_view'] = 'profile.ankets.' . $anketa->type_anketa;
        $data['anketa_route'] = 'forms.update';
        $data['anketa_id'] = $id;

        return view('profile.anketa', $data);
    }

    public function Update (Request $request)
    {
        $id = $request->id;
        $data = $request->all();

        $fillable = new Anketa();
        $fillable = $fillable->fillable;

        $dataToSave = Anketa::find($id);

        if(!$dataToSave) return abort(404);

        $response = [
            'success' => 0
        ];

        $jsonCalcPath = $dataToSave->json_calc;
        $jsonFullDataPath = $dataToSave->full_data;

        foreach($data as $key => $value) {
            if(in_array($key, $fillable)) {
                if($key !== 'full_data' && $key !== 'json_calc') {
                    $dataToSave[$key] = $value;
                }
            }
        }

        if(Storage::disk('public')->exists($jsonCalcPath)) {
            Storage::disk('public')->put($jsonCalcPath, $data['json_calc']);
        } else {
            $dataToSave['json_calc'] = $data['json_calc'];
        }

        if(Storage::disk('public')->exists($jsonFullDataPath)) {
            Storage::disk('public')->put($jsonFullDataPath, $data['full_data']);
        } else {
            $dataToSave['full_data'] = $data['full_data'];
        }

        $response['success'] = $dataToSave->save();
        $response['data'] = [];

        return $response;
    }

    public function AddForm (Request $request, $isApiRoute = false)
    {
        $data = $request->all();
        $user = $request->user();

        $fillable = new Anketa();
        $fillable = $fillable->fillable;

        $dataToSave = [];
        $response = [
            'success' => 0
        ];

        foreach($data as $key => $value) {
            if(in_array($key, $fillable)) {
                $dataToSave[$key] = $value;
            }
        }
        $dataToSave['json_calc'] = json_encode($data);

        // json save
        $fileJsonCalc = 'calc/' . sha1(time()) . '.json';
        $fileJsonFullData = 'calc/' . sha1($fileJsonCalc) . '.json';

        Storage::disk('public')->put($fileJsonCalc, $dataToSave['json_calc']);
        Storage::disk('public')->put($fileJsonFullData, $dataToSave['full_data']);

        $dataToSave['json_calc'] = $fileJsonCalc;
        $dataToSave['full_data'] = $fileJsonFullData;
        $dataToSave['user_name'] = $user->name;

        $response['success'] = Anketa::create($dataToSave);;

        $response['data'] = $dataToSave;

        return $response;
    }

    /**
     * API ROUTES
     */
    public function ApiAddForm (Request $request)
    {
        $addForm = $this->AddForm($request, true);

        return response()->json($addForm);
    }
}
