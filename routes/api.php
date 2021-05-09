<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('login', 'App\Http\Controllers\Api\MemberController@login');
Route::post('register', 'App\Http\Controllers\Api\MemberController@register');
Route::post('ekyc', 'App\Http\Controllers\Api\MemberController@ekyc');
Route::post('forget-password', 'App\Http\Controllers\Api\MemberController@forget_password');

Route::group(['middleware' => 'auth:api'], function() {
    Route::patch('photo', 'App\Http\Controllers\Api\MemberController@photo');
    Route::get('info', 'App\Http\Controllers\Api\MemberController@info');    
    Route::get('insurance-card', 'App\Http\Controllers\Api\MemberController@insurance_card');
});