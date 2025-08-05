<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return 'Laravel is working! Root route is accessible.';
});

Route::get('/test', function () {
    return 'Hello World! Laravel is working.';
});

Route::get('/simple', function () {
    return 'Simple route test - Laravel is working!';
});
