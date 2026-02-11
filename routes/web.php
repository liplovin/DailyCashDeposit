<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CollateralController;
use App\Http\Controllers\TimeDepositController;
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

        Route::get('/government-securities', function () {
            return Inertia::render('Treasury/Goverment Securities/Index');
        })->name('government-securities');

        Route::get('/other-investment', function () {
            return Inertia::render('Treasury/Other Investment/Index');
        })->name('other-investment');

        Route::get('/operating-accounts', function () {
            return Inertia::render('Treasury/Operating Accounts/Index');
        })->name('operating-accounts');
    });
});

require __DIR__.'/auth.php';
