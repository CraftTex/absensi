<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClearanceController;
use App\Http\Controllers\GeminiController;
use App\Http\Controllers\HRDController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\StorageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use function Pest\Laravel\withMiddleware;

Route::get('/', function (Request $request) {
    return response()->json([
        'success' => true,
        'data' => 'how did we get here?'
    ]);
})->name('login');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Gemini
Route::post('/gemini', [GeminiController::class, 'analyzeFile'])->name('gemini');
// Route::post('/gemtest', [GeminiController::class, 'test']);

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// HRD
Route::post('/hrd', [HRDController::class, 'store']);
Route::delete('/hrd', [HRDController::class, 'destroy']);
Route::get('/ishrd', [HRDController::class, 'isHrd'])->middleware('auth:sanctum');

// Karyawan (User)
Route::get('/karyawan/{nik}', [KaryawanController::class, 'getByNIK'])->middleware('auth:sanctum');
Route::get('/karyawan', [KaryawanController::class, 'getAll'])->middleware('auth:sanctum');

// Absensi
Route::get('/absensi/user/today', [AbsensiController::class, 'getTodayUserAbsensi'])->middleware('auth:sanctum');
Route::get('/absensi/user', [AbsensiController::class, 'getAllUserAbsensi'])->middleware('auth:sanctum');
Route::get('/absensi/loc/{location}', [AbsensiController::class, 'getAllLocationAbsensi'])->middleware('auth:sanctum');
Route::get('/absensi', [AbsensiController::class, 'getAllAbsensi'])->middleware(['auth:sanctum', 'ability:member-hrd']);
Route::post('/absensi', [AbsensiController::class, 'create'])->middleware('auth:sanctum');
Route::post('/absensi/checkin', [AbsensiController::class, 'checkIn'])->middleware('auth:sanctum');
Route::post('/absensi/checkout', [AbsensiController::class, 'checkOut'])->middleware('auth:sanctum');

// Clearance
Route::get('/clearance', [ClearanceController::class, 'getAll'])->middleware(['auth:sanctum', 'ability:member-hrd']);
Route::get('/clearance/user', [ClearanceController::class, 'getAll'])->middleware('auth:sanctum');
// Route::get('/clearance/test', [ClearanceController::class, 'test'])->middleware('auth:sanctum');
Route::post('/clearance', [ClearanceController::class, 'create'])->middleware('auth:sanctum');
Route::put('/clearance/{id}', [ClearanceController::class, 'updateStatus'])->middleware(['auth:sanctum', 'ability:member-hrd']);
Route::get('/clearance/{id}', [ClearanceController::class, 'getDetailed'])->middleware('auth:sanctum');
Route::get('/clearance/{id}/bukti', [ClearanceController::class, 'retrieveBukti'])->middleware('auth:sanctum');

// Route::post('/storage/image', [StorageController::class, 'test']);
// Route::post('/storage/image/get', [StorageController::class, 'testGet']);

