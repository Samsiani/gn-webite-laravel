<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex items-center gap-3">
            <x-filament::button type="submit">
                Save Settings
            </x-filament::button>

            <x-filament::button color="gray" wire:click="testEmail" type="button">
                <span wire:loading.remove wire:target="testEmail">Send Test Email</span>
                <span wire:loading wire:target="testEmail">Sending...</span>
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
