<?php

Route::middleware('auth:api')->group(function () {
    Route::post('/alert', 'StopAlertController@store');
    Route::get('/alert', 'StopAlertController@index');
    Route::get('/alert/{stopAlertId}', 'StopAlertController@show');
    Route::patch('/alert/{stopAlertId}', 'StopAlertController@update');
    Route::delete('/alert/{stopAlertId}', 'StopAlertController@destroy');
});

