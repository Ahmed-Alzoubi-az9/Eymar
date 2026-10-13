<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SellerProductController;
use App\Http\Controllers\ServiceRequestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::get('/locale/{locale}', function (Request $request, string $locale) {
    abort_unless(in_array($locale, ['en', 'ar'], true), 404);
    $request->session()->put('locale', $locale);

    return redirect()->back();
})->name('locale.update');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'create'])->name('register');
    Route::post('/register', [AuthController::class, 'store'])->name('register.store');
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.store');
    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

// Email verification (enforced).
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [AuthController::class, 'verifyNotice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', [AuthController::class, 'resendVerification'])->middleware('throttle:6,1')->name('verification.send');
});

Route::get('/professionals', [MarketplaceController::class, 'professionals'])->name('professionals.index');
Route::get('/professionals/{provider}', [MarketplaceController::class, 'professional'])->name('professionals.show');
Route::get('/products', [MarketplaceController::class, 'products'])->name('products.index');
Route::get('/products/{product:slug}', [MarketplaceController::class, 'product'])->name('products.show');
Route::get('/community', [CommunityController::class, 'index'])->name('community.index');

Route::middleware(['auth', 'active', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{product:slug}', [CartController::class, 'store'])->name('cart.store');
    Route::put('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    // Simulated online-payment gateway (no card data is stored).
    Route::get('/payments/gateway', [PaymentController::class, 'gateway'])->name('payments.gateway');
    Route::post('/payments/gateway', [PaymentController::class, 'process'])->name('payments.process');
    Route::post('/orders/{order}/refund', [PaymentController::class, 'refund'])->name('orders.refund');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('/orders/{order}/reviews', [ReviewController::class, 'storeForOrder'])->name('orders.reviews.store');

    Route::get('/projects', [ServiceRequestController::class, 'index'])
        ->middleware('role:customer,company,contractor,craftsman,admin')
        ->name('projects.index');
    Route::get('/projects/create', [ServiceRequestController::class, 'create'])
        ->middleware('role:customer')
        ->name('projects.create');
    Route::post('/projects', [ServiceRequestController::class, 'store'])
        ->middleware('role:customer')
        ->name('projects.store');
    Route::get('/projects/{project}', [ServiceRequestController::class, 'show'])->name('projects.show');
    Route::post('/projects/{project}/quotes', [ServiceRequestController::class, 'quote'])
        ->middleware('role:contractor,craftsman')
        ->name('projects.quotes.store');
    Route::post('/projects/{project}/quotes/{quote}/accept', [ServiceRequestController::class, 'acceptQuote'])
        ->middleware('role:customer')
        ->name('projects.quotes.accept');
    Route::patch('/projects/{project}/status', [ServiceRequestController::class, 'updateStatus'])->name('projects.status');
    Route::post('/projects/{project}/reviews', [ReviewController::class, 'storeForProject'])->name('projects.reviews.store');

    // Hourly availability + bookings.
    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('/bookings/{provider}', [BookingController::class, 'store'])->name('bookings.store');
    Route::patch('/bookings/{booking}/respond', [BookingController::class, 'respond'])->name('bookings.respond');

    // Direct messaging (real-time via 3s polling).
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{user}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{user}', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/{user}/poll', [MessageController::class, 'poll'])->name('messages.poll');

    // Multi-image portfolio for providers.
    Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');
    Route::post('/portfolio', [PortfolioController::class, 'store'])->name('portfolio.store');
    Route::delete('/portfolio/{item}', [PortfolioController::class, 'destroy'])->name('portfolio.destroy');

    Route::post('/community', [CommunityController::class, 'store'])->name('community.store');
    Route::post('/community/{post}/comments', [CommunityController::class, 'comment'])->name('community.comments.store');
    Route::post('/community/{post}/likes', [CommunityController::class, 'toggleLike'])->name('community.likes.toggle');
    Route::delete('/community/{post}', [CommunityController::class, 'destroy'])->name('community.destroy');
    Route::delete('/community/comments/{comment}', [CommunityController::class, 'destroyComment'])->name('community.comments.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

    Route::middleware('role:company')->prefix('seller')->name('seller.')->group(function () {
        Route::get('/products', [SellerProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [SellerProductController::class, 'create'])->name('products.create');
        Route::post('/products', [SellerProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [SellerProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [SellerProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [SellerProductController::class, 'destroy'])->name('products.destroy');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users.index');
        Route::patch('/users/{user}/status', [AdminController::class, 'updateUserStatus'])->name('users.status');
        Route::get('/products', [AdminController::class, 'products'])->name('products.index');
        Route::patch('/products/{product}/status', [AdminController::class, 'updateProductStatus'])->name('products.status');
        Route::get('/orders', [AdminController::class, 'orders'])->name('orders.index');
        Route::get('/posts', [AdminController::class, 'posts'])->name('posts.index');
        Route::delete('/posts/{post}', [AdminController::class, 'destroyPost'])->name('posts.destroy');
        Route::get('/projects', [AdminController::class, 'projects'])->name('projects.index');
        Route::delete('/projects/{project}', [AdminController::class, 'destroyProject'])->name('projects.destroy');
    });
});
