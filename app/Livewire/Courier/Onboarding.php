<?php

namespace App\Livewire\Courier;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Devenir livreur — DjidjiMarket'])]
class Onboarding extends Component
{
    public string $vehicle_type = 'moto';

    public function mount(): void
    {
        abort_unless(auth()->user()->role === 'courier', 403);

        if (auth()->user()->courier()->exists()) {
            $this->redirectRoute('courier.dashboard', navigate: true);
        }
    }

    protected function rules(): array
    {
        return [
            'vehicle_type' => ['required', 'string', Rule::in(['moto', 'tricycle', 'velo', 'pied'])],
        ];
    }

    public function create(): void
    {
        $data = $this->validate();

        auth()->user()->courier()->create([
            ...$data,
            'verification_status' => 'en_attente',
            'is_available' => false,
        ]);

        $this->redirectRoute('courier.dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.courier.onboarding');
    }
}
