<?php

namespace App\Livewire\Vendor;

use App\Models\Listing;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class ListingForm extends Component
{
    use WithFileUploads;

    public ?Listing $listing = null;

    public string $type = 'produit';

    public string $name = '';

    public string $description = '';

    public string $price = '';

    public ?string $sale_price = null;

    public ?string $sale_ends_at = null;

    public ?string $stock_quantity = null;

    public ?string $display_number = null;

    public string $promo_code = '';

    public bool $is_active = true;

    /** @var array<TemporaryUploadedFile> */
    public array $newPhotos = [];

    public function mount(?Listing $listing = null): void
    {
        abort_unless(auth()->user()->role === 'vendor', 403);

        $vendor = auth()->user()->vendor()->first();

        if (! $vendor) {
            $this->redirectRoute('vendor.onboarding', navigate: true);

            return;
        }

        if ($listing) {
            abort_unless($listing->vendor_id === $vendor->id, 403);

            $this->listing = $listing;
            $this->type = $listing->type;
            $this->name = $listing->name;
            $this->description = (string) $listing->description;
            $this->price = (string) $listing->price;
            $this->sale_price = $listing->sale_price !== null ? (string) $listing->sale_price : null;
            $this->sale_ends_at = $listing->sale_ends_at?->format('Y-m-d\TH:i');
            $this->stock_quantity = $listing->stock_quantity !== null ? (string) $listing->stock_quantity : null;
            $this->display_number = $listing->display_number !== null ? (string) $listing->display_number : null;
            $this->promo_code = (string) $listing->promo_code;
            $this->is_active = $listing->is_active;
        }
    }

    protected function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(['produit', 'plat_du_jour', 'menu_item'])],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => [
                'nullable', 'numeric', 'min:0', 'required_with:sale_ends_at',
                function ($attribute, $value, $fail) {
                    if ($value !== null && $value !== '' && (float) $value >= (float) $this->price) {
                        $fail('Le prix promo doit être inférieur au prix normal.');
                    }
                },
            ],
            'sale_ends_at' => ['nullable', 'date', 'after:now', 'required_with:sale_price'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'display_number' => ['nullable', 'integer', 'min:0'],
            'promo_code' => ['nullable', 'string', 'max:255'],
            'newPhotos.*' => ['nullable', 'image', 'max:4096'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        $photoUrls = $this->listing->photo_urls ?? [];

        foreach ($this->newPhotos as $photo) {
            $photoUrls[] = Storage::disk('public')->url($photo->store('listings', 'public'));
        }

        $payload = [
            'type' => $data['type'],
            'name' => $data['name'],
            'description' => $data['description'] ?: null,
            'price' => $data['price'],
            'sale_price' => $data['sale_price'] !== null && $data['sale_price'] !== '' ? $data['sale_price'] : null,
            'sale_ends_at' => $data['sale_ends_at'] ?: null,
            'stock_quantity' => $data['stock_quantity'] !== null && $data['stock_quantity'] !== '' ? $data['stock_quantity'] : null,
            'display_number' => $data['display_number'] !== null && $data['display_number'] !== '' ? $data['display_number'] : null,
            'promo_code' => $data['promo_code'] ?: null,
            'is_active' => $this->is_active,
            'photo_urls' => $photoUrls,
        ];

        if ($this->listing) {
            $this->listing->update($payload);
        } else {
            auth()->user()->vendor()->first()->listings()->create([
                ...$payload,
                'currency' => 'XOF',
            ]);
        }

        $this->redirectRoute('vendor.listings', navigate: true);
    }

    public function removePhoto(string $url): void
    {
        $this->listing->update([
            'photo_urls' => collect($this->listing->photo_urls)->reject(fn ($u) => $u === $url)->values()->all(),
        ]);
    }

    public function render()
    {
        return view('livewire.vendor.listing-form');
    }
}
