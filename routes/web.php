<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\StarsSettingController;
use App\Http\Controllers\Admin\CategoriaController as AdminCategoriaController;
use App\Http\Controllers\Admin\UploadController as AdminUploadController;
use App\Http\Controllers\Admin\OrdenController as AdminOrdenController;

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Admin routes
Route::middleware(['auth','admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::resource('users', AdminUserController::class)->except(['show']);

    // Stars Setting (single record)
    Route::get('settings', [StarsSettingController::class, 'edit'])->name('settings.edit');
    Route::post('settings', [StarsSettingController::class, 'update'])->name('settings.update');

    // Categories
    Route::resource('categorias', AdminCategoriaController::class)->except(['show']);

    // Uploads (cards)
    Route::resource('uploads', AdminUploadController::class);
    Route::delete('media/{media}', [AdminUploadController::class, 'destroyMedia'])->name('uploads.media.destroy');

    // Orders
    Route::get('ordenes', [AdminOrdenController::class, 'index'])->name('ordenes.index');
});
