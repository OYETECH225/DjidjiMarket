<?php

namespace App\Livewire\Vendor;

use App\Services\VendorOnboardingService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Créer ma boutique — DjidjiMarket'])]
class Onboarding extends Component
{
    public string $business_name = '';

    public string $vendor_type = 'boutique';

    public string $slug = '';

    public string $description = '';

    public string $address_text = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->role === 'vendor', 403);

        if (auth()->user()->vendor()->exists()) {
            $this->redirectRoute('vendor.dashboard', navigate: true);
        }
    }

    protected function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'vendor_type' => ['required', 'string', Rule::in(['boutique', 'street_food', 'restaurant'])],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:vendors,slug'],
            'description' => ['nullable', 'string'],
            'address_text' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function create(VendorOnboardingService $onboarding): void
    {
        $data = $this->validate();

        $onboarding->createProfile(auth()->user(), $data);

        $this->redirectRoute('vendor.dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.vendor.onboarding');
    }
}
