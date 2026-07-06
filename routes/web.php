<?php

use App\Livewire\Auth\Login;
use App\Livewire\Cart;
use App\Livewire\Checkout;
use App\Livewire\Courier\AvailableOrders as CourierAvailableOrders;
use App\Livewire\Courier\Dashboard as CourierDashboard;
use App\Livewire\Courier\MyDeliveries as CourierMyDeliveries;
use App\Livewire\Courier\Onboarding as CourierOnboarding;
use App\Livewire\Home;
use App\Livewire\MyOrders;
use App\Livewire\OrderTracking;
use App\Livewire\Profile;
use App\Livewire\Vendor\Dashboard as VendorDashboard;
use App\Livewire\Vendor\ListingForm;
use App\Livewire\Vendor\Listings as VendorListings;
use App\Livewire\Vendor\Onboarding as VendorOnboarding;
use App\Livewire\Vendor\Orders as VendorOrders;
use App\Livewire\VendorStorefront;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');

Route::middleware('guest')->group(function () {
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
    Route::get('/mes-commandes', MyOrders::class)->name('my-orders');
    Route::get('/profil', Profile::class)->name('profile');

    Route::prefix('vendeur')->middleware('role:vendor')->group(function () {
        Route::get('/profil', VendorOnboarding::class)->name('vendor.onboarding');
        Route::get('/', VendorDashboard::class)->name('vendor.dashboard');
        Route::get('/catalogue', VendorListings::class)->name('vendor.listings');
        Route::get('/catalogue/nouveau', ListingForm::class)->name('vendor.listings.create');
        Route::get('/catalogue/{listing}/modifier', ListingForm::class)->name('vendor.listings.edit');
        Route::get('/commandes', VendorOrders::class)->name('vendor.orders');
    });

    Route::prefix('livreur')->middleware('role:courier')->group(function () {
        Route::get('/profil', CourierOnboarding::class)->name('courier.onboarding');
        Route::get('/', CourierDashboard::class)->name('courier.dashboard');
        Route::get('/commandes-disponibles', CourierAvailableOrders::class)->name('courier.available-orders');
        Route::get('/mes-livraisons', CourierMyDeliveries::class)->name('courier.deliveries');
    });
});
