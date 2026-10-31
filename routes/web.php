<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankStatementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\FundTransferController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SecuritySettingsController;
use App\Http\Controllers\SecurityPasswordController;
use App\Http\Controllers\TwoFactorAuthenticationController;
use App\Http\Controllers\LoginHistoryController;
use App\Http\Controllers\ActiveSessionController;
use App\Http\Controllers\SecurityAlertsController;
use App\Http\Controllers\SecurityActivityController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/deposit', [DepositController::class, 'create'])->name('deposit');
    Route::post('/deposit', [DepositController::class, 'store'])->name('deposit.store');
    Route::get('/withdraw', [WithdrawalController::class, 'create'])->name('withdrawal');
    Route::post('/withdraw', [WithdrawalController::class, 'store'])->name('withdrawal.store');
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
    Route::get('/fund-transfer', [FundTransferController::class, 'index'])->name('customer.fund-transfer.index');
    Route::post('/fund-transfer/verify-recipient', [FundTransferController::class, 'verifyRecipient'])
        ->middleware('throttle:10,1')
        ->name('customer.fund-transfer.verify');
    Route::post('/fund-transfer', [FundTransferController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('customer.fund-transfer.store');
    Route::get('/fund-transfer/{transfer}/receipt', [FundTransferController::class, 'receipt'])
        ->name('customer.fund-transfer.receipt');
    Route::get('/bank-statement', [BankStatementController::class, 'index'])->name('customer.bank-statement.index');
    Route::get('/bank-statement/pdf', [BankStatementController::class, 'pdf'])
        ->middleware('throttle:10,1')
        ->name('customer.bank-statement.pdf');
    Route::get('/bank-statement/print', [BankStatementController::class, 'print'])->name('customer.bank-statement.print');
    Route::get('/bank-statement/transaction/{transaction}', [BankStatementController::class, 'show'])->name('customer.bank-statement.transaction');
    Route::get('/profile', [ProfileController::class, 'index'])->name('customer.profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('customer.profile.update');
    Route::get('/profile/photo', [ProfileController::class, 'photo'])->name('customer.profile.photo');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('customer.notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('customer.notifications.read-all');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('customer.notifications.read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('customer.notifications.destroy');
    Route::get('/security-settings', [SecuritySettingsController::class, 'index'])->name('customer.security.index');
    Route::post('/security-settings/password', [SecurityPasswordController::class, 'update'])
        ->middleware('throttle:5,1')->name('customer.security.password.update');
    Route::post('/security-settings/password/verify', [SecurityPasswordController::class, 'verify'])
        ->middleware('throttle:5,1')->name('customer.security.password.verify');
    Route::post('/security-settings/password/resend', [SecurityPasswordController::class, 'resend'])
        ->middleware('throttle:1,1')->name('customer.security.password.resend');
    Route::post('/security-settings/two-factor/start', [TwoFactorAuthenticationController::class, 'start'])
        ->middleware('throttle:5,1')->name('customer.security.two-factor.start');
    Route::get('/security-settings/two-factor/qr-code', [TwoFactorAuthenticationController::class, 'qrCode'])
        ->name('customer.security.two-factor.qr-code');
    Route::post('/security-settings/two-factor/verify', [TwoFactorAuthenticationController::class, 'verify'])
        ->middleware('throttle:5,1')->name('customer.security.two-factor.verify');
    Route::post('/security-settings/two-factor/disable', [TwoFactorAuthenticationController::class, 'disable'])
        ->middleware('throttle:5,1')->name('customer.security.two-factor.disable');
    Route::post('/security-settings/two-factor/recovery-codes', [TwoFactorAuthenticationController::class, 'regenerateRecoveryCodes'])
        ->middleware('throttle:5,1')->name('customer.security.two-factor.recovery-codes');
    Route::post('/security-settings/two-factor/recovery-code', [TwoFactorAuthenticationController::class, 'verifyRecoveryCode'])
        ->middleware('throttle:5,1')->name('customer.security.two-factor.recovery-code');
    Route::get('/security-settings/login-history', [LoginHistoryController::class, 'index'])->name('customer.security.login-history');
    Route::delete('/security-settings/active-sessions/{id}', [ActiveSessionController::class, 'destroy'])
        ->name('customer.security.sessions.destroy');
    Route::post('/security-settings/active-sessions/logout-others', [ActiveSessionController::class, 'destroyOthers'])
        ->name('customer.security.sessions.destroy-others');
    Route::post('/security-settings/login-alerts', [SecurityAlertsController::class, 'update'])
        ->name('customer.security.alerts.update');
    Route::get('/security-settings/activity-log', [SecurityActivityController::class, 'index'])
        ->name('customer.security.activity-log');
});
