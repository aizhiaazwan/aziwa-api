<?php

use App\Http\Controllers\Api\AgendaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\ReminderController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TodoController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'Aziwa API is running',
        'data' => ['time' => now()->toIso8601String()],
    ]);
});

// Publik (dibatasi jumlah percobaan per menit)
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// Wajib login (token Bearer)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::put('/user', [AuthController::class, 'update']);

    Route::apiResource('courses', CourseController::class);
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('agendas', AgendaController::class);
    Route::apiResource('todos', TodoController::class);
    Route::apiResource('reminders', ReminderController::class);
    Route::apiResource('notes', NoteController::class);
});