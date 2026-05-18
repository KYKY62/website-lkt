<?php

use App\Http\Controllers\AdminAccountController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminMenuController;
use App\Http\Controllers\AdminNewsController;
use App\Http\Controllers\AdminPageWidgetController;
use App\Http\Controllers\AdminServiceShortcutController;
use App\Http\Controllers\AdminStaticPageController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Route;

Route::get('/admin', function () {
    return auth()->check()
        ? redirect()->route('admin.news.index')
        : redirect()->route('admin.login');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'store'])->name('admin.login.store');
});

Route::middleware('auth')
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
        Route::get('/account', [AdminAccountController::class, 'edit'])->name('account.edit');
        Route::put('/account/profile', [AdminAccountController::class, 'updateProfile'])->name('account.profile.update');
        Route::put('/account/password', [AdminAccountController::class, 'updatePassword'])->name('account.password.update');

        Route::middleware('role:super_admin,news_editor')->group(function (): void {
            Route::resource('news', AdminNewsController::class)
                ->except(['show'])
                ->parameters(['news' => 'news']);

            Route::resource('widgets', AdminPageWidgetController::class)
                ->except(['show'])
                ->parameters(['widgets' => 'widget']);

            Route::resource('services', AdminServiceShortcutController::class)
                ->except(['show'])
                ->parameters(['services' => 'service']);
        });

        Route::middleware('role:super_admin')->group(function (): void {
            Route::resource('users', AdminUserController::class)
                ->except(['show', 'destroy'])
                ->parameters(['users' => 'user']);

            Route::resource('pages', AdminStaticPageController::class)
                ->except(['show'])
                ->parameters(['pages' => 'page']);

            Route::resource('menus', AdminMenuController::class)
                ->except(['show'])
                ->parameters(['menus' => 'menu']);
            Route::post('menus/reorder', [AdminMenuController::class, 'reorder'])->name('menus.reorder');
        });
    });

Route::post('/api/contact-messages', [ContactMessageController::class, 'store'])
    ->name('contact-messages.store');

Route::get('/{any?}', PublicSiteController::class)
    ->where('any', '.*')
    ->name('public-site');
