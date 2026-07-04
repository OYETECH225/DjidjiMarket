<?php

namespace Tests\Feature\Api;

use App\Models\Listing;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CourierApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrderSearchingForCourier(): Order
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Boutique Test',
            'vendor_type' => 'boutique',
            'slug' => 'boutique-test-'.uniqid(),
            'is_active' => true,
        ]);
        $listing = Listing::create([
            'vendor_id' => $vendor->id, 'type' => 'produit', 'name' => 'Article', 'price' => 5000, 'is_active' => true,
        ]);
        $client = User::factory()->create(['role' => 'client']);

        $order = Order::create([
            'client_id' => $client->id,
            'vendor_id' => $vendor->id,
            'status' => 'cherche_livreur',
            'delivery_address_text' => 'Yopougon, Abidjan',
            'total_amount' => 5000,
        ]);
        $order->items()->create(['listing_id' => $listing->id, 'quantity' => 1, 'unit_price' => 5000]);

        return $order;
    }

    private function makeCourierUser(): User
    {
        $courierUser = User::factory()->create(['role' => 'courier']);
        Sanctum::actingAs($courierUser);
        $this->postJson('/api/courier/profile', ['vehicle_type' => 'moto'])->assertCreated();

        return $courierUser;
    }

    public function test_availability_requires_a_courier_profile_first(): void
    {
        $courierUser = User::factory()->create(['role' => 'courier']);
        Sanctum::actingAs($courierUser);

        $this->postJson('/api/courier/availability', ['is_available' => true])
            ->assertNotFound();

        $this->postJson('/api/courier/profile', ['vehicle_type' => 'moto'])->assertCreated();

        $this->postJson('/api/courier/availability', ['is_available' => true])
            ->assertOk()
            ->assertJsonPath('data.is_available', true);
    }

    public function test_available_orders_requires_courier_to_be_marked_available(): void
    {
        $this->makeOrderSearchingForCourier();
        $courier = $this->makeCourierUser();

        Sanctum::actingAs($courier);
        $this->getJson('/api/courier/orders/available')->assertForbidden();

        $this->postJson('/api/courier/availability', ['is_available' => true])->assertOk();
        $this->getJson('/api/courier/orders/available')->assertOk();
    }

    public function test_available_orders_lists_only_unassigned_orders_searching_for_a_courier(): void
    {
        $waiting = $this->makeOrderSearchingForCourier();
        $alreadyAssigned = $this->makeOrderSearchingForCourier();
        $alreadyAssigned->update(['status' => 'livreur_assigne', 'courier_id' => User::factory()->create(['role' => 'courier'])->id]);

        $courier = $this->makeCourierUser();
        Sanctum::actingAs($courier);
        $this->postJson('/api/courier/availability', ['is_available' => true])->assertOk();

        $response = $this->getJson('/api/courier/orders/available');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($waiting->id));
        $this->assertFalse($ids->contains($alreadyAssigned->id));
        $this->assertNotNull($response->json('data.0.vendor_business_name'));
    }

    public function test_only_one_courier_can_accept_a_waiting_order(): void
    {
        $order = $this->makeOrderSearchingForCourier();

        $courierA = $this->makeCourierUser();
        $courierB = $this->makeCourierUser();

        Sanctum::actingAs($courierA);
        $this->postJson("/api/courier/orders/{$order->id}/accept")
            ->assertOk()
            ->assertJsonPath('data.status', 'livreur_assigne');

        Sanctum::actingAs($courierB);
        $this->postJson("/api/courier/orders/{$order->id}/accept")
            ->assertStatus(409);

        $this->assertSame($courierA->id, $order->refresh()->courier_id);
    }

    public function test_courier_must_follow_status_sequence(): void
    {
        $order = $this->makeOrderSearchingForCourier();
        $courier = $this->makeCourierUser();

        Sanctum::actingAs($courier);
        $this->postJson("/api/courier/orders/{$order->id}/accept")->assertOk();

        // Can't skip straight to "livree".
        $this->postJson("/api/courier/orders/{$order->id}/status", ['status' => 'livree'])
            ->assertStatus(422);

        $this->postJson("/api/courier/orders/{$order->id}/status", ['status' => 'recuperee'])
            ->assertOk()
            ->assertJsonPath('data.status', 'recuperee');

        $this->postJson("/api/courier/orders/{$order->id}/status", ['status' => 'en_livraison'])
            ->assertOk();

        $this->postJson("/api/courier/orders/{$order->id}/status", ['status' => 'livree'])
            ->assertOk()
            ->assertJsonPath('data.status', 'livree');
    }

    public function test_courier_cannot_update_status_of_an_order_assigned_to_someone_else(): void
    {
        $order = $this->makeOrderSearchingForCourier();
        $courierA = $this->makeCourierUser();
        $courierB = $this->makeCourierUser();

        Sanctum::actingAs($courierA);
        $this->postJson("/api/courier/orders/{$order->id}/accept")->assertOk();

        Sanctum::actingAs($courierB);
        $this->postJson("/api/courier/orders/{$order->id}/status", ['status' => 'recuperee'])
            ->assertForbidden();
    }
}
