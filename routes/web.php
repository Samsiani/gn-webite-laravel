<?php

use App\Livewire\Storefront\AccountPage;
use App\Livewire\Storefront\BlogListPage;
use App\Livewire\Storefront\BlogPostPage;
use App\Livewire\Storefront\CartPage;
use App\Livewire\Storefront\CheckoutPage;
use App\Livewire\Storefront\ContactPage;
use App\Livewire\Storefront\HomePage;
use App\Livewire\Storefront\LoginPage;
use App\Livewire\Storefront\ProductDetailPage;
use App\Livewire\Storefront\ProductListingPage;
use App\Livewire\Storefront\RegisterPage;
use App\Livewire\Storefront\ShopPage;
use Illuminate\Support\Facades\Route;

// Storefront routes — shared across all languages
$storefrontRoutes = function () {
    Route::get('/', HomePage::class)->name('home');
    Route::get('/shop', ShopPage::class)->name('shop');
    Route::get('/product/{slug}', ProductDetailPage::class)->name('product.show');
    Route::get('/category/{slug}', ProductListingPage::class)->name('category.show');
    Route::get('/search', ShopPage::class)->name('search');
    Route::get('/cart', CartPage::class)->name('cart');
    Route::get('/checkout', CheckoutPage::class)->name('checkout');
    Route::get('/contact', ContactPage::class)->name('contact');
    Route::get('/login', LoginPage::class)->name('login');
    Route::get('/register', RegisterPage::class)->name('register');
    Route::get('/my-account', AccountPage::class)->name('account')->middleware('auth');
    Route::post('/login-post', function () {
        $credentials = request()->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if (\Illuminate\Support\Facades\Auth::attempt($credentials, request()->boolean('remember'))) {
            request()->session()->regenerate();
            $locale = app()->getLocale();
            $prefix = $locale === 'ka' ? '' : '/' . $locale;
            return redirect($prefix . '/my-account');
        }
        return back()->withErrors(['email' => __('Invalid email or password.')]);
    })->name('login.post');
    Route::post('/logout', function () {
        \Illuminate\Support\Facades\Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        $locale = app()->getLocale();
        $prefix = $locale === 'ka' ? '' : '/' . $locale;
        return redirect($prefix . '/');
    })->name('logout');
    Route::get('/blog', BlogListPage::class)->name('blog');
    Route::get('/blog/category/{category}', BlogListPage::class)->name('blog.category');
    Route::get('/blog/{slug}', BlogPostPage::class)->name('blog.show');
};

// Georgian (default, no prefix)
Route::group([], $storefrontRoutes);

// English
Route::prefix('en')->name('en.')->group($storefrontRoutes);

// Russian
Route::prefix('ru')->name('ru.')->group($storefrontRoutes);
