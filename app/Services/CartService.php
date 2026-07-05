<?php

namespace App\Services;

use App\Models\Listing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

/**
 * Session-backed cart for the web (PWA) client journey. Scoped to a single
 * vendor at a time — DjidjiMarket orders belong to one vendor each
 * (see Order::vendor_id), so mixing listings from two vendors in one cart
 * would have nowhere valid to go at checkout.
 */
class CartService
{
    private const SESSION_KEY = 'cart';

    /**
     * @return array<int, int> listing_id => quantity
     */
    private function raw(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    private function save(array $items): void
    {
        Session::put(self::SESSION_KEY, $items);
    }

    public function add(Listing $listing, int $quantity = 1): void
    {
        $items = $this->raw();

        if (empty($items)) {
            Session::put(self::SESSION_KEY.'_vendor_id', $listing->vendor_id);
        } elseif ($this->vendorId() !== $listing->vendor_id) {
            $this->clear();
            Session::put(self::SESSION_KEY.'_vendor_id', $listing->vendor_id);
            $items = [];
        }

        $items[$listing->id] = ($items[$listing->id] ?? 0) + $quantity;
        $this->save($items);
    }

    public function updateQuantity(int $listingId, int $quantity): void
    {
        $items = $this->raw();

        if ($quantity < 1) {
            unset($items[$listingId]);
        } else {
            $items[$listingId] = $quantity;
        }

        $this->save($items);
    }

    public function remove(int $listingId): void
    {
        $this->updateQuantity($listingId, 0);
    }

    public function clear(): void
    {
        Session::forget([self::SESSION_KEY, self::SESSION_KEY.'_vendor_id']);
    }

    public function vendorId(): ?int
    {
        return Session::get(self::SESSION_KEY.'_vendor_id');
    }

    public function count(): int
    {
        return array_sum($this->raw());
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * @return Collection<int, array{listing: Listing, quantity: int, subtotal: float}>
     */
    public function items(): Collection
    {
        $raw = $this->raw();

        if (empty($raw)) {
            return collect();
        }

        return Listing::whereIn('id', array_keys($raw))
            ->get()
            ->map(fn (Listing $listing) => [
                'listing' => $listing,
                'quantity' => $raw[$listing->id],
                'subtotal' => $listing->effectivePrice() * $raw[$listing->id],
            ]);
    }

    public function total(): float
    {
        return (float) $this->items()->sum('subtotal');
    }

    /**
     * @return array<int, array{listing_id: int, quantity: int}>
     */
    public function toOrderItems(): array
    {
        return collect($this->raw())
            ->map(fn (int $quantity, int $listingId) => ['listing_id' => $listingId, 'quantity' => $quantity])
            ->values()
            ->all();
    }
}
