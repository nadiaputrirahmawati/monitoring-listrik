<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Services\MqttService;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/relay', function (MqttService $mqtt) {
    $mqtt->matikanRelay();
    return 'Relay mati';
});

Route::get('/cekrelay', function (MqttService $mqtt) {
    $mqtt->cekRelay();
    return 'Relay mati';
});


// Dashboard routes
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

// API routes untuk realtime data
Route::get('/dashboard/realtime', [DashboardController::class, 'realtime'])->name('dashboard.realtime');

// Route untuk kontrol
Route::post('/dashboard/ubahMode', [DashboardController::class, 'ubahMode'])->name('dashboard.ubahMode');
Route::post('/dashboard/controlRelay', [DashboardController::class, 'controlRelay'])->name('dashboard.controlRelay');
Route::post('/dashboard/updateSetting', [DashboardController::class, 'updateSetting'])->name('dashboard.updateSetting');

// Route untuk menjalankan MQTT service (bisa dijalankan via command)
Route::get('/mqtt/start', function () {
    $mqttService = new \App\Services\MqttService();
    $mqttService->subscribe();
})->name('mqtt.start');
