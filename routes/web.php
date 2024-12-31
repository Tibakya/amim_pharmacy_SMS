<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\LogoutController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\Auth\RegisterController;
use App\Http\Controllers\Admin\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\ProfitReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/profit-report', [ProfitReportController::class, 'index'])->name('reports.profit');
    
    Route::get('/generate-profit-report', [ProfitReportController::class, 'generateReport'])->name('admin.profit-report.generateReport');


    Route::post('/sales/multiple', [SaleController::class, 'storeMultipleSales'])->name('sales.storeMultiple');

    Route::post('/products/details', [ProductController::class, 'fetchProductDetails'])->name('products.details');
    Route::get('/products/fetch-batch-numbers', [ProductController::class, 'fetchBatchNumbers'])->name('products.fetchBatchNumbers');


    // Dashboard Routes
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('', [DashboardController::class, 'index']);
    Route::get('dashboard/todays-sales', [DashboardController::class, 'fetchTodaysSalesData'])->name('dashboard.todays-sales'); // New Route for Today's Sales

    // Notification Routes
    Route::get('notification', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
    Route::get('notification-read', [NotificationController::class, 'read'])->name('read');

    // Profile Routes
    Route::get('profile', [UserController::class, 'profile'])->name('profile');
    Route::post('profile/{user}', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::put('profile/update-password/{user}', [UserController::class, 'updatePassword'])->name('update-password');
    Route::post('logout', [LogoutController::class, 'index'])->name('logout');

    // Resourceful Routes
    Route::resource('users', UserController::class);
    Route::resource('permissions', PermissionController::class)->only(['index', 'store', 'destroy']);
    Route::put('permission', [PermissionController::class, 'update'])->name('permissions.update');
    Route::resource('roles', RoleController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'destroy']);
    Route::put('categories', [CategoryController::class, 'update'])->name('categories.update');
    Route::resource('purchases', PurchaseController::class)->except('show');
    Route::get('purchases/reports', [PurchaseController::class, 'reports'])->name('purchases.report');
    Route::post('purchases/reports', [PurchaseController::class, 'generateReport']);
    Route::resource('products', ProductController::class)->except('show');
    Route::get('products/outstock', [ProductController::class, 'outstock'])->name('outstock');
    Route::get('products/expired', [ProductController::class, 'expired'])->name('expired');
    Route::resource('sales', SaleController::class)->except('show');
    Route::get('sales/reports', [SaleController::class, 'reports'])->name('sales.report');
    Route::post('sales/reports', [SaleController::class, 'generateReport']);

    // Backup Routes
    Route::get('backup', [BackupController::class, 'index'])->name('backup.index');
    Route::put('backup/create', [BackupController::class, 'create'])->name('backup.store');
    Route::get('backup/download/{file_name?}', [BackupController::class, 'download'])->name('backup.download');
    Route::delete('backup/delete/{file_name?}', [BackupController::class, 'destroy'])->where('file_name', '(.*)')->name('backup.destroy');

    // Settings Route
    Route::get('settings', [SettingController::class, 'index'])->name('settings');
});

Route::middleware(['guest'])->group(function () {
    // Default Redirection for Guests
    Route::get('', function () {
        return redirect()->route('dashboard');
    });

    // Authentication Routes
    Route::get('login', [LoginController::class, 'index'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('register', [RegisterController::class, 'index'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);

    // Password Reset Routes
    Route::get('forgot-password', [ForgotPasswordController::class, 'index'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'requestEmail']);
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'index'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'resetPassword'])->name('password.update');
});
