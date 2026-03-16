<x-filament-panels::page>
    <div class="space-y-4">
        {{-- Search & Actions --}}
        <div class="flex items-center justify-between gap-4">
            <div class="flex-1 max-w-md">
                <input type="search" wire:model.live.debounce.300ms="search"
                       placeholder="Search translations..."
                       class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            <div class="flex gap-2">
                <x-filament::button wire:click="addTranslation" color="gray" size="sm">
                    Add New
                </x-filament::button>
                <x-filament::button wire:click="save" size="sm">
                    Save All
                </x-filament::button>
            </div>
        </div>

        {{-- Translation Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900">
                        <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-8">#</th>
                        <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Key (English)</th>
                        <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Georgian (KA)</th>
                        <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Russian (RU)</th>
                        <th class="px-3 py-2.5 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($this->getFilteredTranslations() as $index => $translation)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                            <td class="px-3 py-1.5 text-gray-400 text-xs">{{ $index + 1 }}</td>
                            <td class="px-3 py-1.5">
                                <input type="text"
                                       wire:model.blur="translations.{{ $index }}.en"
                                       class="w-full rounded border-gray-200 dark:border-gray-600 text-xs py-1.5 px-2 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:bg-gray-700 dark:text-white">
                            </td>
                            <td class="px-3 py-1.5">
                                <input type="text"
                                       wire:model.blur="translations.{{ $index }}.ka"
                                       class="w-full rounded border-gray-200 dark:border-gray-600 text-xs py-1.5 px-2 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                                       @if(empty($translation['ka'])) style="background-color: #fef3c7" @endif>
                            </td>
                            <td class="px-3 py-1.5">
                                <input type="text"
                                       wire:model.blur="translations.{{ $index }}.ru"
                                       class="w-full rounded border-gray-200 dark:border-gray-600 text-xs py-1.5 px-2 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:bg-gray-700 dark:text-white"
                                       @if(empty($translation['ru'])) style="background-color: #fef3c7" @endif>
                            </td>
                            <td class="px-3 py-1.5">
                                <button wire:click="removeTranslation({{ $index }})"
                                        class="text-gray-300 hover:text-red-500 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="text-xs text-gray-400">
            {{ count($this->translations) }} translations total.
            Missing translations are highlighted in yellow.
        </div>
    </div>
</x-filament-panels::page>
