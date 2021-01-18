<?php

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
    return view('welcome');
});

Route::get('/user', function () {
    echo '123';
});

Route::get('/user', 'IndexController@index');
//Route::get('/user', 'IndexController@index');


Route::get('/end', function (){
    var_dump(env('APP_DEBUG'));
    var_dump(env('DB_PREFIX','pur_'));
});

Route::get('/conf', function (){
    var_dump(date('Y-m-d H:i:s'));
    var_dump(Config('app.timezone'));
    Config(['app.timezone' => 'UTC']);
    var_dump(Config('app.timezone'));
    var_dump(Config('mail.port'));
});

Route::get('home',function(){
    return view('home');
});

Route::get('jiben','JibenController@index');
Route::get('jiben3','JibenController@index');


Route::get('login','LoginController@index');
Route::post('check','LoginController@check');


Route::get('putWeb','LoginController@putWeb');
Route::put('putHandle','LoginController@putHandle');