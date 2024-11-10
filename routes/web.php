<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\QrAbsensiController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PermissionController;

Route::get('/', function () {
    return view('pages.auth.auth-login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('home', function () {
        return view('pages.dashboard', ['type_menu' => 'home']);
    })->name('home');

    Route::resource('users', UserController::class);
    Route::resource('companies', CompanyController::class);
    Route::resource('attendances', AttendanceController::class);
    Route::resource('permissions', PermissionController::class);
    Route::resource('qr_absens', QrAbsensiController::class);
    Route::get('/qr-absens/{id}/download', [QrAbsensiController::class, 'downloadPDF'])->name('qr_absens.download');
});
