<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SiteController;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Support\Facades\Route;

Route::get('/login', fn () => redirect()->route('admin.login'))->name('login');

Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('/dich-vu', [SiteController::class, 'services'])->name('services');
Route::get('/dich-vu/{category:slug}', [SiteController::class, 'category'])->name('category');
Route::get('/bai-viet', [SiteController::class, 'events'])->name('events');
Route::get('/bai-viet/{event:slug}', [SiteController::class, 'event'])->name('event');
Route::get('/danh-muc/{category:slug}', fn (Category $category) => redirect()->route('category', $category, 301));

Route::get('/sitemap.xml', function () {
    return response()->view('site.sitemap', [
        'categories' => Category::all(),
        'events' => Event::where('status', 'published')->get(),
    ])->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/robots.txt', fn () => response(
    "User-agent: *\nAllow: /\nDisallow: /admin\nSitemap: ".route('sitemap')."\n",
    200,
    ['Content-Type' => 'text/plain']
));

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AuthController::class, 'form'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login.submit');
    });

    Route::get('/forgot-password', [AuthController::class, 'forgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:3,10')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'resetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:6,1')->name('password.reset.submit');

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/account/email', [AuthController::class, 'updateEmail'])->name('account.email.update');
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::get('/events', [AdminController::class, 'events'])->name('events');
        Route::get('/events/create', [AdminController::class, 'eventForm'])->name('events.create');
        Route::get('/events/{event}/edit', [AdminController::class, 'eventForm'])->name('events.edit');
        Route::post('/events/{event?}', [AdminController::class, 'saveEvent'])->name('events.save');
        Route::delete('/events/{event}', [AdminController::class, 'deleteEvent'])->name('events.delete');
        Route::delete('/images/{image}', [AdminController::class, 'deleteImage'])->name('images.delete');

        Route::post('/categories/seed/defaults', [AdminController::class, 'seedServices'])->name('categories.seed');
        Route::get('/categories/{category}/page', [AdminController::class, 'categoryPage'])->name('categories.page');
        Route::post('/categories/{category}/page', [AdminController::class, 'saveCategoryPage'])->name('categories.page.save');
        Route::get('/categories/{edit?}', [AdminController::class, 'categories'])->name('categories');
        Route::post('/categories/{category?}', [AdminController::class, 'saveCategory'])->name('categories.save');
        Route::delete('/categories/{category}', [AdminController::class, 'deleteCategory'])->name('categories.delete');

        Route::get('/settings/site', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings/site', [AdminController::class, 'saveSettings'])->name('settings.save');
    });
});
