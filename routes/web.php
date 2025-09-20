<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\NewsController as PublicNewsController;
use App\Http\Controllers\ContactController as PublicContactController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;

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

// Public access code entry
Route::get('/access', [GalleryController::class, 'accessForm'])->name('galleries.access');
Route::post('/access', [GalleryController::class, 'accessSubmit'])->name('galleries.access.submit');

// Public resources: read-only
Route::resource('galleries', GalleryController::class)->only(['index', 'show']);
Route::resource('galleries.photos', PhotoController::class)->only(['index', 'show']);

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
        // Admin CRUD for galleries and nested photos
        Route::resource('galleries', \App\Http\Controllers\GalleryController::class)->except(['index', 'show']);
        Route::resource('galleries.photos', \App\Http\Controllers\PhotoController::class)->except(['index', 'show']);
        // Admin view for managing galleries, uses the main GalleryController
        Route::get('/galleries', [\App\Http\Controllers\GalleryController::class, 'adminIndex'])
            ->name('galleries.index');
        Route::post('/galleries/{gallery}/photos/upload', [\App\Http\Controllers\PhotoController::class, 'upload'])
            ->middleware('upload.tuning')
            ->name('galleries.photos.upload');
        // Generate and email access codes
        Route::post('/galleries/{gallery}/codes', [\App\Http\Controllers\GalleryController::class, 'adminGenerateCode'])
            ->name('galleries.codes.store');
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

        // Admin News CRUD
        Route::resource('news', AdminNewsController::class);
        // Admin Contact review
        Route::resource('contacts', AdminContactController::class)->only(['index','show','update','destroy']);
        // Admin Featured Galleries (placeholder view-only for now)
        Route::get('/featured-galleries', function () { return Inertia::render('FeaturedGalleries/Index')->rootView('admin'); })->name('featured-galleries.index');

        // Admin Settings
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])
            ->name('settings.index');
        Route::patch('/settings/{setting}', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])
            ->name('settings.update');

        // Admin Orders
        Route::get('/orders', [\App\Http\Controllers\Admin\OrdersController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [\App\Http\Controllers\Admin\OrdersController::class, 'show'])->name('orders.show');
    });

// Cart API (session/user based, simple JSON)
Route::get('/cart', [\App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
Route::post('/cart/items', [\App\Http\Controllers\CartController::class, 'add'])->name('cart.items.add');
Route::patch('/cart/items/{item}', [\App\Http\Controllers\CartController::class, 'updateItem'])->name('cart.items.update');
Route::delete('/cart/items/{item}', [\App\Http\Controllers\CartController::class, 'removeItem'])->name('cart.items.remove');
Route::delete('/cart', [\App\Http\Controllers\CartController::class, 'clear'])->name('cart.clear');
// Public News & Contact
Route::get('/news', [PublicNewsController::class, 'index'])->name('news.index');
Route::get('/news/{slug}', [PublicNewsController::class, 'show'])->name('news.show');
Route::get('/contact', [PublicContactController::class, 'index'])->name('contact.index');
Route::post('/contact', [PublicContactController::class, 'store'])->name('contact.store');
