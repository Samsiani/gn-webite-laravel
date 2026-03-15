<?php

use App\Livewire\Storefront\HomePage;
use App\Livewire\Storefront\ProductDetailPage;
use App\Livewire\Storefront\ProductListingPage;
use App\Livewire\Storefront\SearchPage;
use Illuminate\Support\Facades\Route;

// Storefront routes — shared across all languages
$storefrontRoutes = function () {
    Route::get('/', HomePage::class)->name('home');
    Route::get('/product/{slug}', ProductDetailPage::class)->name('product.show');
    Route::get('/category/{slug}', ProductListingPage::class)->name('category.show');
    Route::get('/search', SearchPage::class)->name('search');
};

// Georgian (default, no prefix)
Route::group([], $storefrontRoutes);

// English
Route::prefix('en')->name('en.')->group($storefrontRoutes);

// Russian
Route::prefix('ru')->name('ru.')->group($storefrontRoutes);
