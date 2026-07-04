<?php

namespace App\Livewire\Auth;

use App\Services\AuthService;
use App\Services\OtpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Vérification — DjidjiMarket'])]
class VerifyOtp extends Component
{
    public string $phone = '';

    public string $code = '';

    public ?string $resendMessage = null;

    public function mount(): void
    {
        $phone = Session::get('otp_phone');

        if (! $phone) {
            $this->redirectRoute('register', navigate: true);

            return;
        }

        $this->phone = $phone;
    }

    public function verify(AuthService $auth): void
    {
        $this->validate(['code' => ['required', 'string', 'size:6']]);

        $user = $auth->verifyOtpAndActivate($this->phone, $this->code);

        Auth::login($user);
        Session::forget('otp_phone');

        $this->redirectRoute('home', navigate: true);
    }

    public function resend(OtpService $otp): void
    {
        $otp->send($this->phone);
        $this->resendMessage = 'Un nouveau code a été envoyé.';
    }

    public function render()
    {
        return view('livewire.auth.verify-otp');
    }
}
