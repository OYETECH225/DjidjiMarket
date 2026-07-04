<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Courier\AvailableOrders;
use App\Livewire\Courier\Dashboard;
use App\Livewire\Courier\MyDeliveries;
use App\Livewire\Courier\Onboarding;
use App\Models\Listing;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CourierJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_courier_role_can_access_onboarding(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client)->get(route('courier.onboarding'))->assertForbidden();
    }

    public function test_onboarding_creates_courier_profile_and_redirects_to_dashboard(): void
    {
        $courierUser = User::factory()->create(['role' => 'courier']);

        Livewire::actingAs($courierUser)
            ->test(Onboarding::class)
            ->set('vehicle_type', 'moto')
            ->call('create')
            ->assertRedirect(route('courier.dashboard'));

        $this->assertDatabaseHas('couriers', ['user_id' => $courierUser->id, 'vehicle_type' => 'moto']);
    }

    public function test_dashboard_redirects_to_onboarding_without_a_profile(): void
    {
        $courierUser = User::factory()->create(['role' => 'courier']);

        Livewire::actingAs($courierUser)
            ->test(Dashboard::class)
            ->assertRedirect(route('courier.onboarding'));
    }

    private function makeCourier(): User
    {
        $courierUser = User::factory()->create(['role' => 'courier']);
        $courierUser->courier()->create(['vehicle_type' => 'moto', 'verification_status' => 'en_attente']);

        return $courierUser;
    }

    public function test_dashboard_can_toggle_availability(): void
    {
        $courierUser = $this->makeCourier();

        Livewire::actingAs($courierUser)
            ->test(Dashboard::class)
            ->call('toggleAvailability');

        $this->assertTrue($courierUser->courier()->first()->is_available);
    }

    private function makeOrderSearchingForCourier(): Order
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id, 'business_name' => 'Boutique Test', 'vendor_type' => 'boutique',
            'slug' => 'boutique-test-'.uniqid(), 'is_active' => true,
        ]);
        $listing = Listing::create([
            'vendor_id' => $vendor->id, 'type' => 'produit', 'name' => 'Article', 'price' => 5000, 'is_active' => true,
        ]);
        $client = User::factory()->create(['role' => 'client']);

        $order = Order::create([
            'client_id' => $client->id, 'vendor_id' => $vendor->id, 'status' => 'cherche_livreur',
            'delivery_address_text' => 'Yopougon, Abidjan', 'total_amount' => 5000,
        ]);
        $order->items()->create(['listing_id' => $listing->id, 'quantity' => 1, 'unit_price' => 5000]);

        return $order;
    }

    public function test_available_orders_requires_being_marked_available(): void
    {
        $this->makeOrderSearchingForCourier();
        $courierUser = $this->makeCourier();

        Livewire::actingAs($courierUser)
            ->test(AvailableOrders::class)
            ->assertForbidden();

        $courierUser->courier()->first()->update(['is_available' => true]);

        Livewire::actingAs($courierUser)
            ->test(AvailableOrders::class)
            ->assertOk()
            ->assertSee('Yopougon, Abidjan');
    }

    public function test_only_one_courier_can_accept_from_the_pwa(): void
    {
        $order = $this->makeOrderSearchingForCourier();

        $courierA = $this->makeCourier();
        $courierA->courier()->first()->update(['is_available' => true]);
        $courierB = $this->makeCourier();
        $courierB->courier()->first()->update(['is_available' => true]);

        Livewire::actingAs($courierA)
            ->test(AvailableOrders::class)
            ->call('accept', $order->id)
            ->assertRedirect(route('courier.deliveries'));

        Livewire::actingAs($courierB)
            ->test(AvailableOrders::class)
            ->call('accept', $order->id)
            ->assertSet('message', fn ($m) => str_contains($m, 'prise'));

        $this->assertSame($courierA->id, $order->refresh()->courier_id);
    }

    public function test_my_deliveries_advances_status_in_sequence(): void
    {
        $order = $this->makeOrderSearchingForCourier();
        $courierUser = $this->makeCourier();
        $order->update(['status' => 'livreur_assigne', 'courier_id' => $courierUser->id]);

        Livewire::actingAs($courierUser)
            ->test(MyDeliveries::class)
            ->call('advance', $order->id, 'recuperee');

        $this->assertSame('recuperee', $order->refresh()->status);
    }

    public function test_my_deliveries_rejects_invalid_transition(): void
    {
        $order = $this->makeOrderSearchingForCourier();
        $courierUser = $this->makeCourier();
        $order->update(['status' => 'livreur_assigne', 'courier_id' => $courierUser->id]);

        Livewire::actingAs($courierUser)
            ->test(MyDeliveries::class)
            ->call('advance', $order->id, 'livree')
            ->assertSet('errorMessage', fn ($m) => str_contains($m, 'Transition invalide'));

        $this->assertSame('livreur_assigne', $order->refresh()->status);
    }
}
