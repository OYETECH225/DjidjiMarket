<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Cart;
use App\Livewire\Checkout;
use App\Livewire\OrderTracking;
use App\Livewire\VendorStorefront;
use App\Models\Listing;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShoppingJourneyTest extends TestCase
{
    use RefreshDatabase;

    private function makeVendorWithListings(): array
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Boutique Awa',
            'vendor_type' => 'boutique',
            'slug' => 'boutique-awa',
            'is_active' => true,
        ]);
        $listingA = Listing::create(['vendor_id' => $vendor->id, 'type' => 'produit', 'name' => 'Robe wax', 'price' => 15000, 'is_active' => true]);
        $listingB = Listing::create(['vendor_id' => $vendor->id, 'type' => 'produit', 'name' => 'Sac a main', 'price' => 8000, 'is_active' => true]);

        return [$vendor, $listingA, $listingB];
    }

    public function test_adding_to_cart_from_storefront(): void
    {
        [$vendor, $listingA] = $this->makeVendorWithListings();

        Livewire::test(VendorStorefront::class, ['slug' => $vendor->slug])
            ->call('addToCart', $listingA->id)
            ->assertSet('addedMessage', "\"Robe wax\" ajouté au panier.");

        $this->assertSame(1, app(CartService::class)->count());
    }

    public function test_cart_can_update_quantity_and_remove_items(): void
    {
        [$vendor, $listingA] = $this->makeVendorWithListings();
        app(CartService::class)->add($listingA, 2);

        Livewire::test(Cart::class)
            ->assertSee('Robe wax')
            ->call('updateQuantity', $listingA->id, 5)
            ->assertSee('5');

        $this->assertSame(5, app(CartService::class)->count());

        Livewire::test(Cart::class)->call('remove', $listingA->id);

        $this->assertTrue(app(CartService::class)->isEmpty());
    }

    public function test_checkout_redirects_to_cart_when_empty(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        Livewire::actingAs($client)
            ->test(Checkout::class)
            ->assertRedirect(route('cart.show'));
    }

    public function test_checkout_creates_order_and_redirects_to_tracking(): void
    {
        [$vendor, $listingA, $listingB] = $this->makeVendorWithListings();
        $client = User::factory()->create(['role' => 'client']);

        app(CartService::class)->add($listingA, 1);
        app(CartService::class)->add($listingB, 1);

        Livewire::actingAs($client)
            ->test(Checkout::class)
            ->set('delivery_address_text', 'Cocody, Abidjan')
            ->set('provider', 'cash_on_delivery')
            ->call('placeOrder')
            ->assertRedirect(route('order.show', ['order' => 1]));

        $order = Order::firstOrFail();
        $this->assertSame($client->id, $order->client_id);
        $this->assertSame('23000.00', $order->total_amount);
        $this->assertSame('confirmee', $order->status);
        $this->assertTrue(app(CartService::class)->isEmpty());
    }

    public function test_checkout_with_mobile_money_leaves_order_awaiting_payment(): void
    {
        [$vendor, $listingA] = $this->makeVendorWithListings();
        $client = User::factory()->create(['role' => 'client']);
        app(CartService::class)->add($listingA, 1);

        Livewire::actingAs($client)
            ->test(Checkout::class)
            ->set('delivery_address_text', 'Cocody, Abidjan')
            ->set('provider', 'orange_money')
            ->call('placeOrder');

        $this->assertSame('en_attente_paiement', Order::firstOrFail()->status);
    }

    public function test_order_tracking_forbids_other_clients(): void
    {
        [$vendor, $listingA] = $this->makeVendorWithListings();
        $owner = User::factory()->create(['role' => 'client']);
        $intruder = User::factory()->create(['role' => 'client']);

        $order = Order::create([
            'client_id' => $owner->id,
            'vendor_id' => $vendor->id,
            'delivery_address_text' => 'Cocody',
            'total_amount' => 15000,
        ]);

        Livewire::actingAs($intruder)
            ->test(OrderTracking::class, ['order' => $order])
            ->assertForbidden();
    }

    public function test_order_tracking_confirm_receipt_requires_delivered_status(): void
    {
        [$vendor, $listingA] = $this->makeVendorWithListings();
        $client = User::factory()->create(['role' => 'client']);

        $order = Order::create([
            'client_id' => $client->id,
            'vendor_id' => $vendor->id,
            'status' => 'confirmee',
            'delivery_address_text' => 'Cocody',
            'total_amount' => 15000,
        ]);

        Livewire::actingAs($client)
            ->test(OrderTracking::class, ['order' => $order])
            ->call('confirmReceipt')
            ->assertSet('errorMessage', fn ($message) => str_contains($message, 'livrée'));
    }
}
