<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\VerifyOtp;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_unverified_user_and_redirects_to_otp_screen(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'Awa Client')
            ->set('phone', '+225 07 10 00 00 01')
            ->set('role', 'client')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertRedirect(route('otp.verify'));

        $user = User::where('phone', '+225 07 10 00 00 01')->firstOrFail();
        $this->assertNull($user->phone_verified_at);
        $this->assertSame('+225 07 10 00 00 01', Session::get('otp_phone'));
    }

    public function test_register_rejects_privileged_role(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'Fake Admin')
            ->set('phone', '+225 07 10 00 00 02')
            ->set('role', 'admin')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertHasErrors('role');
    }

    public function test_verify_otp_logs_the_user_in(): void
    {
        $user = User::factory()->create(['phone' => '+225 07 10 00 00 03', 'phone_verified_at' => null]);
        $code = OtpCode::generateFor($user->phone);
        Session::put('otp_phone', $user->phone);

        Livewire::test(VerifyOtp::class)
            ->set('code', $code)
            ->call('verify')
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user->fresh());
        $this->assertNotNull($user->fresh()->phone_verified_at);
    }

    public function test_verify_otp_rejects_wrong_code(): void
    {
        $user = User::factory()->create(['phone' => '+225 07 10 00 00 04', 'phone_verified_at' => null]);
        OtpCode::generateFor($user->phone);
        Session::put('otp_phone', $user->phone);

        Livewire::test(VerifyOtp::class)
            ->set('code', '000000')
            ->call('verify')
            ->assertHasErrors('code');

        $this->assertGuest();
    }

    public function test_login_fails_for_unverified_account(): void
    {
        User::factory()->create([
            'phone' => '+225 07 10 00 00 05',
            'password' => bcrypt('password123'),
            'phone_verified_at' => null,
        ]);

        Livewire::test(Login::class)
            ->set('phone', '+225 07 10 00 00 05')
            ->set('password', 'password123')
            ->call('login')
            ->assertHasErrors('phone');

        $this->assertGuest();
    }

    public function test_login_succeeds_for_verified_account(): void
    {
        $user = User::factory()->create([
            'phone' => '+225 07 10 00 00 06',
            'password' => bcrypt('password123'),
            'phone_verified_at' => now(),
        ]);

        Livewire::test(Login::class)
            ->set('phone', '+225 07 10 00 00 06')
            ->set('password', 'password123')
            ->call('login')
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }
}
