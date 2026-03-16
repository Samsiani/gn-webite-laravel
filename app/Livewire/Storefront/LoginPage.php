<?php

namespace App\Livewire\Storefront;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\CollectionGroup;

#[Layout('components.layouts.storefront')]
class LoginPage extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ];
    }

    protected function messages(): array
    {
        return [
            'email.required' => __('required'),
            'email.email' => __('invalid email'),
            'password.required' => __('required'),
            'password.min' => __('min 6 characters'),
        ];
    }

    public function mount(): void
    {
        // Already logged in? Go to account
        if (Auth::guard('web')->check()) {
            $this->redirect($this->localePath('/my-account'));
        }
    }

    public function login(): void
    {
        $this->validate();

        if (! Auth::guard('web')->attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', __('Invalid email or password.'));
            return;
        }

        session()->regenerate();
        $this->redirect($this->localePath('/my-account'));
    }

    private function localePath(string $path): string
    {
        $locale = app()->getLocale();
        $prefix = $locale === 'ka' ? '' : '/' . $locale;
        return $prefix . $path;
    }

    public function render()
    {
        $categories = $this->getCategories();

        return view('livewire.storefront.login-page')
            ->layoutData(['categories' => $categories]);
    }

    private function getCategories()
    {
        $group = CollectionGroup::where('handle', 'product-categories')->first();
        return $group
            ? LunarCollection::where('collection_group_id', $group->id)->whereIsRoot()->with(['urls.language'])->get()
            : collect();
    }
}
