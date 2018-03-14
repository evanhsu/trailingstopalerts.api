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

// This route is a catch-all that will render the container-html for the React app, which then handles routing on the client side.
Route::get('/{anything?}', function () {
//    return view('welcome');
    return view('app');
});
