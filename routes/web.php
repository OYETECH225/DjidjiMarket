<?php

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\VerifyOtp;
use App\Livewire\Cart;
use App\Livewire\Checkout;
use App\Livewire\Home;
use App\Livewire\OrderTracking;
use App\Livewire\VendorStorefront;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/inscription', Register::class)->name('register');
    Route::get('/verification-otp', VerifyOtp::class)->name('otp.verify');
    Route::get('/connexion', Login::class)->name('login');
});

Route::post('/deconnexion', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('home');
})->middleware('auth')->name('logout');

Route::get('/boutique/{slug}', VendorStorefront::class)->name('vendor.show');

Route::get('/panier', Cart::class)->name('cart.show');

Route::middleware('auth')->group(function () {
    Route::get('/commande/nouvelle', Checkout::class)->name('checkout');
    Route::get('/commande/{order}', OrderTracking::class)->name('order.show');
});
