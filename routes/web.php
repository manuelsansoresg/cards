<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\StarsSettingController;
use App\Http\Controllers\Admin\CategoriaController as AdminCategoriaController;
use App\Http\Controllers\Admin\UploadController as AdminUploadController;
use App\Http\Controllers\Admin\OrdenController as AdminOrdenController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ReactionController;

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
    $categorias = \App\Models\Categoria::where('estado', 1)->orderBy('id')->get();
    $uploads = \App\Models\Upload::with(['media','reactions','categoria'])
        ->whereIn('categoria_id', $categorias->pluck('id'))
        ->orderByDesc('id')
        ->get();

    $purchasedUploadIds = [];
    if (auth()->check()) {
        $purchasedUploadIds = \App\Models\DetalleOrden::whereHas('orden', function($q){
                $q->where('usuario_id', auth()->id());
            })->pluck('archivo_id')->toArray();
    }

    return view('front.index', compact('categorias','uploads','purchasedUploadIds'));
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Checkout
// Página dedicada para seleccionar método de pago
Route::get('/pago', [CheckoutController::class, 'page'])->name('checkout.page');
Route::middleware('auth')->group(function(){
    Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('checkout');
    // Reactions: guardar del usuario
    Route::post('/reactions', [ReactionController::class, 'store'])->name('reactions.store');
});

// Reactions: listado público por tarjeta
Route::get('/reactions/{upload}', [ReactionController::class, 'index'])->name('reactions.index');

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
