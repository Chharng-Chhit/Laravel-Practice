<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    return ["Hello"];
});

Route::get('/test1', function () {
    return ["Hello"];
});