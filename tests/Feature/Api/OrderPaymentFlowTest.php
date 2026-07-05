<?php

namespace Tests\Feature\Api;

use App\Models\Listing;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderPaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeVendorWithListing(float $price = 15000, ?int $stock = null, float $commissionRate = 10): array
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Boutique Test',
            'vendor_type' => 'boutique',
            'slug' => 'boutique-test-'.uniqid(),
            'verification_level' => 'non_verifie',
            'commission_rate' => $commissionRate,
            'is_active' => true,
        ]);
        $listing = Listing::create([
            'vendor_id' => $vendor->id,
            'type' => 'produit',
            'name' => 'Telephone X',
            'price' => $price,
            'stock_quantity' => $stock,
            'is_active' => true,
        ]);

        return [$vendor, $listing];
    }

    public function test_client_can_create_order_with_computed_totals(): void
    {
        [$vendor, $listing] = $this->makeVendorWithListing(price: 15000, commissionRate: 10);
        $client = User::factory()->create(['role' => 'client']);
        Sanctum::actingAs($client);

        $response = $this->postJson('/api/orders', [
            'vendor_id' => $vendor->id,
            'items' => [['listing_id' => $listing->id, 'quantity' => 2]],
            'delivery_address_text' => 'Cocody, Abidjan',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('order.total_amount', '30000.00');
        $response->assertJsonPath('order.commission_amount', '3000.00');
        $response->assertJsonPath('order.status', 'en_attente_paiement');

        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $response->json('order.id'),
            'status' => 'en_attente_paiement',
        ]);
    }

    public function test_order_charges_the_active_flash_sale_price(): void
    {
        [$vendor, $listing] = $this->makeVendorWithListing(price: 15000, commissionRate: 10);
        $listing->update(['sale_price' => 9000, 'sale_ends_at' => now()->addHours(2)]);
        $client = User::factory()->create(['role' => 'client']);
        Sanctum::actingAs($client);

        $response = $this->postJson('/api/orders', [
            'vendor_id' => $vendor->id,
            'items' => [['listing_id' => $listing->id, 'quantity' => 2]],
            'delivery_address_text' => 'Cocody, Abidjan',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('order.total_amount', '18000.00');
        $this->assertDatabaseHas('order_items', [
            'listing_id' => $listing->id,
            'unit_price' => 9000,
        ]);
    }

    public function test_order_ignores_an_expired_flash_sale_price(): void
    {
        [$vendor, $listing] = $this->makeVendorWithListing(price: 15000, commissionRate: 10);
        $listing->update(['sale_price' => 9000, 'sale_ends_at' => now()->subHour()]);
        $client = User::factory()->create(['role' => 'client']);
        Sanctum::actingAs($client);

        $response = $this->postJson('/api/orders', [
            'vendor_id' => $vendor->id,
            'items' => [['listing_id' => $listing->id, 'quantity' => 1]],
            'delivery_address_text' => 'Cocody, Abidjan',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('order.total_amount', '15000.00');
    }

    public function test_client_can_list_only_their_own_orders(): void
    {
        [$vendor, $listing] = $this->makeVendorWithListing();
        $client = User::factory()->create(['role' => 'client']);
        $otherClient = User::factory()->create(['role' => 'client']);

        Sanctum::actingAs($client);
        $this->postJson('/api/orders', [
            'vendor_id' => $vendor->id,
            'items' => [['listing_id' => $listing->id, 'quantity' => 1]],
            'delivery_address_text' => 'Cocody, Abidjan',
        ])->assertCreated();

        Sanctum::actingAs($otherClient);
        $this->postJson('/api/orders', [
            'vendor_id' => $vendor->id,
            'items' => [['listing_id' => $listing->id, 'quantity' => 1]],
            'delivery_address_text' => 'Yopougon, Abidjan',
        ])->assertCreated();

        Sanctum::actingAs($client);
        $response = $this->getJson('/api/orders');

        $response->assertOk();
        $orders = $response->json('data');
        $this->assertCount(1, $orders);
        $this->assertSame('Boutique Test', $orders[0]['vendor_business_name']);
    }

    public function test_order_creation_rejects_listing_from_a_different_vendor(): void
    {
        [$vendorA] = $this->makeVendorWithListing();
        [, $listingB] = $this->makeVendorWithListing();
        $client = User::factory()->create(['role' => 'client']);
        Sanctum::actingAs($client);

        $response = $this->postJson('/api/orders', [
            'vendor_id' => $vendorA->id,
            'items' => [['listing_id' => $listingB->id, 'quantity' => 1]],
            'delivery_address_text' => 'Cocody, Abidjan',
        ]);

        $response->assertStatus(422);
    }

    public function test_order_creation_rejects_insufficient_stock(): void
    {
        [$vendor, $listing] = $this->makeVendorWithListing(stock: 1);
        $client = User::factory()->create(['role' => 'client']);
        Sanctum::actingAs($client);

        $response = $this->postJson('/api/orders', [
            'vendor_id' => $vendor->id,
            'items' => [['listing_id' => $listing->id, 'quantity' => 5]],
            'delivery_address_text' => 'Cocody, Abidjan',
        ]);

        $response->assertStatus(422);
    }

    private function createOrder(User $client, Vendor $vendor, Listing $listing, int $quantity = 1): Order
    {
        Sanctum::actingAs($client);

        $response = $this->postJson('/api/orders', [
            'vendor_id' => $vendor->id,
            'items' => [['listing_id' => $listing->id, 'quantity' => $quantity]],
            'delivery_address_text' => 'Cocody, Abidjan',
        ])->assertCreated();

        return Order::findOrFail($response->json('order.id'));
    }

    public function test_cash_on_delivery_confirms_order_immediately_without_escrow(): void
    {
        [$vendor, $listing] = $this->makeVendorWithListing();
        $client = User::factory()->create(['role' => 'client']);
        $order = $this->createOrder($client, $vendor, $listing);

        Sanctum::actingAs($client);
        $response = $this->postJson('/api/payments/initiate', [
            'order_id' => $order->id,
            'provider' => 'cash_on_delivery',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('payment.status', 'confirme');
        $this->assertSame('confirmee', $order->refresh()->status);
    }

    public function test_mobile_money_webhook_moves_payment_to_escrow(): void
    {
        [$vendor, $listing] = $this->makeVendorWithListing();
        $client = User::factory()->create(['role' => 'client']);
        $order = $this->createOrder($client, $vendor, $listing);

        Sanctum::actingAs($client);
        $this->postJson('/api/payments/initiate', [
            'order_id' => $order->id,
            'provider' => 'orange_money',
        ])->assertCreated();

        $this->assertSame('en_attente_paiement', $order->refresh()->status);

        $webhookResponse = $this->postJson('/api/payments/webhook', [
            'order_id' => $order->id,
            'provider' => 'orange_money',
            'provider_transaction_id' => 'OM-TEST-123',
            'status' => 'confirme',
        ], ['X-Webhook-Secret' => config('services.payment_aggregator.webhook_secret')]);

        $webhookResponse->assertOk();
        $this->assertSame('paiement_sequestre', $order->refresh()->status);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'sequestre',
            'provider_transaction_id' => 'OM-TEST-123',
        ]);
    }

    public function test_webhook_rejects_invalid_secret(): void
    {
        [$vendor, $listing] = $this->makeVendorWithListing();
        $client = User::factory()->create(['role' => 'client']);
        $order = $this->createOrder($client, $vendor, $listing);

        $response = $this->postJson('/api/payments/webhook', [
            'order_id' => $order->id,
            'provider' => 'orange_money',
            'provider_transaction_id' => 'OM-TEST-123',
            'status' => 'confirme',
        ], ['X-Webhook-Secret' => 'wrong-secret']);

        $response->assertForbidden();
        $this->assertSame('en_attente_paiement', $order->refresh()->status);
    }

    public function test_confirm_receipt_requires_delivered_status_and_releases_escrow(): void
    {
        [$vendor, $listing] = $this->makeVendorWithListing();
        $client = User::factory()->create(['role' => 'client']);
        $order = $this->createOrder($client, $vendor, $listing);

        Sanctum::actingAs($client);
        $this->postJson('/api/payments/initiate', [
            'order_id' => $order->id,
            'provider' => 'orange_money',
        ])->assertCreated();

        $this->postJson('/api/payments/webhook', [
            'order_id' => $order->id,
            'provider' => 'orange_money',
            'provider_transaction_id' => 'OM-TEST-999',
            'status' => 'confirme',
        ], ['X-Webhook-Secret' => config('services.payment_aggregator.webhook_secret')])->assertOk();

        // Too early: order isn't marked "livree" yet (that happens via the
        // courier/status endpoint or Filament, not modeled here).
        Sanctum::actingAs($client);
        $this->postJson("/api/orders/{$order->id}/confirm-receipt")->assertStatus(422);

        $order->update(['status' => 'livree']);

        $response = $this->postJson("/api/orders/{$order->id}/confirm-receipt");

        $response->assertOk();
        $response->assertJsonPath('data.status', 'paiement_libere');
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'libere',
        ]);
    }
}
