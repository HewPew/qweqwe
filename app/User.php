<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'name', 'email', 'password', 'role', 'api_token', 'phone', 'sms_code', 'company'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static $userRolesValues = [
        'client' => 0,
        'user' => 1,
        'manager' => 11,
        'admin' => 777,
        'expert' => 778
    ];

    public static $userRolesKeys = [
        '0' => 'protokol',
        '1' => 'protokol',
        '11' => 'protokol',
        '777' => 'protokol',
        '778' => 'protokol'
    ];

    public function hasRole ($role)
    {
        if(isset(self::$userRolesValues[$role])) {
            return true;
        }

        return false;
    }

    /**
     * Проверка админа пользователя
     */
    public static function isAdmin () {
        return Auth::user()->role === 777;
    }

    /**
     * Получение имени юзера
     * @param $id
     * @return string
     */

    public function getName ($id)
    {
        $userName = User::find($id);

        if($userName) {
            $userName = $userName->name;
        } else {
            $userName = '';
        }

        return $userName;
    }
}
