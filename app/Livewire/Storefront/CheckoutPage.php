<?php

namespace App\Livewire\Storefront;

use App\Services\StorefrontData;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Lunar\Facades\CartSession;
use Lunar\Models\Address;
use Lunar\Models\Order;

class CheckoutPage extends Component
{
    // Billing fields
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $phone = '';
    public string $line_one = '';
    public string $city = '';
    public string $postcode = '';
    public string $notes = '';
    public string $payment_method = 'cod';

    public bool $orderPlaced = false;
    public ?int $orderId = null;

    // Address selection
    public ?int $selectedAddressId = null;

    // Address modal (new + edit)
    public bool $showAddressModal = false;
    public ?int $editingAddrId = null;
    public string $modal_addr_first_name = '';
    public string $modal_addr_last_name = '';
    public string $modal_addr_line_one = '';
    public string $modal_addr_city = '';
    public string $modal_addr_postcode = '';
    public string $modal_addr_phone = '';

    public function mount(): void
    {
        $user = Auth::guard('web')->user();
        if (! $user) return;

        $nameParts = explode(' ', $user->name ?? '', 2);
        $this->first_name = $nameParts[0] ?? '';
        $this->last_name = $nameParts[1] ?? '';
        $this->email = $user->email ?? '';
        $this->phone = $user->phone ?? '';

        $customer = $this->getCustomer();
        if ($customer) {
            $default = $customer->addresses()
                ->where('shipping_default', true)
                ->first();

            if ($default) {
                $this->selectedAddressId = $default->id;
                $this->applyAddress($default);
            }
        }
    }

    public function selectAddress(?int $addressId): void
    {
        $this->selectedAddressId = $addressId;

        if (! $addressId) {
            $this->line_one = '';
            $this->city = '';
            $this->postcode = '';
            return;
        }

        $user = Auth::guard('web')->user();
        if (! $user) return;

        $customer = $this->getCustomer();
        if (! $customer) return;

        $address = $customer->addresses()->find($addressId);
        if ($address) {
            $this->applyAddress($address);
        }
    }

    public function openNewAddressModal(): void
    {
        $this->editingAddrId = null;
        $this->resetModalFields();
        $user = Auth::guard('web')->user();
        if ($user) {
            $nameParts = explode(' ', $user->name ?? '', 2);
            $this->modal_addr_first_name = $nameParts[0] ?? '';
            $this->modal_addr_last_name = $nameParts[1] ?? '';
            $this->modal_addr_phone = $user->phone ?? '';
        }
        $this->showAddressModal = true;
    }

    public function openEditAddressModal(int $id): void
    {
        $user = Auth::guard('web')->user();
        if (! $user) return;
        $customer = $this->getCustomer();
        if (! $customer) return;
        $address = $customer->addresses()->find($id);
        if (! $address) return;

        $this->editingAddrId = $id;
        $this->modal_addr_first_name = $address->first_name ?? '';
        $this->modal_addr_last_name = $address->last_name ?? '';
        $this->modal_addr_line_one = $address->line_one ?? '';
        $this->modal_addr_city = $address->city ?? '';
        $this->modal_addr_postcode = $address->postcode ?? '';
        $this->modal_addr_phone = $address->contact_phone ?? '';
        $this->showAddressModal = true;
    }

    public function saveAddress(): void
    {
        $this->validate([
            'modal_addr_first_name' => 'required|min:2',
            'modal_addr_line_one' => 'required|min:3',
            'modal_addr_city' => 'required|min:2',
        ], [
            'modal_addr_first_name.required' => __('required'),
            'modal_addr_line_one.required' => __('required'),
            'modal_addr_city.required' => __('required'),
        ]);

        $user = Auth::guard('web')->user();
        if (! $user) return;
        $customer = $this->getCustomer();
        if (! $customer) return;

        $country = StorefrontData::countryGE();

        $data = [
            'customer_id' => $customer->id,
            'country_id' => $country?->id,
            'first_name' => $this->modal_addr_first_name,
            'last_name' => $this->modal_addr_last_name,
            'line_one' => $this->modal_addr_line_one,
            'city' => $this->modal_addr_city,
            'postcode' => $this->modal_addr_postcode ?: null,
            'contact_phone' => $this->modal_addr_phone ?: null,
        ];

        if ($this->editingAddrId) {
            $customer->addresses()->where('id', $this->editingAddrId)->update($data);
            $address = $customer->addresses()->find($this->editingAddrId);
        } else {
            $data['shipping_default'] = false;
            $data['billing_default'] = false;
            $address = Address::create($data);
        }

        $this->selectedAddressId = $address->id;
        $this->applyAddress($address);
        $this->closeAddressModal();
    }

