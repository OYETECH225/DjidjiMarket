<?php

namespace App\Livewire\Auth;

use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Connexion — DjidjiMarket'])]
class Login extends Component
{
    public string $role = 'client';

    public string $phone = '';

    public bool $codeSent = false;

    public bool $isNewUser = false;

    public string $name = '';

    public string $code = '';

    public ?string $resendMessage = null;

    public function selectRole(string $role): void
    {
        if (in_array($role, ['client', 'vendor', 'courier'], true)) {
            $this->role = $role;
        }
    }

    public function requestCode(AuthService $auth): void
    {
        $this->validate(['phone' => ['required', 'string', 'max:30']]);

        $this->isNewUser = $auth->requestOtp($this->phone);
        $this->codeSent = true;
    }

    public function changeNumber(): void
    {
        $this->codeSent = false;
        $this->code = '';
        $this->resendMessage = null;
    }

    public function resend(AuthService $auth): void
    {
        $auth->requestOtp($this->phone);
        $this->resendMessage = 'Un nouveau code a été envoyé.';
    }

    public function verify(AuthService $auth): void
    {
        $rules = ['code' => ['required', 'string', 'size:6']];

        if ($this->isNewUser) {
            $rules['name'] = ['required', 'string', 'max:255'];
        }

        $this->validate($rules);

        $user = $auth->verifyOtpAndAuthenticate(
            $this->phone,
            $this->code,
            $this->isNewUser ? $this->name : null,
            $this->isNewUser ? $this->role : null,
        );

        Auth::login($user);
        session()->regenerate();

        $this->redirectRoute('home', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
