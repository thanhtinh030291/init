<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\MenuController;
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
        Route::get('home', [HomeController::class, 'index'])->name('home');;
        Route::resource('user', UserController::class);
        Route::resource('role', RoleController::class);
        Route::resource('menu', MenuController::class);
    });
});
