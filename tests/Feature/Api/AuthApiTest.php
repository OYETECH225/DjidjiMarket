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

    public function test_request_otp_for_a_new_phone_reports_is_new(): void
    {
        Log::spy();

        $response = $this->postJson('/api/auth/otp/request', ['phone' => '+225 07 00 00 00 01']);

        $response->assertOk();
        $response->assertJsonPath('is_new', true);
        $this->assertDatabaseMissing('users', ['phone' => '+225 07 00 00 00 01']);
        $this->extractOtpFromLogs('+225 07 00 00 00 01');
    }

    public function test_request_otp_for_an_existing_phone_reports_not_new(): void
    {
        Log::spy();
        User::factory()->create(['phone' => '+225 07 00 00 00 02']);

        $response = $this->postJson('/api/auth/otp/request', ['phone' => '+225 07 00 00 00 02']);

        $response->assertOk();
        $response->assertJsonPath('is_new', false);
        $this->extractOtpFromLogs('+225 07 00 00 00 02');
    }

    public function test_verify_otp_creates_a_new_account_with_name_and_role(): void
    {
        Log::spy();
        $this->postJson('/api/auth/otp/request', ['phone' => '+225 07 00 00 00 03'])->assertOk();
        $code = $this->extractOtpFromLogs('+225 07 00 00 00 03');

        $response = $this->postJson('/api/auth/otp/verify', [
            'phone' => '+225 07 00 00 00 03',
            'code' => $code,
            'name' => 'Awa Client',
            'role' => 'client',
        ]);

        $response->assertOk()->assertJsonStructure(['token', 'user']);

        $user = User::where('phone', '+225 07 00 00 00 03')->firstOrFail();
        $this->assertSame('Awa Client', $user->name);
        $this->assertSame('client', $user->role);
        $this->assertNotNull($user->phone_verified_at);
        $this->assertNull($user->password);
    }

    public function test_verify_otp_rejects_new_account_without_name_or_role(): void
    {
        Log::spy();
        $this->postJson('/api/auth/otp/request', ['phone' => '+225 07 00 00 00 04'])->assertOk();
        $code = $this->extractOtpFromLogs('+225 07 00 00 00 04');

        $response = $this->postJson('/api/auth/otp/verify', [
            'phone' => '+225 07 00 00 00 04',
            'code' => $code,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('users', ['phone' => '+225 07 00 00 00 04']);
    }

    public function test_verify_otp_rejects_privileged_roles_for_new_accounts(): void
    {
        Log::spy();
        $this->postJson('/api/auth/otp/request', ['phone' => '+225 07 00 00 00 05'])->assertOk();
        $code = $this->extractOtpFromLogs('+225 07 00 00 00 05');

        $response = $this->postJson('/api/auth/otp/verify', [
            'phone' => '+225 07 00 00 00 05',
            'code' => $code,
            'name' => 'Fake Admin',
            'role' => 'admin',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('users', ['phone' => '+225 07 00 00 00 05']);
    }

    public function test_verify_otp_logs_in_an_existing_account_and_ignores_supplied_name_and_role(): void
    {
        Log::spy();
        $user = User::factory()->create([
            'phone' => '+225 07 00 00 00 06', 'name' => 'Real Name', 'role' => 'client', 'phone_verified_at' => now(),
        ]);
        $this->postJson('/api/auth/otp/request', ['phone' => '+225 07 00 00 00 06'])->assertOk();
        $code = $this->extractOtpFromLogs('+225 07 00 00 00 06');

        // An attacker who intercepted someone else's OTP (or the real owner
        // logging in from a form that still has stale fields) should never
        // be able to change the account's name/role via this endpoint.
        $response = $this->postJson('/api/auth/otp/verify', [
            'phone' => '+225 07 00 00 00 06',
            'code' => $code,
            'name' => 'Attacker Name',
            'role' => 'vendor',
        ]);

        $response->assertOk()->assertJsonStructure(['token', 'user']);

        $user->refresh();
        $this->assertSame('Real Name', $user->name);
        $this->assertSame('client', $user->role);
    }

    public function test_verify_otp_marks_an_unverified_existing_account_as_verified(): void
    {
        Log::spy();
        User::factory()->create(['phone' => '+225 07 00 00 00 07', 'phone_verified_at' => null]);
        $this->postJson('/api/auth/otp/request', ['phone' => '+225 07 00 00 00 07'])->assertOk();
        $code = $this->extractOtpFromLogs('+225 07 00 00 00 07');

        $this->postJson('/api/auth/otp/verify', [
            'phone' => '+225 07 00 00 00 07',
            'code' => $code,
        ])->assertOk();

        $this->assertNotNull(User::where('phone', '+225 07 00 00 00 07')->firstOrFail()->phone_verified_at);
    }

    public function test_otp_verify_rejects_wrong_code(): void
    {
        OtpCode::generateFor('+225 07 00 00 00 08');
        User::factory()->create(['phone' => '+225 07 00 00 00 08']);

        $response = $this->postJson('/api/auth/otp/verify', [
            'phone' => '+225 07 00 00 00 08',
            'code' => '000000',
        ]);

        $response->assertStatus(422);
    }
}
