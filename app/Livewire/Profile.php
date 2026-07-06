<?php

namespace App\Livewire;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Profil — DjidjiMarket', 'showBottomNav' => true])]
class Profile extends Component
{
    public const ROLE_LABELS = [
        'client' => 'Client',
        'vendor' => 'Vendeur',
        'courier' => 'Livreur',
        'admin' => 'Administrateur',
        'partner_manager' => 'Gestionnaire partenaire',
    ];

    public string $name = '';

    public string $email = '';

    public ?string $savedMessage = null;

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        $this->email = (string) auth()->user()->email;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore(auth()->id())],
        ]);

        auth()->user()->update($data);

        $this->savedMessage = 'Profil mis à jour.';
    }

    public function render()
    {
        return view('livewire.profile', [
            'roleLabel' => self::ROLE_LABELS[auth()->user()->role] ?? auth()->user()->role,
        ]);
    }
}