    public function closeAddressModal(): void
    {
        $this->showAddressModal = false;
        $this->editingAddrId = null;
        $this->resetModalFields();
    }

    private $cachedCustomer = false;

    private function getCustomer()
    {
        if ($this->cachedCustomer !== false) return $this->cachedCustomer;

        $user = Auth::guard('web')->user();
        if (! $user) return $this->cachedCustomer = null;

        return $this->cachedCustomer = $this->getCustomer();
    }

    private function resetModalFields(): void
    {
        $this->modal_addr_first_name = '';
        $this->modal_addr_last_name = '';
        $this->modal_addr_line_one = '';
        $this->modal_addr_city = '';
        $this->modal_addr_postcode = '';
        $this->modal_addr_phone = '';
    }

    public function deleteAddress(int $id): void
    {
        $user = Auth::guard('web')->user();
        if (! $user) return;

        $customer = $this->getCustomer();
        if (! $customer) return;

        $address = $customer->addresses()->find($id);
        if (! $address) return;

        $address->delete();

        if ($this->selectedAddressId === $id) {
            $this->selectedAddressId = null;
            $this->line_one = '';
            $this->city = '';
            $this->postcode = '';
        }
    }

    private function applyAddress($address): void
    {
        $this->first_name = $address->first_name ?? $this->first_name;
        $this->last_name = $address->last_name ?? $this->last_name;
        $this->line_one = $address->line_one ?? '';
        $this->city = $address->city ?? '';
        $this->postcode = $address->postcode ?? '';
        if ($address->contact_phone) {
            $this->phone = $address->contact_phone;
        }
    }

    protected function rules(): array
    {
        return [
            'first_name' => 'required|min:2',
            'last_name' => 'required|min:2',
            'email' => 'required|email',
            'phone' => 'required|min:9',
            'line_one' => 'required|min:3',
            'city' => 'required|min:2',
        ];
    }

    public function placeOrder(): void
    {
        $this->validate();

        $cart = CartSession::current(calculate: true);

        if (! $cart || $cart->lines->isEmpty()) {
            session()->flash('error', __('Your cart is empty.'));
            return;
        }

        $country = StorefrontData::countryGE();

        $cart->setBillingAddress([
            'country_id' => $country?->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'line_one' => $this->line_one,
            'city' => $this->city,
            'postcode' => $this->postcode ?: '0000',
            'contact_email' => $this->email,
            'contact_phone' => $this->phone,
        ]);

        $cart->setShippingAddress([
            'country_id' => $country?->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'line_one' => $this->line_one,
            'city' => $this->city,
            'postcode' => $this->postcode ?: '0000',
            'contact_email' => $this->email,
            'contact_phone' => $this->phone,
        ]);

        $shippingOptions = \Lunar\Facades\ShippingManifest::getOptions($cart);
        if ($shippingOptions->isNotEmpty()) {
            $cart->setShippingOption($shippingOptions->first());
        }

        $cart->calculate();

        $order = $cart->createOrder();

        $user = Auth::guard('web')->user();
        $updateData = [
            'status' => 'pending',
            'placed_at' => now(),
            'notes' => $this->notes,
            'meta' => array_merge($order->meta ?? [], [
                'payment_method' => $this->payment_method,
            ]),
        ];

        if ($user) {
            $updateData['user_id'] = $user->id;
            $customer = $this->getCustomer();
            if ($customer) {
                $updateData['customer_id'] = $customer->id;
            }
        }

        $order->update($updateData);

        $this->orderId = $order->id;
        $this->orderPlaced = true;

        CartSession::forget();
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        $cart = CartSession::current(calculate: true);
        $lines = $cart ? $cart->lines->load('purchasable.product.urls.language', 'purchasable.product.media', 'purchasable.prices') : collect();

        $order = null;
        if ($this->orderPlaced && $this->orderId) {
            $order = Order::with(['lines', 'billingAddress', 'shippingAddress'])->find($this->orderId);
        }

        $savedAddresses = collect();
        $user = Auth::guard('web')->user();
        if ($user) {
            $customer = $this->getCustomer();
            if ($customer) {
                $savedAddresses = $customer->addresses()->get();
            }
        }

        $categories = StorefrontData::categories();

        return view('livewire.storefront.checkout-page', [
            'cart' => $cart,
            'lines' => $lines,
            'order' => $order,
            'savedAddresses' => $savedAddresses,
        ])->layout('components.layouts.storefront', ['categories' => $categories]);
    }
}
