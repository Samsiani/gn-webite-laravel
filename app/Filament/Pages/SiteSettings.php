<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 9;
    protected static ?string $title = 'Site Settings';
    protected static ?string $slug = 'site-settings';
    protected static string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    protected static string $settingsFile = 'site-settings.json';

    public function mount(): void
    {
        $this->form->fill(static::getSettings());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('SEO — Global')
                    ->description('Search engine optimization defaults')
                    ->icon('heroicon-o-magnifying-glass')
                    ->schema([
                        Toggle::make('seo_noindex')
                            ->label('Block search engine indexing (noindex, nofollow)')
                            ->helperText('Keep ON while site is in staging. Turn OFF when going live.')
                            ->default(true),
                        TextInput::make('seo_title_suffix')
                            ->label('Title Suffix')
                            ->placeholder(' — GN Industrial')
                            ->helperText('Appended to every page title (e.g. " — GN Industrial")'),
                        Textarea::make('seo_default_description')
                            ->label('Default Meta Description')
                            ->placeholder('Professional kitchen equipment for restaurants, hotels, and food industry.')
                            ->rows(2)
                            ->helperText('Used when a page has no specific description'),
                        TextInput::make('seo_og_image')
                            ->label('Default OG Image URL')
                            ->placeholder('https://laravel.gn.ge/images/og-default.jpg')
                            ->helperText('Default social sharing image (1200x630px recommended)'),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make('SEO — LocalBusiness Schema')
                    ->description('Structured data for Google Rich Results')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        TextInput::make('schema_business_name')
                            ->label('Business Name')
                            ->placeholder('GN Industrial'),
                        TextInput::make('schema_business_type')
                            ->label('Business Type')
                            ->placeholder('Store')
                            ->helperText('Schema.org type: Store, Restaurant, LocalBusiness, etc.'),
                        TextInput::make('schema_street')
                            ->label('Street Address')
                            ->placeholder('Kaishi Street #15'),
                        TextInput::make('schema_city')
                            ->label('City')
                            ->placeholder('Tbilisi'),
                        TextInput::make('schema_postal')
                            ->label('Postal Code')
                            ->placeholder('1103'),
                        TextInput::make('schema_country')
                            ->label('Country')
                            ->placeholder('GE'),
                        TextInput::make('schema_phone')
                            ->label('Phone')
                            ->placeholder('+995 593 73 76 73'),
                        TextInput::make('schema_email')
                            ->label('Email')
                            ->placeholder('info@gn.ge'),
                        TextInput::make('schema_logo')
                            ->label('Logo URL')
                            ->placeholder('https://laravel.gn.ge/images/logo.png')
                            ->helperText('Full URL to your logo for Schema.org'),
                        TextInput::make('schema_url')
                            ->label('Website URL')
                            ->placeholder('https://gn.ge')
                            ->helperText('Primary website URL for Schema.org'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                Section::make('Contact Form')
                    ->description('Where contact form submissions are sent')
                    ->icon('heroicon-o-envelope')
                    ->schema([
                        TextInput::make('contact_email')
                            ->label('Recipient Email')
                            ->email()
                            ->required()
                            ->placeholder('info@gn.ge')
                            ->helperText('Contact form messages will be sent to this address'),
                    ])
                    ->columns(1),

                Section::make('SMTP Settings')
                    ->description('Email delivery configuration')
                    ->icon('heroicon-o-server-stack')
                    ->schema([
                        TextInput::make('mail_host')
                            ->label('SMTP Host')
                            ->placeholder('smtp.gmail.com')
                            ->required(),
                        TextInput::make('mail_port')
                            ->label('SMTP Port')
                            ->numeric()
                            ->placeholder('587')
                            ->required(),
                        Select::make('mail_encryption')
                            ->label('Encryption')
                            ->options([
                                'tls' => 'TLS',
                                'ssl' => 'SSL',
                                '' => 'None',
                            ])
                            ->default('tls'),
                        TextInput::make('mail_username')
                            ->label('SMTP Username')
                            ->placeholder('your@gmail.com'),
                        TextInput::make('mail_password')
                            ->label('SMTP Password')
                            ->password()
                            ->revealable(),
                        TextInput::make('mail_from_address')
                            ->label('From Address')
                            ->email()
                            ->placeholder('noreply@gn.ge'),
                        TextInput::make('mail_from_name')
                            ->label('From Name')
                            ->placeholder('GN Industrial'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Encrypt SMTP password before storing
        if (! empty($data['mail_password'])) {
            $data['mail_password'] = encrypt($data['mail_password']);
        }

        file_put_contents(
            storage_path(static::$settingsFile),
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    public function testEmail(): void
    {
        $settings = $this->form->getState();

        if (empty($settings['mail_host']) || empty($settings['contact_email'])) {
            Notification::make()
                ->title('Please fill SMTP settings and save first')
                ->danger()
                ->send();
            return;
        }

        // Save with encrypted password
        $toSave = $settings;
        if (! empty($toSave['mail_password'])) {
            $toSave['mail_password'] = encrypt($toSave['mail_password']);
        }
        file_put_contents(
            storage_path(static::$settingsFile),
            json_encode($toSave, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        // Apply SMTP config on the fly (uses plaintext from form)
        static::applyMailConfig($settings);

        try {
            Mail::raw('This is a test email from GN Industrial contact form settings.', function ($message) use ($settings) {
                $message->to($settings['contact_email'])
                    ->subject('Test Email — GN Industrial');
            });

            Notification::make()
                ->title('Test email sent to ' . $settings['contact_email'])
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Email failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public static function getSettings(): array
    {
        $file = storage_path(static::$settingsFile);

        $defaults = [
            // SEO
            'seo_noindex' => true,
            'seo_title_suffix' => ' — GN Industrial',
            'seo_default_description' => 'Professional kitchen equipment for restaurants, hotels, and food industry.',
            'seo_og_image' => '',
            // Schema
            'schema_business_name' => 'GN Industrial',
            'schema_business_type' => 'Store',
            'schema_street' => 'Kaishi Street #15',
            'schema_city' => 'Tbilisi',
            'schema_postal' => '1103',
            'schema_country' => 'GE',
            'schema_phone' => '+995 593 73 76 73',
            'schema_email' => 'info@gn.ge',
            'schema_logo' => 'https://laravel.gn.ge/images/logo.png',
            'schema_url' => 'https://gn.ge',
            // Contact/Mail
            'contact_email' => 'info@gn.ge',
            'mail_host' => '',
            'mail_port' => '587',
            'mail_encryption' => 'tls',
            'mail_username' => '',
            'mail_password' => '',
            'mail_from_address' => '',
            'mail_from_name' => 'GN Industrial',
        ];

        if (! file_exists($file)) {
            return $defaults;
        }

        $data = array_merge($defaults, json_decode(file_get_contents($file), true) ?: []);

        // Decrypt SMTP password
        if (! empty($data['mail_password'])) {
            try {
                $data['mail_password'] = decrypt($data['mail_password']);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                // Already plaintext (legacy) — will be encrypted on next save
            }
        }

        return $data;
    }

    public static function applyMailConfig(?array $settings = null): void
    {
        $settings ??= static::getSettings();

        if (! empty($settings['mail_host'])) {
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.host', $settings['mail_host']);
            Config::set('mail.mailers.smtp.port', (int) ($settings['mail_port'] ?? 587));
            Config::set('mail.mailers.smtp.encryption', $settings['mail_encryption'] ?: null);
            Config::set('mail.mailers.smtp.username', $settings['mail_username'] ?? '');
            Config::set('mail.mailers.smtp.password', $settings['mail_password'] ?? '');
            Config::set('mail.from.address', $settings['mail_from_address'] ?? $settings['mail_username'] ?? 'noreply@gn.ge');
            Config::set('mail.from.name', $settings['mail_from_name'] ?? 'GN Industrial');
        }
    }
}
