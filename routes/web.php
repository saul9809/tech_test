<?php

use Illuminate\Support\Facades\Route;

// Ruta principal - todas las rutas van a Vue
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
