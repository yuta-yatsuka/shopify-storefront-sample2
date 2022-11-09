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

Route::middleware('auth')->group(function() {
    Route::get('/', '\App\Http\Controllers\TopController@index')->name('top');
    Route::get('/cart', '\App\Http\Controllers\CartController@index')->name('cart');
    Route::post('/cart/add', '\App\Http\Controllers\CartController@addItem')->name('cart.add');
    Route::get('/cart/checkout', '\App\Http\Controllers\CartController@checkout')->name('cart.checkout');
    Route::get('/cart/remove', '\App\Http\Controllers\CartController@removeItem')->name('cart.remove');
    Route::get('/test2','\App\Http\Controllers\TopController@test2')->name('test2');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
