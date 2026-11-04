<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PhotoController;

/*
|--------------------------------------------------------------------------
| Web Routes - FolderIn Application
|--------------------------------------------------------------------------
|
| Knowledge & Context Bookmark Manager Routes
|
*/

// Redirect root to folders dashboard
Route::get('/', function () {
    return redirect()->route('folders.index');
});

// Folder Resource Routes
Route::resource('folders', FolderController::class);

// Resource Routes with additional custom actions
Route::resource('resources', ResourceController::class);

// Additional Resource Actions
Route::patch('resources/{resource}/status', [ResourceController::class, 'updateStatus'])
    ->name('resources.update-status');

Route::patch('resources/{resource}/move', [ResourceController::class, 'move'])
    ->name('resources.move');

// Note Resource Routes
Route::resource('notes', NoteController::class);

// Photo Resource Routes
Route::resource('photos', PhotoController::class);
