<?php

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('home');
});
Route::group(['prefix' => 'admin'], function () {
    Route::group(['middleware' => ['auth', 'verified']], function () {
        Route::get('home', 'HomeController@index')->name('home');;
        Route::resource('user', 'App\Http\Controllers\UserController');
        Route::resource('role', 'App\Http\Controllers\RoleController');
        Route::resource('menu', 'App\Http\Controllers\MenuController');
        //mesage
        Route::resource('message', 'App\Http\Controllers\MessageController');
        Route::post('message/destroyMany', 'App\Http\Controllers\MessageController@destroyMany')->name('message.destroyMany');
        Route::get('/sent', 'App\Http\Controllers\MessageController@sent')->name('message.sent');
        Route::get('/trash', 'App\Http\Controllers\MessageController@trash')->name('message.trash');
        Route::post('message/important', 'App\Http\Controllers\MessageController@important')->name('message.important');
        
        //Settings
        Route::get('setting',  'App\Http\Controllers\SettingController@index')->name('setting.index');
        Route::post('setting/update','App\Http\Controllers\SettingController@update');
        Route::post('setting/updatePlan','App\Http\Controllers\SettingController@updatePlan');

        //plan hbs
        Route::resource('hbsplan', 'App\Http\Controllers\HbsPlanConfigController');

        //moblie user
        Route::resource('mobileuser', 'App\Http\Controllers\MobileUserController');
    });
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
