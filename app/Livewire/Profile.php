<?php

namespace App\Livewire;

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

    public function render()
    {
        return view('livewire.profile', [
            'roleLabel' => self::ROLE_LABELS[auth()->user()->role] ?? auth()->user()->role,
        ]);
    }
}
