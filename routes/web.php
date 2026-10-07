<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BalanceSheetReportController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GeneralLedgerReportController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\ProfitLossReportController;
use Illuminate\Support\Facades\Route;

// 1. Rute Pengunjung (Guest)

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// 2. Rute Terproteksi (Wajib Login)
Route::middleware(['auth'])->group(function () {

    // Auth Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Hanya Admin yang bisa mengelola Master Chart of Accounts (CoA)
    Route::middleware(['role:Admin'])->group(function () {
        Route::resource('accounts', ChartOfAccountController::class);
    });


    // General Journal Entry
    Route::resource('journals', JournalController::class)->only([
        'index',
        'create',
        'store',
        'show',
    ]);

    // General Journal Entry & Export
    Route::get('/journals/export/excel', [JournalController::class, 'exportExcel'])->name('journals.export.excel');
    Route::get('/journals/export/pdf', [JournalController::class, 'exportPdf'])->name('journals.export.pdf');


    // Financial Reports (Memerlukan izin 'view-reports')
    Route::middleware(['permission:view-reports'])->prefix('reports')->name('reports.')->group(function () {
        Route::get('/general-ledger', [GeneralLedgerReportController::class, 'index'])->name('general-ledger');
        Route::get('/profit-loss', [ProfitLossReportController::class, 'index'])->name('profit-loss');
        Route::get('/balance-sheet', [BalanceSheetReportController::class, 'index'])->name('balance-sheet');
    });

});
