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


Auth::routes();


Route::group(['middleware' => 'auth'], function() {
    Route::resource('/users','UserController');
    Route::resource('/', 'HomeController');

});

Route::group(['prefix' => 'reports', 'middleware' => 'auth'], function() {
    Route::get('/dashboard-stats/', 'HomeController@dashboardStats')->name('dashboard-stats');
    Route::get('/realtime-stats', 'ReportsController@realTimeReport')->name("realtime-stats");

});
