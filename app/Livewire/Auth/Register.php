<?php

namespace App\Livewire\Auth;

use App\Services\AuthService;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Créer un compte — DjidjiMarket'])]
class Register extends Component
{
    public string $name = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = 'client';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:client,vendor,courier'],
        ];
    }

    public function register(AuthService $auth): void
    {
        $data = $this->validate();

        $auth->registerOrResendOtp($data['name'], $data['phone'], $data['password'], $data['role']);

        Session::put('otp_phone', $data['phone']);

        $this->redirectRoute('otp.verify', navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
