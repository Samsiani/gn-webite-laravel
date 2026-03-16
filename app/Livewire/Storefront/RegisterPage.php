<?php

namespace App\Livewire\Storefront;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Customer;

#[Layout('components.layouts.storefront')]
class RegisterPage extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|min:9',
            'password' => 'required|min:6|confirmed',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => __('required'),
            'email.required' => __('required'),
            'email.email' => __('invalid email'),
            'email.unique' => __('already registered'),
            'password.required' => __('required'),
            'password.min' => __('min 6 characters'),
            'password.confirmed' => __('passwords do not match'),
        ];
    }

    public function mount(): void
    {
        if (Auth::guard('web')->check()) {
            $this->redirect($this->localePath('/my-account'));
        }
    }

    public function updated($field): void
    {
        $this->validateOnly($field);
    }

    public function register(): void
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'password' => $this->password,
        ]);

        $nameParts = explode(' ', $this->name, 2);
        $customer = Customer::create([
            'first_name' => $nameParts[0],
            'last_name' => $nameParts[1] ?? '',
            'attribute_data' => [],
        ]);
        $customer->users()->attach($user);

        Auth::guard('web')->login($user);
        session()->regenerate();
        $this->redirect($this->localePath('/my-account'));
    }

    private function localePath(string $path): string
    {
        $locale = app()->getLocale();
        return ($locale === 'ka' ? '' : '/' . $locale) . $path;
    }

    public function render()
    {
        $group = CollectionGroup::where('handle', 'product-categories')->first();
        $categories = $group
            ? LunarCollection::where('collection_group_id', $group->id)->whereIsRoot()->with(['urls.language'])->get()
            : collect();

        return view('livewire.storefront.register-page')
            ->layoutData(['categories' => $categories]);
    }
}
