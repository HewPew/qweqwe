<?php

use App\Mail\CallbackMail;
use App\Http\Middleware\ {
    CheckAdmin, CheckManager
};

use Illuminate\Support\Facades\Mail;

// Маршруты статичных и главных страниц
Route::get('/', 'IndexController@RenderIndex')->name('index');
Route::get('/home/{type_ankets?}', 'HomeController@index')->name('home');

/**
 * API-маршруты
 */

// Сброс пункта выпуска
Route::get('/api/getField/{model}/{field}/{default_value?}', 'IndexController@GetFieldHTML');

Route::get('/anketa/print/{id}/{not_show_double_calc}', 'AnketsController@PrintExport')->name('forms.print');

Route::post('/send-sms-code', 'SmsController@SendSmsCode');

/**
 * Профиль, анкета, авторзация
 */
Route::middleware(['auth'])->group(function () {
    Route::get('/callback', function () {
        return view ('callback');
    })->name('callback');

    Route::post('/callback', function () {
        $data = request()->all();

        $data['to'] = 'it2.nozdratenko@gmail.com';

        $mailSend = Mail::send(new CallbackMail($data));

        return redirect(route('callback', ['success' => 1]));
    })->name('callback.post');

    Route::prefix('profile')->group(function () {

        // Модернизация анкеты
        Route::get('/anketa', 'IndexController@RenderForms')->name('forms');
        Route::post('/anketa', 'AnketsController@AddForm')->name('addAnket');

        Route::get('/', 'ProfileController@RenderIndex')->name('profile');
        Route::post('/', 'ProfileController@UpdateData')->name('updateProfile');

    });

    // Рендер элемента (водитель, компания и т.д.)
    Route::get('/elements/{type}', 'IndexController@RenderElements')->name('renderElements');
});


/**
 * Элементы CRM
 */
Route::middleware(['auth'])->group(function () {
    Route::prefix('elements')->group(function () {
        // Удаление элемента (водитель, компания и т.д.)
        Route::get('/{type}/{id}', 'IndexController@RemoveElement')->name('removeElement');
        // Добавление элемента (водитель, компания и т.д.)
        Route::post('/{type}', 'IndexController@AddElement')->name('addElement');
        // Обновление элемента (водитель, компания и т.д.)
        Route::post('/{type}/{id}', 'IndexController@updateElement')->name('updateElement');
    });

    Route::post('/elements-import/{type}', 'IndexController@ImportElements')->name('importElements');
    Route::get('/elements-syncdata/{fieldFindId}/{fieldFind}/{model}/{fieldSync}/{fieldSyncValue}', 'IndexController@SyncDataElement')->name('syncDataElement');

    Route::prefix('anketa')->group(function () {
        Route::delete('/{id}', 'AnketsController@Delete')->name('forms.delete');
        Route::post('/{id}', 'AnketsController@Update')->name('forms.update');
        Route::get('/{id}', 'AnketsController@Get')->name('forms.get');

        Route::post('/save-pdf/{id}', 'AnketsController@SavePdfProtokol')->name('forms.savepdf');
    });

    // Сохранение полей в HOME
    Route::post('/save-fields-home/{type_ankets}', 'HomeController@SaveCheckedFieldsFilter')->name('home.save-fields');

    Route::get('/anketa-trash/{id}/{action}', 'AnketsController@Trash')->name('forms.trash');
});

/**
 * Панель администратора
 */
Route::middleware(['auth', CheckAdmin::class])->group(function () {
    Route::prefix('admin')->group(function () {
        // Модернизация пользователей
        Route::get('/users', 'AdminController@ShowUsers')->name('adminUsers');
        Route::post('/users', 'AdminController@CreateUser')->name('adminCreateUser');
        Route::get('/users/{id}', 'AdminController@DeleteUser')->name('adminDeleteUser');
        Route::post('/users/{id}', 'AdminController@UpdateUser')->name('adminUpdateUser');
    });
});

// Маршруты авторизации
Auth::routes();

