<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\RegisterController;
use App\Point;
use App\Req;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public static function getTimeZones () {
        //https://coderoad.ru/4755704/PHP-Timezone-%D1%81%D0%BF%D0%B8%D1%81%D0%BE%D0%BA
        return \DateTimeZone::listIdentifiers();
    }

    /**
     * POST-запросы
     */
    public function CreateUser (Request $request)
    {
        $registerController = new RegisterController();
        $data = $request->all();

        $data_validate = $registerController->validator($data);
        $validate_errors = $data_validate->errors()->messages();

        // Проверяем данные регистрации на ошибки
        if($validate_errors) return $this->ShowUsers($request, $validate_errors);

        // Добавляем и удаляем лишние данные
        $data['api_token'] = sha1($data['password']) . sha1(date('H:i:s'));
        $data['hash_id'] = mt_rand(0,9999) . date('s');
        unset($data['_token']);

        if($request->hasFile('photo') && !isset($data['photo_base64'])) {
            $file_photo = $request->file('photo');
            $file_path = Storage::disk('public')->putFile('avatars', $file_photo);

            $data['photo'] = $file_path;
        }

        if(isset($data['photo_base64'])) {
            $dataKey = 'photo_base64';
            $dataItem = $data[$dataKey];

            if(preg_match('/^data:image\/(\w+);base64,/', $dataItem)) {
                unset($data[$dataKey]);
                $dataKey = str_replace('_base64', '', $dataKey);

                $base64_image = substr($dataItem, strpos($dataItem, ',') + 1);
                $base64_image = base64_decode($base64_image);

                $hash = sha1(time());
                $path = "avatars/$hash.png";
                $base64_image = Storage::disk('public')->put($path, $base64_image);

                $data[$dataKey] = $path;
            }
        }

        // Создаем Пользователя
        $registerController->create($data);

        return redirect($_SERVER['HTTP_REFERER']);
    }

    public function DeleteUser (Request $request)
    {
        $user = User::find($request->id);

        if($user->delete()) {
            Storage::disk('public')->delete($user->photo);

            return redirect($_SERVER['HTTP_REFERER']);
        }

        return abort(500);
    }

    public function UpdateUser (Request $request)
    {
        $user = User::find($request->id);
        $data = $request->all();
        $fields = new User();
        $fields = $fields->fillable;

        // Удаляем лишние данные
        unset($data['_token']);

        if(empty($data['password']))
            unset($data['password']);
        else
            $data['password'] = Hash::make($data['password']);

        if($request->hasFile('photo') && !isset($data['photo_base64'])) {
            $file_photo = $request->file('photo');
            $file_path = Storage::disk('public')->putFile('avatars', $file_photo);

            Storage::disk('public')->delete($user->photo);
            $data['photo'] = $file_path;
        }

        foreach($data as $k => $v) {

            if(preg_match('/^data:image\/(\w+);base64,/', $v)) {
                unset($data[$k]);
                $k = str_replace('_base64', '', $k);

                $base64_image = substr($v, strpos($v, ',') + 1);
                $base64_image = base64_decode($base64_image);

                $hash = sha1(time());
                $path = "avatars/$hash.png";
                $base64_image = Storage::disk('public')->put($path, $base64_image);

                $user[$k] = $path;
            } else {
                if(in_array($k, $fields)) {
                    $user[$k] = $v;
                }
            }
        }

        // Сохраняем пользователя
        if($user->save()) {
            return redirect($_SERVER['HTTP_REFERER']);
        }

        return abort(500);
    }

    /**
     * GET-запросы
     */
    public function ShowUsers (Request $request, $errors = [])
    {
        $data = [];

        /**
         * Сортировка
         */
        $data['orderKey'] = $request->get('orderKey', 'created_at');
        $data['orderBy'] = $request->get('orderBy', 'DESC');

        $users = app('App\\User');
        $fieldsModel = $users->fillable;

        $notClients = isset($_GET['notClients']);

        if(isset($_GET['filter'])) {
            foreach($_GET as $fk => $fv) {
                if(in_array($fk, $fieldsModel)) {
                    if($fk === 'role') {
                        $users = $users->where($fk, '=', $_GET[$fk]);
                    } else {
                        $users = $users->where($fk, 'LIKE', '%'. trim($_GET[$fk]) .'%');
                    }
                }
            }
        }

        if($notClients) {
            $users = $users->where('role', '!=', 0);
        }

        $users = $users->orderBy($data['orderKey'], $data['orderBy'])->paginate(10);

        $data['users'] = $users;
        $data['errors'] = $errors;

        return view('admin.users.all', $data);
    }
}
