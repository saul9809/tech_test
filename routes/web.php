<?php

use App\Http\Controllers\SpaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
// -- Route of principal screen
Route::get('/{any}', [SpaController::class, 'index'])->where('any', '.*');
