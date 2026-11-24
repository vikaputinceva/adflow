<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CampaignController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SectionController;
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return redirect('/campaigns');
});

Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
Route::delete('/campaigns/{id}', [CampaignController::class, 'destroy']);
Route::patch('/campaigns/{id}/status', [CampaignController::class, 'updateStatus']);
Route::patch('/campaigns/{id}/metrics', [CampaignController::class, 'updateMetrics']);


Route::post('/campaigns/{id}/materials', [CampaignController::class, 'storeMaterial']);

Route::get('/tasks', [SectionController::class, 'tasks']);
Route::get('/finance', [SectionController::class, 'finance']);
Route::get('/reports', [SectionController::class, 'reports']);


Route::get('/tasks', [SectionController::class, 'tasks']);
Route::post('/tasks', [SectionController::class, 'storeTask']);
Route::patch('/tasks/{id}/toggle', [SectionController::class, 'toggleTask']);
Route::delete('/tasks/{id}', [SectionController::class, 'destroyTask']);

Route::get('/finance', [SectionController::class, 'finance']);
Route::patch('/finance/{id}/budget', [SectionController::class, 'updateFinance']);

