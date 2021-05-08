<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\HbsPlanConfigController;

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
    //
    return redirect()->route('home');
});
Route::group(['prefix' => 'admin'], function () {
    Route::group(['middleware' => ['auth', 'verified']], function () {
        Route::get('home', 'HomeController@index')->name('home');;
        Route::resource('user', UserController::class);
        Route::resource('role', RoleController::class);
        Route::resource('menu', MenuController::class);
        //mesage
        Route::resource('message', MessageController::class);
        Route::post('message/destroyMany', [MessageController::class, 'destroyMany'])->name('message.destroyMany');
        Route::get('/sent', [MessageController::class, 'sent'])->name('message.sent');
        Route::get('/trash', [MessageController::class, 'trash'])->name('message.trash');
        Route::post('message/important', [MessageController::class, 'important'])->name('message.important');
        
        //Settings
        Route::get('setting',  [SettingController::class, 'index'])->name('setting.index');
        Route::post('setting/update',[SettingController::class, 'update']);
        Route::post('setting/updatePlan',[SettingController::class, 'updatePlan']);

        //plan hbs
        Route::resource('hbsplan', HbsPlanConfigController::class);
        
    });
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
