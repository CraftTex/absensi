<?php

use App\Http\Controllers\GeminiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'success' => false,
        'error' => 'Something went wrong, check your inputs'
    ])->setStatusCode(400);
});
Route::get('/test', function () {
    return 'bad';
});