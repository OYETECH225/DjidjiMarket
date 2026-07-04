<?php

namespace Tests\Feature\Api;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ThrottleRequests::class);
    }

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

    public function test_register_creates_unverified_user_and_logs_otp(): void
    {
        Log::spy();

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Awa Client',
            'phone' => '+225 07 00 00 00 01',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'client',
        ]);

        $response->assertCreated();

        $user = User::where('phone', '+225 07 00 00 00 01')->firstOrFail();
        $this->assertNull($user->phone_verified_at);
        $this->assertSame('client', $user->role);

        $this->extractOtpFromLogs($user->phone);
    }

    public function test_register_rejects_privileged_roles(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Fake Admin',
            'phone' => '+225 07 00 00 00 02',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('users', ['phone' => '+225 07 00 00 00 02']);
    }

    public function test_otp_verify_activates_account_and_returns_token(): void
    {
        Log::spy();

        $this->postJson('/api/auth/register', [
            'name' => 'Awa Client',
            'phone' => '+225 07 00 00 00 03',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'client',
        ])->assertCreated();

        $code = $this->extractOtpFromLogs('+225 07 00 00 00 03');

        $response = $this->postJson('/api/auth/otp/verify', [
            'phone' => '+225 07 00 00 00 03',
            'code' => $code,
        ]);

        $response->assertOk()->assertJsonStructure(['token', 'user']);

        $user = User::where('phone', '+225 07 00 00 00 03')->firstOrFail();
        $this->assertNotNull($user->phone_verified_at);
    }

    public function test_otp_verify_rejects_wrong_code(): void
    {
        OtpCode::generateFor('+225 07 00 00 00 04');
        User::factory()->create(['phone' => '+225 07 00 00 00 04']);

        $response = $this->postJson('/api/auth/otp/verify', [
            'phone' => '+225 07 00 00 00 04',
            'code' => '000000',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_fails_when_phone_unverified(): void
    {
        User::factory()->create([
            'phone' => '+225 07 00 00 00 05',
            'password' => bcrypt('password123'),
            'phone_verified_at' => null,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'phone' => '+225 07 00 00 00 05',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_succeeds_once_verified(): void
    {
        User::factory()->create([
            'phone' => '+225 07 00 00 00 06',
            'password' => bcrypt('password123'),
            'phone_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'phone' => '+225 07 00 00 00 06',
            'password' => 'password123',
        ]);

        $response->assertOk()->assertJsonStructure(['token', 'user']);
    }
}
