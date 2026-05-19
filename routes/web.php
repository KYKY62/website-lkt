<?php

use App\Http\Controllers\AdminAccountController;
use App\Http\Controllers\AdminAnnouncementController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminDepartmentNewsController;
use App\Http\Controllers\AdminDownloadDocumentController;
use App\Http\Controllers\AdminMenuController;
use App\Http\Controllers\AdminNewsController;
use App\Http\Controllers\AdminPageWidgetController;
use App\Http\Controllers\AdminServiceShortcutController;
use App\Http\Controllers\AdminStaticPageController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\LegacyRedirectController;
use App\Http\Controllers\PublicContentFileController;
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

            Route::resource('announcements', AdminAnnouncementController::class)
                ->except(['show'])
                ->parameters(['announcements' => 'announcement']);

            Route::resource('downloads', AdminDownloadDocumentController::class)
                ->except(['show'])
                ->parameters(['downloads' => 'download']);

            Route::get('department-news', [AdminDepartmentNewsController::class, 'edit'])->name('department-news.edit');
            Route::put('department-news', [AdminDepartmentNewsController::class, 'update'])->name('department-news.update');
            Route::post('department-news/refresh', [AdminDepartmentNewsController::class, 'refresh'])->name('department-news.refresh');
            Route::post('department-news/clear', [AdminDepartmentNewsController::class, 'clear'])->name('department-news.clear');
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

Route::get('/berita/{legacyId}/{legacySlug?}', [LegacyRedirectController::class, 'news'])
    ->whereNumber('legacyId')
    ->name('legacy.news');
Route::get('/pengumuman/detil/{legacyId}/{legacySlug?}', [LegacyRedirectController::class, 'announcement'])
    ->whereNumber('legacyId')
    ->name('legacy.announcements.show');
Route::get('/pengumuman/get/{legacyId}/{anything?}', [LegacyRedirectController::class, 'announcementFile'])
    ->whereNumber('legacyId')
    ->where('anything', '.*')
    ->name('legacy.announcements.file');
Route::get('/download/get/{legacyId}/{anything?}', [LegacyRedirectController::class, 'downloadFile'])
    ->whereNumber('legacyId')
    ->where('anything', '.*')
    ->name('legacy.downloads.file');
Route::get('/pengumuman/file/{announcement:slug}', [PublicContentFileController::class, 'announcementFile'])
    ->name('announcements.file');
Route::get('/download/file/{download:slug}', [PublicContentFileController::class, 'downloadFile'])
    ->name('downloads.file');

Route::get('/{any?}', PublicSiteController::class)
    ->where('any', '.*')
    ->name('public-site');
