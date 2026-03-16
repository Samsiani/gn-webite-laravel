<?php

namespace App\Livewire\Storefront;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Lunar\Models\Address;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Country;
use Lunar\Models\Order;

#[Layout('components.layouts.storefront')]
class AccountPage extends Component
{
    public string $tab = 'orders';

    // Profile
    public string $profile_name = '';
    public string $profile_email = '';
    public string $profile_phone = '';

    // Password
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    // Address form
    public bool $showAddressForm = false;
    public ?int $editingAddressId = null;
    public string $addr_first_name = '';
    public string $addr_last_name = '';
    public string $addr_line_one = '';
    public string $addr_city = '';
    public string $addr_postcode = '';
    public string $addr_phone = '';
    public bool $addr_shipping_default = false;
    public bool $addr_billing_default = false;

    public function mount(): void
    {
        if (! Auth::guard('web')->check()) {
            $this->redirect($this->localePath('/login'));
            return;
        }

        $user = Auth::guard('web')->user();
        $this->profile_name = $user->name ?? '';
        $this->profile_email = $user->email ?? '';
        $this->profile_phone = $user->phone ?? '';
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetAddressForm();
    }

    // ── Profile ──

    public function updateProfile(): void
    {
        $user = Auth::guard('web')->user();

        $this->validate([
            'profile_name' => 'required|min:2',
            'profile_email' => 'required|email|unique:users,email,' . $user->id,
            'profile_phone' => 'nullable|min:9',
        ], [
            'profile_name.required' => __('required'),
            'profile_email.required' => __('required'),
            'profile_email.unique' => __('already taken'),
        ]);

        $user->update([
            'name' => $this->profile_name,
            'email' => $this->profile_email,
            'phone' => $this->profile_phone ?: null,
        ]);

        $customer = $this->getCustomer();
        if ($customer) {
            $nameParts = explode(' ', $this->profile_name, 2);
            $customer->update([
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1] ?? '',
            ]);
        }

        session()->flash('profile_success', __('Profile updated successfully.'));
    }

    // ── Password ──

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ], [
            'current_password.required' => __('required'),
            'new_password.required' => __('required'),
            'new_password.min' => __('min 6 characters'),
            'new_password.confirmed' => __('passwords do not match'),
        ]);

        $user = Auth::guard('web')->user();

        if (! Hash::check($this->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('Current password is incorrect.'),
            ]);
        }

        $user->update(['password' => $this->new_password]);
        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        session()->flash('password_success', __('Password changed successfully.'));
    }

    // ── Addresses ──

    public function editAddress(int $id): void
    {
        $customer = $this->getCustomer();
        if (! $customer) return;

        $address = $customer->addresses()->find($id);
        if (! $address) return;

        $this->editingAddressId = $id;
        $this->addr_first_name = $address->first_name ?? '';
        $this->addr_last_name = $address->last_name ?? '';
        $this->addr_line_one = $address->line_one ?? '';
        $this->addr_city = $address->city ?? '';
        $this->addr_postcode = $address->postcode ?? '';
        $this->addr_phone = $address->contact_phone ?? '';
        $this->addr_shipping_default = (bool) $address->shipping_default;
        $this->addr_billing_default = (bool) $address->billing_default;
        $this->showAddressForm = true;
    }

    public function saveAddress(): void
    {
        $this->validate([
            'addr_first_name' => 'required|min:2',
            'addr_line_one' => 'required|min:3',
            'addr_city' => 'required|min:2',
        ], [
            'addr_first_name.required' => __('required'),
            'addr_line_one.required' => __('required'),
            'addr_city.required' => __('required'),
        ]);

        $customer = $this->getCustomer();
        if (! $customer) return;

        $country = Country::where('iso2', 'GE')->first();

        $data = [
            'customer_id' => $customer->id,
            'country_id' => $country?->id,
            'first_name' => $this->addr_first_name,
            'last_name' => $this->addr_last_name,
            'line_one' => $this->addr_line_one,
            'city' => $this->addr_city,
            'postcode' => $this->addr_postcode ?: null,
            'contact_phone' => $this->addr_phone ?: null,
            'shipping_default' => $this->addr_shipping_default,
            'billing_default' => $this->addr_billing_default,
        ];

        if ($this->addr_shipping_default) {
            $customer->addresses()->where('id', '!=', $this->editingAddressId)->update(['shipping_default' => false]);
        }
        if ($this->addr_billing_default) {
            $customer->addresses()->where('id', '!=', $this->editingAddressId)->update(['billing_default' => false]);
        }

        if ($this->editingAddressId) {
            $customer->addresses()->where('id', $this->editingAddressId)->update($data);
        } else {
            Address::create($data);
        }

        $this->resetAddressForm();
    }

    public function deleteAddress(int $id): void
    {
        $customer = $this->getCustomer();
        $customer?->addresses()->where('id', $id)->delete();
    }

    public function resetAddressForm(): void
    {
        $this->showAddressForm = false;
        $this->editingAddressId = null;
        $this->reset(['addr_first_name', 'addr_last_name', 'addr_line_one', 'addr_city', 'addr_postcode', 'addr_phone', 'addr_shipping_default', 'addr_billing_default']);
    }

    // ── Logout ──

    public function logout(): void
    {
        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();
        $this->redirect($this->localePath('/'));
    }

    // ── Render ──

    public function render()
    {
        $user = Auth::guard('web')->user();
        $orders = collect();
        $addresses = collect();

        if ($user) {
            $customer = $this->getCustomer();

            $orders = Order::where(function ($q) use ($customer, $user) {
                    if ($customer) {
                        $q->where('customer_id', $customer->id)->orWhere('user_id', $user->id);
                    } else {
                        $q->where('user_id', $user->id);
                    }
                })
                ->whereNotNull('placed_at')
                ->with(['lines', 'billingAddress', 'shippingAddress'])
                ->latest('placed_at')
                ->get();

            $addresses = $customer ? $customer->addresses()->with('country')->get() : collect();
        }

        return view('livewire.storefront.account-page', [
            'orders' => $orders,
            'addresses' => $addresses,
        ])->layoutData(['categories' => $this->getCategories()]);
    }

    // ── Helpers ──

    private function getCustomer()
    {
        $user = Auth::guard('web')->user();
        if (! $user) return null;
        return $user->customers()->latest('lunar_customer_user.created_at')->first();
    }

    private function localePath(string $path): string
    {
        $locale = app()->getLocale();
        return ($locale === 'ka' ? '' : '/' . $locale) . $path;
    }

    private function getCategories()
    {
        $group = CollectionGroup::where('handle', 'product-categories')->first();
        return $group
            ? LunarCollection::where('collection_group_id', $group->id)->whereIsRoot()->with(['urls.language'])->get()
            : collect();
    }
}
