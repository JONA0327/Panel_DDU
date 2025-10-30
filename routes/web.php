<?php

use App\Http\Controllers\AssistantController;
use App\Http\Controllers\AssistantSettingsController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MeetingDetailsController;
use App\Http\Controllers\MeetingGroupController;
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
    Route::get('/reuniones/detalles', [MeetingController::class, 'showDetails'])->name('reuniones.showDetails');
    Route::get('/reuniones/{meeting}', [MeetingController::class, 'show'])->name('reuniones.show');
    Route::post('/reuniones/{meeting}/grupos', [MeetingGroupController::class, 'attachMeeting'])->name('grupos.meetings.attach');
    Route::get('/download/audio/{meeting}', [DownloadController::class, 'downloadAudio'])->name('download.audio');
    Route::get('/download/ju/{meeting}', [DownloadController::class, 'downloadJu'])->name('download.ju');
    Route::get('/meeting-details/{transcriptionId}', [MeetingDetailsController::class, 'show'])->name('meetings.details');
    Route::get('/grupos', [MeetingGroupController::class, 'index'])->name('grupos.index');
    Route::post('/grupos', [MeetingGroupController::class, 'store'])->name('grupos.store');
    Route::delete('/grupos/{group}', [MeetingGroupController::class, 'destroy'])->name('grupos.destroy');
    Route::post('/grupos/{group}/miembros', [MeetingGroupController::class, 'storeMember'])->name('grupos.members.store');
    Route::delete('/grupos/{group}/meetings/{meeting}', [MeetingGroupController::class, 'detachMeeting'])->name('grupos.meetings.detach');
    Route::prefix('asistente')->name('assistant.')->group(function () {
        Route::get('/', [AssistantController::class, 'index'])->name('index');
        Route::post('/mensaje', [AssistantController::class, 'sendMessage'])->name('message');
        Route::post('/conversaciones', [AssistantController::class, 'createConversation'])->name('conversations.create');
        Route::get('/conversaciones/{conversation}', [AssistantController::class, 'showConversation'])->name('conversations.show');
        Route::delete('/conversaciones/{conversation}', [AssistantController::class, 'deleteConversation'])->name('conversations.delete');
        Route::post('/documentos', [AssistantController::class, 'uploadDocument'])->name('documents.store');
        Route::post('/configuracion', [AssistantSettingsController::class, 'update'])->name('settings.update');
    });

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
