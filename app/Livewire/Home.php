<?php

namespace App\Livewire;

use App\Models\Vendor;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['title' => 'DjidjiMarket — le vrai marché, en toute confiance', 'showBottomNav' => true])]
class Home extends Component
{
    use WithPagination;

    #[Url(as: 'type')]
    public ?string $type = null;

    public function filterBy(?string $type): void
    {
        $this->type = $type && array_key_exists($type, Vendor::VENDOR_TYPE_LABELS) ? $type : null;
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.home', [
            'vendors' => Vendor::where('is_active', true)
                ->when($this->type, fn ($query) => $query->where('vendor_type', $this->type))
                ->latest()
                ->paginate(12),
        ]);
    }
}
