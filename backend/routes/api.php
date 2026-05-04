<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GroupController;
use App\Models\Task;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/health', function () {
    return response()->json(['ok' => true]);
});

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/events/stats', [EventController::class, 'stats']);
    Route::get('/events', [EventController::class, 'index']);
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{id}', [EventController::class, 'update']);
    Route::delete('/events/{id}', [EventController::class, 'destroy']);

    Route::apiResource('groups', GroupController::class);

    Route::get('/tasks', function (Request $request) {
        $data = $request->validate([
            'group_id' => 'required|integer|exists:groups,id',
        ]);

        return Task::where('group_id', $data['group_id'])
            ->orderBy('done')
            ->orderBy('created_at', 'desc')
            ->get();
    });

    Route::post('/tasks', function (Request $request) {
        $data = $request->validate([
            'title' => 'required|string|min:3|max:255',
            'group_id' => 'required|integer|exists:groups,id',
            'done' => 'boolean',
        ]);

        return Task::create($data);
    });

    Route::put('/tasks/{task}', function (Request $request, Task $task) {
        $data = $request->validate([
            'title' => 'required|string|min:3|max:255',
            'done' => 'boolean',
            'group_id' => 'required|integer|exists:groups,id',
        ]);

        $task->update($data);

        return $task;
    });

    Route::delete('/tasks/{task}', function (Task $task) {
        $task->delete();

        return response()->json(['message' => 'Uzdevums izdzēsts']);
    });
});
