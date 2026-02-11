<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CollateralController;
use App\Http\Controllers\TimeDepositController;
use App\Http\Controllers\GovernmentSecurityController;
use App\Http\Controllers\OtherInvestmentController;
use App\Http\Controllers\OperatingAccountController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    $component = match($user->role) {
        'admin' => 'Admin/Dashboard',
        'treasury' => 'Treasury/Dashboard',
        'accounting' => 'Accounting/Dashboard',
        default => 'Admin/Dashboard'
    };
    return Inertia::render($component);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Treasury Routes
    Route::prefix('treasury')->name('treasury.')->group(function () {
        // Collateral Routes
        Route::get('/collateral', [CollateralController::class, 'index'])->name('collateral');
        Route::post('/collateral', [CollateralController::class, 'store'])->name('collateral.store');
        Route::put('/collateral/{id}', [CollateralController::class, 'update'])->name('collateral.update');
        Route::delete('/collateral/{id}', [CollateralController::class, 'destroy'])->name('collateral.destroy');

        // Time Deposit Routes
        Route::get('/time-deposit', [TimeDepositController::class, 'index'])->name('timedeposit');
        Route::post('/time-deposit', [TimeDepositController::class, 'store'])->name('timedeposit.store');
        Route::put('/time-deposit/{id}', [TimeDepositController::class, 'update'])->name('timedeposit.update');
        Route::delete('/time-deposit/{id}', [TimeDepositController::class, 'destroy'])->name('timedeposit.destroy');

        // Government Securities Routes
        Route::get('/government-securities', [GovernmentSecurityController::class, 'index'])->name('government-securities');
        Route::post('/government-securities', [GovernmentSecurityController::class, 'store'])->name('government-securities.store');
        Route::put('/government-securities/{id}', [GovernmentSecurityController::class, 'update'])->name('government-securities.update');
        Route::delete('/government-securities/{id}', [GovernmentSecurityController::class, 'destroy'])->name('government-securities.destroy');

        // Other Investment Routes
        Route::get('/other-investment', [OtherInvestmentController::class, 'index'])->name('other-investment');
        Route::post('/other-investment', [OtherInvestmentController::class, 'store'])->name('other-investment.store');
        Route::put('/other-investment/{id}', [OtherInvestmentController::class, 'update'])->name('other-investment.update');
        Route::delete('/other-investment/{id}', [OtherInvestmentController::class, 'destroy'])->name('other-investment.destroy');

        // Operating Accounts Routes
        Route::get('/operating-accounts', [OperatingAccountController::class, 'index'])->name('operating-accounts');
        Route::post('/operating-accounts', [OperatingAccountController::class, 'store'])->name('operating-accounts.store');
        Route::put('/operating-accounts/{id}', [OperatingAccountController::class, 'update'])->name('operating-accounts.update');
        Route::delete('/operating-accounts/{id}', [OperatingAccountController::class, 'destroy'])->name('operating-accounts.destroy');
    });
});

require __DIR__.'/auth.php';
