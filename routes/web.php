<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes - FolderIn Application
|--------------------------------------------------------------------------
|
| Knowledge & Context Bookmark Manager Routes
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::get('/register', [AuthController::class, 'showRegistration'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', function () {
        return redirect()->route('folders.index');
    });

    Route::resource('folders', FolderController::class);

    Route::resource('resources', ResourceController::class);

    Route::patch('resources/{resource}/status', [ResourceController::class, 'updateStatus'])
        ->name('resources.update-status');

    Route::patch('resources/{resource}/move', [ResourceController::class, 'move'])
        ->name('resources.move');

    Route::resource('notes', NoteController::class);
    Route::get('photos/{photo}/file', [PhotoController::class, 'file'])->name('photos.file');
    Route::resource('photos', PhotoController::class);
});
