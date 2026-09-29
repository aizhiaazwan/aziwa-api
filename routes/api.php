<?php

use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'Aziwa API is running',
        'data'    => ['time' => now()->toIso8601String()],
    ]);
});