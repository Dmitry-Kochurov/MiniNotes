<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Страница документации Swagger
Route::get('/api/documentation', function () {
    return view('swagger');
});
