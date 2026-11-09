<?php

use App\Http\Controllers\AdminAuditLogController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - TataKelas Application
|--------------------------------------------------------------------------
|
| Student Workspace & Learning System Routes
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::get('/register', [AuthController::class, 'showRegistration'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
});

Route::get('/', function () {
    if (! auth()->check()) {
        return view('welcome');
    }

    return redirect()->route(auth()->user()->role === 'admin' ? 'admin.index' : 'folders.index');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/account/password', [PasswordController::class, 'edit'])->name('account.password.edit');
    Route::put('/account/password', [PasswordController::class, 'update'])->name('account.password.update');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminUserController::class, 'index'])->name('index');
        Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');
        Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
    });

    Route::middleware('role:teacher,admin,student')->group(function () {
        Route::get('/classrooms', [ClassroomController::class, 'index'])->name('classrooms.index');
        Route::get('/classrooms/{classroom}', [ClassroomController::class, 'show'])->name('classrooms.show');
        Route::get('/classrooms/{classroom}/folders/{folder}', [ClassroomController::class, 'showFolder'])
            ->scopeBindings()
            ->name('classrooms.folders.show');
        Route::post('/classrooms/join', [ClassroomController::class, 'join'])->name('classrooms.join');
    });

    Route::middleware('role:teacher,admin')->group(function () {
        Route::post('/classrooms', [ClassroomController::class, 'store'])->name('classrooms.store');
        Route::post('/classrooms/{classroom}/folders', [ClassroomController::class, 'storeFolder'])
            ->name('classrooms.folders.store');
        Route::delete('/classrooms/{classroom}/co-teachers/{coTeacher}', [ClassroomController::class, 'removeCoTeacher'])
            ->name('classrooms.co-teachers.destroy');
        Route::delete('/classrooms/{classroom}/co-teacher', [ClassroomController::class, 'leaveCoTeaching'])
            ->name('classrooms.co-teacher.leave');
        Route::post('/classrooms/{classroom}/folders/{folder}/resources', [ResourceController::class, 'storeForClassroom'])
            ->scopeBindings()
            ->name('classrooms.folders.resources.store');
        Route::patch('/submissions/{submission}/grade', [SubmissionController::class, 'grade'])
            ->name('submissions.grade');
    });

    Route::middleware('role:student')->group(function () {
        Route::post('/folders/{folder}/submissions', [SubmissionController::class, 'store'])
            ->name('folders.submissions.store');
        Route::delete('/folders/{folder}/submissions', [SubmissionController::class, 'destroy'])
            ->name('folders.submissions.destroy');
    });

    Route::get('/resources/{resource}/file', [ResourceController::class, 'classroomFile'])
        ->name('resources.file');
    Route::get('/resources/{resource}/preview', [ResourceController::class, 'preview'])
        ->name('resources.preview');
    Route::get('/submission-files/{file}', [SubmissionController::class, 'file'])
        ->name('submissions.files.download');

    Route::middleware('role:teacher,student')->group(function () {
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
});
