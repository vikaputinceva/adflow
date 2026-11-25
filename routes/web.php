<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Вход и восстановление пароля (только для тех, кто ещё не вошёл)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.post');

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

// Всё остальное доступно только после входа
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('home');

    // Задачи видят все: исполнитель только свои и может отметить выполнение
    Route::get('/tasks', [SectionController::class, 'tasks']);
    Route::patch('/tasks/{task}/toggle', [SectionController::class, 'toggleTask']);

    // Администратор и менеджер
    Route::middleware('role:admin,manager')->group(function () {
        Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
        Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
        Route::delete('/campaigns/{campaign}', [CampaignController::class, 'destroy']);
        Route::patch('/campaigns/{campaign}/status', [CampaignController::class, 'updateStatus']);
        Route::patch('/campaigns/{campaign}/metrics', [CampaignController::class, 'updateMetrics']);
        Route::post('/campaigns/{campaign}/materials', [CampaignController::class, 'storeMaterial']);
        Route::delete('/materials/{material}', [CampaignController::class, 'destroyMaterial']);

        Route::post('/tasks', [SectionController::class, 'storeTask']);
        Route::delete('/tasks/{task}', [SectionController::class, 'destroyTask']);

        Route::get('/leads', [LeadController::class, 'index']);
        Route::post('/leads', [LeadController::class, 'store']);
        Route::patch('/leads/{lead}/status', [LeadController::class, 'updateStatus']);
        Route::post('/leads/{lead}/convert', [LeadController::class, 'convert']);
        Route::delete('/leads/{lead}', [LeadController::class, 'destroy']);

        Route::get('/finance', [SectionController::class, 'finance']);
        Route::patch('/finance/{campaign}/budget', [SectionController::class, 'updateFinance']);

        Route::get('/reports', [SectionController::class, 'reports']);
    });

    // Только администратор
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);

        Route::get('/platforms', [PlatformController::class, 'index']);
        Route::post('/platforms', [PlatformController::class, 'store']);
        Route::delete('/platforms/{platform}', [PlatformController::class, 'destroy']);
    });
});
