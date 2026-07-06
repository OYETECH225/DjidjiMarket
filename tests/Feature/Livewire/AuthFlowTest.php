<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Auth\Login;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    private function extractOtpFromLogs(string $phone): string
    {
        $otp = null;

        Log::shouldHaveReceived('info')->withArgs(function (string $message) use ($phone, &$otp) {
            if (preg_match('/OTP for '.preg_quote($phone, '/').': (\d{6})/', $message, $matches)) {
                $otp = $matches[1];

                return true;
            }

            return false;
        });

        $this->assertNotNull($otp, 'OTP was not logged.');

        return $otp;
    }

    public function test_request_code_for_a_new_phone_flags_it_as_new(): void
    {
        Log::spy();

        Livewire::test(Login::class)
            ->set('phone', '+225 07 10 00 00 01')
            ->call('requestCode')
            ->assertSet('codeSent', true)
            ->assertSet('isNewUser', true);

        $this->extractOtpFromLogs('+225 07 10 00 00 01');
    }

    public function test_request_code_for_an_existing_phone_flags_it_as_not_new(): void
    {
        Log::spy();
        User::factory()->create(['phone' => '+225 07 10 00 00 02']);

        Livewire::test(Login::class)
            ->set('phone', '+225 07 10 00 00 02')
            ->call('requestCode')
            ->assertSet('isNewUser', false);
    }

    public function test_verify_creates_a_new_account_and_logs_in(): void
    {
        Log::spy();

        $component = Livewire::test(Login::class)
            ->set('role', 'vendor')
            ->set('phone', '+225 07 10 00 00 03')
            ->call('requestCode');

        $code = $this->extractOtpFromLogs('+225 07 10 00 00 03');

        $component
            ->set('name', 'Awa Vendeuse')
            ->set('code', $code)
            ->call('verify')
            ->assertRedirect(route('home'));

        $user = User::where('phone', '+225 07 10 00 00 03')->firstOrFail();
        $this->assertSame('Awa Vendeuse', $user->name);
        $this->assertSame('vendor', $user->role);
        $this->assertAuthenticatedAs($user);
    }

    public function test_verify_rejects_new_account_without_name(): void
    {
        Log::spy();

        $component = Livewire::test(Login::class)
            ->set('phone', '+225 07 10 00 00 04')
            ->call('requestCode');

        $code = $this->extractOtpFromLogs('+225 07 10 00 00 04');

        $component
            ->set('code', $code)
            ->call('verify')
            ->assertHasErrors('name');

        $this->assertGuest();
    }

    public function test_verify_rejects_wrong_code(): void
    {
        $user = User::factory()->create(['phone' => '+225 07 10 00 00 05', 'phone_verified_at' => null]);
        OtpCode::generateFor($user->phone);

        Livewire::test(Login::class)
            ->set('phone', $user->phone)
            ->call('requestCode')
            ->set('code', '000000')
            ->call('verify')
            ->assertHasErrors('code');

        $this->assertGuest();
    }

    public function test_verify_logs_in_an_existing_account(): void
    {
        Log::spy();
        $user = User::factory()->create(['phone' => '+225 07 10 00 00 06', 'phone_verified_at' => now()]);

        $component = Livewire::test(Login::class)
            ->set('phone', $user->phone)
            ->call('requestCode');

        $code = $this->extractOtpFromLogs($user->phone);

        $component
            ->set('code', $code)
            ->call('verify')
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }
}
