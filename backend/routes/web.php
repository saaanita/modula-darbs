<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'Catlendar API']);
});

Route::fallback(function () {
    return response()->json(['message' => 'Endpoint not found'], 404);
});
