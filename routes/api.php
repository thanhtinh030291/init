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

Route::group(['prefix' => 'v1' , 'middleware' => ['localization'],], function () {
    Route::group(['prefix' => 'member'], function () {
        Route::post('login', 'App\Http\Controllers\Api\MemberController@login');
        Route::post('register', 'App\Http\Controllers\Api\MemberController@register');
        Route::post('ekyc', 'App\Http\Controllers\Api\MemberController@ekyc');
        Route::post('forget-password', 'App\Http\Controllers\Api\MemberController@forget_password');
        Route::post('can-register', 'App\Http\Controllers\Api\MemberController@can_register');
        Route::post('check-policy', 'App\Http\Controllers\Api\MemberController@check_policy');
    });
    
    Route::group(['middleware' => 'auth:api'], function() {
        Route::group(['prefix' => 'member'], function () {
            Route::patch('photo', 'App\Http\Controllers\Api\MemberController@photo');
            Route::get('info', 'App\Http\Controllers\Api\MemberController@info');
            Route::get('full-info', 'App\Http\Controllers\Api\MemberController@full_info');
            Route::get('benefit', 'App\Http\Controllers\Api\MemberController@benefit');
            Route::get('bank-accounts', 'App\Http\Controllers\Api\MemberController@bank_accounts');
            Route::post('bank-account', 'App\Http\Controllers\Api\MemberController@bank_account_create');
            Route::put('bank-account', 'App\Http\Controllers\Api\MemberController@bank_account_update');
            Route::get('insurance-card', 'App\Http\Controllers\Api\MemberController@insurance_card');
            Route::post('device', 'App\Http\Controllers\Api\MemberController@device');
            Route::patch('password', 'App\Http\Controllers\Api\MemberController@password');
            Route::get('benefit/download/{id}', 'App\Http\Controllers\Api\MemberController@benefit_download');
        });
        
        Route::group(['prefix' => 'claim'], function () {
            Route::get('issues', 'App\Http\Controllers\Api\ClaimController@issues');
            Route::get('issue/{id}', 'App\Http\Controllers\Api\ClaimController@issue');
            Route::post('issue', 'App\Http\Controllers\Api\ClaimController@issue_create');
            Route::post('note/{id}', 'App\Http\Controllers\Api\ClaimController@note_create');
        });

        Route::group(['prefix' => 'provider'], function () {
            Route::get('all', 'App\Http\Controllers\Api\ProviderController@all');
            Route::get('properties', 'App\Http\Controllers\Api\ProviderController@properties');
            Route::get('nearby', 'App\Http\Controllers\Api\ProviderController@nearby');
        });
        
    });
});