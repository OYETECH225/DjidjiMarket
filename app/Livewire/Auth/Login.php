<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Connexion — DjidjiMarket'])]
class Login extends Component
{
    public string $phone = '';

    public string $password = '';

    public function login(): void
    {
        $this->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('phone', $this->phone)->first();

        // Check credentials before verification status, so a wrong password
        // and an unverified account give different, distinguishable errors
        // only once we already know the password is correct — otherwise an
        // attacker could probe phone numbers to learn which are registered.
        if (! $user || ! Hash::check($this->password, $user->password)) {
            $this->addError('phone', 'Identifiants invalides.');

            return;
        }

        if ($user->phone_verified_at === null) {
            $this->addError('phone', 'Numéro non vérifié. Vérifiez votre code OTP avant de vous connecter.');

            return;
        }

        Auth::login($user);
        session()->regenerate();

        $this->redirectRoute('home', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
