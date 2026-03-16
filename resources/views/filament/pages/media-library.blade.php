<x-filament-panels::page>
    <div x-data="{ selected: null, mediaItems: @js($this->media->items()) }" class="space-y-4">
        {{-- Toolbar --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex flex-1 items-center gap-3 w-full sm:w-auto">
                <input type="search" wire:model.live.debounce.300ms="search"
                       placeholder="Search files..."
                       class="flex-1 sm:w-64 rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <select wire:model.live="filterCollection"
                        class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">All Collections</option>
                    @foreach($this->collections as $col)
                        <option value="{{ $col }}">{{ $col }}</option>
                    @endforeach
                </select>
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ $this->media->total() }} files
            </div>
        </div>

        {{-- Media Grid --}}
        @if($this->media->isNotEmpty())
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:12px">
            @foreach($this->media as $index => $item)
                <div class="group relative bg-white dark:bg-gray-800 rounded-lg border-2 cursor-pointer overflow-hidden transition-all"
                     :class="selected?.id === {{ $item->id }} ? 'border-primary-500 ring-2 ring-primary-500/30' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'"
                     @click="selected = {
                         id: {{ $item->id }},
                         url: '{{ $item->getUrl() }}',
                         file_name: '{{ addslashes($item->file_name) }}',
                         name: '{{ addslashes($item->name) }}',
                         mime_type: '{{ $item->mime_type }}',
                         size: {{ $item->size }},
                         collection: '{{ $item->collection_name }}',
                         model_type: '{{ $item->model_type }}',
                         created: '{{ $item->created_at->format('d.m.Y H:i') }}',
                         width: '{{ $item->getCustomProperty('width', '-') }}',
                         height: '{{ $item->getCustomProperty('height', '-') }}'
                     }">
                    <div class="aspect-square bg-gray-50 dark:bg-gray-900 flex items-center justify-center overflow-hidden">
                        @if(str_starts_with($item->mime_type, 'image/'))
                            <img src="{{ $item->getUrl() }}" alt="{{ $item->file_name }}"
                                 class="w-full h-full object-contain p-1" loading="lazy">
                        @else
                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        @endif
                    </div>
                    {{-- Filename --}}
                    <div class="p-1.5">
                        <p class="text-[10px] text-gray-600 dark:text-gray-400 truncate">{{ $item->file_name }}</p>
                    </div>
                    {{-- Selected check --}}
                    <div x-show="selected?.id === {{ $item->id }}" x-cloak
                         class="absolute top-1.5 right-1.5 w-5 h-5 bg-primary-500 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $this->media->links() }}
        </div>

        {{-- Detail Sidebar (WordPress-style modal) --}}
        <template x-teleport="body">
            <div x-show="selected" x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-[100] flex" @keydown.escape.window="selected = null">

                {{-- Backdrop --}}
                <div class="absolute inset-0 bg-black/60" @click="selected = null"></div>

                {{-- Modal Content --}}
                <div x-show="selected"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full"
                     class="relative ml-auto flex h-full w-full max-w-4xl bg-white dark:bg-gray-800 shadow-2xl">

                    {{-- Image Preview --}}
                    <div class="flex-1 bg-gray-100 dark:bg-gray-900 flex items-center justify-center p-8 min-w-0">
                        <img :src="selected?.url" :alt="selected?.file_name"
                             class="max-w-full max-h-full object-contain rounded-lg shadow-lg">
                    </div>

                    {{-- Info Sidebar --}}
                    <div class="w-72 border-l border-gray-200 dark:border-gray-700 flex flex-col shrink-0">
                        {{-- Header --}}
                        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="font-semibold text-sm text-gray-900 dark:text-white">Attachment Details</h3>
                            <button @click="selected = null" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        {{-- Details --}}
                        <div class="flex-1 overflow-y-auto p-4 space-y-4 text-sm">
                            {{-- Filename --}}
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">File Name</label>
                                <p class="mt-1 text-gray-900 dark:text-white break-all" x-text="selected?.file_name"></p>
                            </div>

                            {{-- Type --}}
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</label>
                                <p class="mt-1 text-gray-900 dark:text-white" x-text="selected?.mime_type"></p>
                            </div>

                            {{-- Size --}}
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Size</label>
                                <p class="mt-1 text-gray-900 dark:text-white">
                                    <span x-text="selected ? (selected.size / 1024).toFixed(1) + ' KB' : ''"></span>
                                </p>
                            </div>

                            {{-- Collection --}}
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Collection</label>
                                <p class="mt-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300"
                                          x-text="selected?.collection"></span>
                                </p>
                            </div>

                            {{-- Model --}}
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Attached To</label>
                                <p class="mt-1 text-gray-900 dark:text-white" x-text="selected?.model_type || 'Unattached'"></p>
                            </div>

                            {{-- Date --}}
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Uploaded</label>
                                <p class="mt-1 text-gray-900 dark:text-white" x-text="selected?.created"></p>
                            </div>

                            {{-- URL --}}
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">URL</label>
                                <div class="mt-1 flex items-center gap-1">
                                    <input type="text" :value="selected?.url" readonly
                                           class="flex-1 text-xs rounded border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 dark:text-white py-1 px-2"
                                           @click="$event.target.select()">
                                    <button @click="navigator.clipboard.writeText(selected?.url)"
                                            class="shrink-0 p-1.5 text-gray-400 hover:text-primary-500 transition" title="Copy URL">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="p-4 border-t border-gray-200 dark:border-gray-700 space-y-2">
                            <a :href="selected?.url" target="_blank"
                               class="flex items-center justify-center gap-2 w-full py-2 px-4 bg-primary-500 text-white rounded-lg text-sm font-medium hover:bg-primary-600 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                View Full Size
                            </a>
                            <button @click="if(confirm('Delete this file permanently?')) { $wire.deleteMedia(selected.id); selected = null; }"
                                    class="flex items-center justify-center gap-2 w-full py-2 px-4 border border-red-300 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Delete Permanently
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        @else
            <div class="text-center py-12">
                <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm text-gray-500 dark:text-gray-400">No media files found</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
