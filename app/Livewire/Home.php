<?php

namespace App\Livewire;

use App\Models\Vendor;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'DjidjiMarket — le vrai marché, en toute confiance'])]
class Home extends Component
{
    public function render()
    {
        return view('livewire.home', [
            'vendors' => Vendor::where('is_active', true)->latest()->paginate(12),
        ]);
    }
}
