<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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

        if (! file_exists($file)) {
            return [
                'contact_email' => 'info@gn.ge',
                'mail_host' => '',
                'mail_port' => '587',
                'mail_encryption' => 'tls',
                'mail_username' => '',
                'mail_password' => '',
                'mail_from_address' => '',
                'mail_from_name' => 'GN Industrial',
            ];
        }

        $data = json_decode(file_get_contents($file), true) ?: [];

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
