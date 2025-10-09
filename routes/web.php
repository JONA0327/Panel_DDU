<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Dashboard DDU routes
Route::middleware(['auth', 'verified', 'ddu.member'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reuniones', [App\Http\Controllers\DashboardController::class, 'reuniones'])->name('reuniones.index');
    Route::get('/asistente', [App\Http\Controllers\DashboardController::class, 'asistente'])->name('asistente.index');

    // Rutas para administración de miembros (solo administradores)
    Route::prefix('admin/members')->name('admin.members.')->group(function () {
        Route::get('/', [App\Http\Controllers\MemberManagementController::class, 'index'])->name('index');
        Route::get('/search-users', [App\Http\Controllers\MemberManagementController::class, 'searchUsers'])->name('search.users');
        Route::post('/add', [App\Http\Controllers\MemberManagementController::class, 'addMember'])->name('add');
        Route::put('/{id}', [App\Http\Controllers\MemberManagementController::class, 'updateMember'])->name('update');
        Route::patch('/{id}/toggle-status', [App\Http\Controllers\MemberManagementController::class, 'toggleStatus'])->name('toggle.status');
        Route::delete('/{id}', [App\Http\Controllers\MemberManagementController::class, 'removeMember'])->name('remove');
    });
});

Route::middleware(['auth', 'ddu.member'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


});

require __DIR__.'/auth.php';
