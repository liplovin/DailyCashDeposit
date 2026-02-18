<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CollateralController;
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

        // Operating Accounts Routes
        Route::get('/operating-accounts', [OperatingAccountController::class, 'index'])->name('operating-accounts');
        Route::post('/operating-accounts', [OperatingAccountController::class, 'store'])->name('operating-accounts.store');
        Route::put('/operating-accounts/{id}', [OperatingAccountController::class, 'update'])->name('operating-accounts.update');
        Route::delete('/operating-accounts/{id}', [OperatingAccountController::class, 'destroy'])->name('operating-accounts.destroy');
        Route::post('/operating-accounts/{id}/collection', [OperatingAccountController::class, 'storeCollections'])->name('operating-accounts.store-collections');
        Route::post('/collections/process', [OperatingAccountController::class, 'processCollections'])->name('collections.process');
        Route::delete('/collections/{id}', [OperatingAccountController::class, 'deleteCollection'])->name('collections.delete');
        Route::post('/collections/{id}', [OperatingAccountController::class, 'updateCollection'])->name('collections.update');

        // Investment Routes
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

        // Reports Module - Important
        Route::get('/reports', function () {
            return Inertia::render('Admin/Reports/Index');
        })->name('reports');

        // Treasury Module Routes - Coming Soon
        Route::get('/collateral', function () {
            return Inertia::render('Admin/Collateral/Index');
        })->name('collateral');

        Route::get('/time-deposit', function () {
            return Inertia::render('Admin/Time Deposit/Index');
        })->name('time-deposit');

        Route::get('/government-securities', function () {
            return Inertia::render('Admin/Goverment Securities/Index');
        })->name('government-securities');

        Route::get('/other-investment', function () {
            return Inertia::render('Admin/Other Investment/Index');
        })->name('other-investment');

        Route::get('/operating-accounts/view-collection', function () {
            $operatingAccounts = \App\Models\OperatingAccount::with('collections')->get();
            return Inertia::render('Admin/Operating Accounts/ViewCollectionOperatingAccounts', [
                'operatingAccounts' => $operatingAccounts,
            ]);
        })->name('operating-accounts.view-collection');

        Route::get('/operating-accounts', function () {
            $operatingAccounts = \App\Models\OperatingAccount::with('collections')->get();
            return Inertia::render('Admin/Operating Accounts/Index', [
                'operatingAccounts' => $operatingAccounts,
            ]);
        })->name('operating-accounts');
    });

    // Accounting Routes
    Route::prefix('accounting')->name('accounting.')->group(function () {
        Route::get('/operating-accounts', function () {
            $operatingAccounts = \App\Models\OperatingAccount::all();
            $disbursements = \App\Models\OperatingAccountDisbursement::with('operatingAccount')->where('status', 'pending')->get();
            return Inertia::render('Accounting/Operating Accounts/Index', [
                'operatingAccounts' => $operatingAccounts,
                'disbursements' => $disbursements,
            ]);
        })->name('operating-accounts');
        Route::get('/operating-accounts/processed', function () {
            $operatingAccounts = \App\Models\OperatingAccount::all();
            $disbursements = \App\Models\OperatingAccountDisbursement::with('operatingAccount')->where('status', 'processed')->get();
            return Inertia::render('Accounting/Operating Accounts/ProcessedIndex', [
                'operatingAccounts' => $operatingAccounts,
                'disbursements' => $disbursements,
            ]);
        })->name('operating-accounts.processed');
        Route::post('/operating-account-disbursement', [\App\Http\Controllers\OperatingAccountDisbursementController::class, 'store'])->name('operating-account-disbursement.store');
        Route::post('/validate-operating-account-disbursement', [\App\Http\Controllers\OperatingAccountDisbursementController::class, 'validate'])->name('operating-account-disbursement.validate');
        Route::post('/operating-account-disbursement/process', [\App\Http\Controllers\OperatingAccountDisbursementController::class, 'processDisbursements'])->name('operating-account-disbursement.process');
        Route::put('/operating-account-disbursement/{operatingAccountDisbursement}', [\App\Http\Controllers\OperatingAccountDisbursementController::class, 'update'])->name('operating-account-disbursement.update');
        Route::delete('/operating-account-disbursement/{operatingAccountDisbursement}', [\App\Http\Controllers\OperatingAccountDisbursementController::class, 'destroy'])->name('operating-account-disbursement.destroy');
    });

    // User Management Routes
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});

require __DIR__.'/auth.php';
