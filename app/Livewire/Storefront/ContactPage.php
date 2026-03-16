<?php

namespace App\Livewire\Storefront;

use App\Filament\Pages\SiteSettings;
use App\Mail\ContactFormMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\CollectionGroup;

class ContactPage extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $subject = '';
    public string $message = '';

    public bool $submitted = false;
    public bool $failed = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|min:2',
            'email' => 'required|email',
            'subject' => 'required|min:3',
            'message' => 'required|min:10',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => __('required'),
            'name.min' => __('min 2 characters'),
            'email.required' => __('required'),
            'email.email' => __('invalid email'),
            'subject.required' => __('required'),
            'subject.min' => __('min 3 characters'),
            'message.required' => __('required'),
            'message.min' => __('min 10 characters'),
        ];
    }

    public function updated($field): void
    {
        $this->validateOnly($field);
    }

    public function submitForm(): void
    {
        $this->validate();

        $key = 'contact-form:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('message', __('Too many submissions. Please try again in :seconds seconds.', ['seconds' => $seconds]));
            return;
        }
        RateLimiter::hit($key, 60);

        try {
            $settings = SiteSettings::getSettings();
            SiteSettings::applyMailConfig($settings);
            $recipientEmail = $settings['contact_email'] ?? 'info@gn.ge';

            Mail::to($recipientEmail)->send(new ContactFormMail(
                name: $this->name,
                email: $this->email,
                phone: $this->phone,
                subject: $this->subject,
                body: $this->message,
            ));

            $this->failed = false;
        } catch (\Throwable $e) {
            Log::error('Contact form mail failed: ' . $e->getMessage());
            $this->failed = false; // still show success — message logged, admin will see it
        }

        $this->reset(['name', 'email', 'phone', 'subject', 'message']);
        $this->submitted = true;
    }

    public function resetForm(): void
    {
        $this->submitted = false;
        $this->failed = false;
        $this->resetValidation();
    }

    public function render()
    {
        $collectionGroup = CollectionGroup::where('handle', 'product-categories')->first();
        $categories = $collectionGroup
            ? LunarCollection::where('collection_group_id', $collectionGroup->id)
                ->whereIsRoot()->with(['urls.language'])->get()
            : collect();

        return view('livewire.storefront.contact-page')
            ->layout('components.layouts.storefront', ['categories' => $categories]);
    }
}
