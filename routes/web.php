<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\PhotoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [LandingPageController::class, 'index']);
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('can:isAdmin');

Route::resource('galleries', GalleryController::class);
Route::resource('galleries.photos', PhotoController::class);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Admin routes (OTP-authenticated and must be admin)
Route::middleware(['auth', 'can:isAdmin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        // Admin view for managing galleries, uses the main GalleryController
        Route::get('/galleries', [\App\Http\Controllers\GalleryController::class, 'adminIndex'])
            ->name('galleries.index');
        Route::post('/galleries/{gallery}/photos/upload', [\App\Http\Controllers\PhotoController::class, 'upload'])
            ->middleware('upload.tuning')
            ->name('galleries.photos.upload');
        // Chunked upload endpoints
        Route::post('/galleries/{gallery}/photos/upload/chunk/start', [\App\Http\Controllers\PhotoController::class, 'chunkStart'])
            ->middleware('upload.tuning')
            ->name('galleries.photos.upload.chunk.start');
        Route::post('/galleries/{gallery}/photos/upload/chunk', [\App\Http\Controllers\PhotoController::class, 'chunkUpload'])
            ->middleware('upload.tuning')
            ->name('galleries.photos.upload.chunk');
        Route::post('/galleries/{gallery}/photos/upload/chunk/finish', [\App\Http\Controllers\PhotoController::class, 'chunkFinish'])
            ->middleware('upload.tuning')
            ->name('galleries.photos.upload.chunk.finish');
        Route::post('/galleries/{gallery}/photos/{photo}/transform', [\App\Http\Controllers\PhotoController::class, 'transform'])
            ->middleware('upload.tuning')
            ->name('galleries.photos.transform');
    });
