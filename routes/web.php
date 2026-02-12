<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CollateralController;
use App\Http\Controllers\TimeDepositController;
use App\Http\Controllers\GovernmentSecurityController;
use App\Http\Controllers\OtherInvestmentController;
use App\Http\Controllers\OperatingAccountController;
use App\Http\Controllers\DollarController;
use App\Http\Controllers\CorporateBondController;
use App\Http\Controllers\CashInfusionController;
use App\Http\Controllers\InvestmentController;
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
        'treasury2' => 'Treasury2/Dashboard',
        'treasury3' => 'Treasury3/Dashboard',
        'accounting' => 'Accounting/Dashboard',
        'accounting2' => 'Accounting2/Dashboard',
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
        Route::post('/operating-accounts/{id}/collection', [OperatingAccountController::class, 'storeCollections'])->name('operating-accounts.store-collections');
        Route::post('/collections/process', [OperatingAccountController::class, 'processCollections'])->name('collections.process');

        // Dollar Routes
        Route::get('/dollar', [DollarController::class, 'index'])->name('dollar');
        Route::post('/dollar', [DollarController::class, 'store'])->name('dollar.store');
        Route::put('/dollar/{id}', [DollarController::class, 'update'])->name('dollar.update');
        Route::delete('/dollar/{id}', [DollarController::class, 'destroy'])->name('dollar.destroy');

        // Corporate Bonds Routes
        Route::get('/corporate-bonds', [CorporateBondController::class, 'index'])->name('corporate-bonds');
        Route::post('/corporate-bonds', [CorporateBondController::class, 'store'])->name('corporate-bonds.store');
        Route::put('/corporate-bonds/{id}', [CorporateBondController::class, 'update'])->name('corporate-bonds.update');
        Route::delete('/corporate-bonds/{id}', [CorporateBondController::class, 'destroy'])->name('corporate-bonds.destroy');

        // Cash Infusion Routes
        Route::get('/cash-infusion', [CashInfusionController::class, 'index'])->name('cash-infusion');
        Route::post('/cash-infusion', [CashInfusionController::class, 'store'])->name('cash-infusion.store');
        Route::put('/cash-infusion/{id}', [CashInfusionController::class, 'update'])->name('cash-infusion.update');
        Route::delete('/cash-infusion/{id}', [CashInfusionController::class, 'destroy'])->name('cash-infusion.destroy');

        // Investment Routes
        Route::get('/investment', [InvestmentController::class, 'index'])->name('investment');
        Route::post('/investment', [InvestmentController::class, 'store'])->name('investment.store');
        Route::put('/investment/{id}', [InvestmentController::class, 'update'])->name('investment.update');
        Route::delete('/investment/{id}', [InvestmentController::class, 'destroy'])->name('investment.destroy');

        // Processed Collection Routes
        Route::get('/processed-collection', function () {
            $operatingAccounts = \App\Models\OperatingAccount::with(['collections' => function($query) {
                $query->where('status', 'processed');
            }])->get();
            return Inertia::render('Treasury3/ProcessedCollection/Index', [
                'operatingAccounts' => $operatingAccounts,
            ]);
        })->name('processed-collection');
    });

    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users');

        Route::get('/settings', function () {
            return Inertia::render('Admin/Settings/Index');
        })->name('settings');
    });

    // User Management Routes
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

require __DIR__.'/auth.php';
