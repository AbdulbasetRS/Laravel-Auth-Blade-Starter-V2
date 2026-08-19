<?php

use App\Http\Controllers\Admin\DashboardTestController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Frontend\HomeController;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::group([
    'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['localeSessionRedirect', 'localeViewPath'],
], function () {

    Route::get('/', HomeController::class)->name('home');

    Route::middleware('guest')->group(function () {
        Route::get('/login', [LoginController::class, 'create'])->name('login');
        Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/test', DashboardTestController::class)->name('test');

            // Reference pages — living documentation, not tied to Database
            Route::view('/system-design', 'admin.system-design')->name('system-design');
            Route::view('/documentation', 'admin.documentation')->name('documentation');

            Route::prefix('users')->name('users.')->group(function () {
                Route::get('/', [UserController::class, 'index'])->name('index');
                Route::get('/data', [UserController::class, 'data'])->name('data'); // XHR JSON only
                Route::get('/create', [UserController::class, 'create'])->name('create');
                Route::post('/', [UserController::class, 'store'])->name('store');
                Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
                Route::get('/export/{format}', [UserController::class, 'export'])->name('export'); // excel|csv
            });
        });
    });
});
