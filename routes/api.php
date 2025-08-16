<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PendudukController;
use App\Http\Controllers\StatistikController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\VisiMisiController;
use App\Http\Controllers\OrganizationalStructureController;
use App\Http\Controllers\PostDocumentController; 

// --- Rute Publik (Bisa diakses siapa saja) ---
Route::post('/login', [AuthController::class, 'login']);
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{post:slug}', [PostController::class, 'show']);
Route::get('/staff', [StaffController::class, 'index']);
Route::get('/pages/{page:slug}', [PageController::class, 'show']);
Route::get('/location', [LocationController::class, 'getLocation']);
Route::get('/statistik', [StatistikController::class, 'getAllStatistik']);
Route::get('/galleries', [GalleryController::class, 'index']);

Route::get('/visi-misi', [VisiMisiController::class, 'index']);
Route::get('/struktur-organisasi', [OrganizationalStructureController::class, 'show']);
Route::get('/statistik/wilayah', [StatistikController::class, 'statistikPerJorong']);
Route::get('/statistik/pendidikan', [StatistikController::class, 'statistikPendidikan']);
Route::get('/statistik/usia', [StatistikController::class, 'statistikUsia']);

Route::get('/documents/{document}/download', [PostDocumentController::class, 'download'])->name('documents.download');


// --- Rute Terproteksi (Hanya bisa diakses oleh Admin yang sudah Login) ---
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rute untuk mengelola Posts (Berita & Pengumuman)
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{post}', [PostController::class, 'update']);
    Route::post('/posts/{post}', [PostController::class, 'update']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);

    // Rute untuk mengelola Staff (Perangkat Nagari)
    Route::post('/staff', [StaffController::class, 'store']);
    Route::put('/staff/{staff}', [StaffController::class, 'update']);
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy']);

    // Rute untuk mengelola Pages (Profil)
    Route::put('/pages/{page}', [PageController::class, 'update']);

    Route::post('/location/update', [LocationController::class, 'updateLocation']);

    Route::get('/penduduk/options', [PendudukController::class, 'getOptions']);
    Route::apiResource('/penduduk', PendudukController::class);

    Route::post('/galleries', [GalleryController::class, 'store']);
    Route::delete('/galleries/{gallery}', [GalleryController::class, 'destroy']);

    Route::put('/visi-misi', [VisiMisiController::class, 'update']);
    // Route::get('/pages/{page:slug}', [PageController::class, 'show']);
    //Route::post('/pages/{page}', [PageController::class, 'update']);

    Route::post('/struktur-organisasi', [OrganizationalStructureController::class, 'store']);

    Route::post('/penduduk/export-excel', [PendudukController::class, 'exportExcel']);
});