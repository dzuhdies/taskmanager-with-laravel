<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

Route::get('/', fn () => redirect()->route('tasks.index'));

Route::resource('tasks', TaskController::class)->only(['index','store','update','destroy']);
Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');

Route::get('/history', [TaskController::class, 'history'])->name('tasks.history');
Route::delete('/history/clear', [TaskController::class, 'clearHistory'])->name('tasks.history.clear');
