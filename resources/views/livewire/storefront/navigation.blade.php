@php
    $locale = app()->getLocale();
    $megaItem = $menuItems->first(fn($i) => $i->children->isNotEmpty());
    $megaChildren = ($megaItem?->children ?? collect())->filter(function($child) {
        if ($child->type === 'category' && $child->reference_id) {
            return \Lunar\Models\Collection::find($child->reference_id)?->products()->count() > 0;
        }
        return true;
    })->values();
    $megaCols = $megaChildren->isNotEmpty() ? $megaChildren->chunk(ceil($megaChildren->count() / 4)) : collect();
@endphp
<div>
    {{-- Desktop --}}
    <nav class="hidden lg:block relative"
         x-data="{
             megaOpen: false,
             _t: null,
             megaShow() { clearTimeout(this._t); this.megaOpen = true; },
             megaHide() { this._t = setTimeout(() => this.megaOpen = false, 120); }
         }">
        <div class="max-w-[1400px] mx-auto px-4 relative" style="z-index:51">
            <ul class="flex items-center">
                @foreach($menuItems as $item)
                    @php
                        $label = $item->getTranslatedLabel($locale);
                        $url = $item->getResolvedUrl($locale);
                        $hasChildren = $item->children->isNotEmpty();
                    @endphp
                    <li @if($hasChildren)
                            @mouseenter="megaShow()"
                            @mouseleave="megaHide()"
                        @else
                            @mouseenter="clearTimeout(_t); megaOpen = false"
                        @endif>
                        <a wire:navigate href="{{ $url }}"
                           class="relative flex items-center gap-1.5 px-4 py-3.5 text-[13px] font-semibold uppercase tracking-wider transition-colors duration-200 cursor-pointer"
                           :class="megaOpen && {{ $hasChildren ? 'true' : 'false' }} ? 'text-primary' : 'text-gray-600 hover:text-primary'">
                            {{ $label }}
                            @if($hasChildren)
                                <svg class="w-3 h-3 transition-transform duration-300" :class="megaOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                <span class="absolute bottom-0 left-4 right-4 h-[2px] rounded-full transition-all duration-300 pointer-events-none"
                                      :class="megaOpen ? 'bg-primary scale-x-100' : 'bg-transparent scale-x-0'"
                                      style="transform-origin:left"></span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Single Mega Panel --}}
        @if($megaChildren->isNotEmpty())
            <div x-show="megaOpen" x-cloak
                 class="pointer-events-none"
                 style="position:absolute;left:0;right:0;z-index:50;top:100%"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1">

                <div class="pointer-events-auto"
                     style="background:#fff;border-radius:0 0 12px 12px;box-shadow:0 8px 24px rgba(80,82,157,0.08);clip-path:inset(0 -40px -40px -40px)"
                     @mouseenter="megaShow()" @mouseleave="megaHide()">
                    <div class="max-w-[1400px] mx-auto" style="padding:28px 24px 20px">
                        <div class="flex">
                            @foreach($megaCols as $colIndex => $colItems)
                                <div class="flex-1" style="{{ $colIndex < $megaCols->count() - 1 ? 'border-right:1px solid rgba(80,82,157,0.05);padding-right:16px;margin-right:16px' : '' }}">
                                    @foreach($colItems as $child)
                                        @php
                                            $childLabel = $child->getTranslatedLabel($locale);
                                            $childUrl = $child->getResolvedUrl($locale);
                                            $catImage = \Illuminate\Support\Facades\Cache::remember(
                                                'cat_thumb_v2_' . ($child->reference_id ?? 0), 3600,
                                                function () use ($child) {
                                                    if ($child->type === 'category' && $child->reference_id) {
                                                        $col = \Lunar\Models\Collection::with('media')->find($child->reference_id);
                                                        if ($col) {
                                                            $img = $col->getFirstMediaUrl('images', 'thumb') ?: $col->getFirstMediaUrl('images');
                                                            if ($img) return $img;
                                                            $fp = $col->products()->with('media')->first();
                                                            return $fp?->getFirstMediaUrl('images', 'thumb') ?: $fp?->getFirstMediaUrl('images');
                                                        }
                                                    }
                                                    return null;
                                                }
                                            );
                                        @endphp
                                        <a wire:navigate href="{{ $childUrl }}"
                                           class="flex items-center gap-3 py-2 px-2.5 rounded-lg cursor-pointer"
                                           style="transition:background 0.15s ease"
                                           onmouseover="this.style.background='rgba(80,82,157,0.04)'"
                                           onmouseout="this.style.background='transparent'"
                                           @click="megaOpen = false">
                                            <div style="width:48px;height:48px;min-width:48px;border-radius:8px;overflow:hidden;background:#F7F7FA;display:flex;align-items:center;justify-content:center">
                                                @if($catImage)
                                                    <img src="{{ $catImage }}" alt="{{ $childLabel }}" loading="eager" fetchpriority="low"
                                                         style="width:100%;height:100%;object-fit:contain;padding:3px">
                                                @else
                                                    <svg style="width:22px;height:22px;color:#C8C8D8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                    </svg>
                                                @endif
                                            </div>
                                            <span style="font-size:13px;font-weight:500;color:#2D2D3F;transition:color 0.15s ease"
                                                  onmouseover="this.style.color='#50529D'"
                                                  onmouseout="this.style.color='#2D2D3F'">{{ $childLabel }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>

                        <div class="flex items-center justify-between" style="margin-top:16px;padding-top:14px;border-top:1px solid rgba(80,82,157,0.05)">
                            <a wire:navigate href="{{ $megaItem->getResolvedUrl($locale) }}"
                               class="cursor-pointer"
                               style="font-size:13px;font-weight:600;color:#50529D;display:inline-flex;align-items:center;gap:6px;transition:gap 0.2s ease"
                               onmouseover="this.style.gap='10px'" onmouseout="this.style.gap='6px'"
                               @click="megaOpen = false">
                                {{ __('View All') }} {{ $megaItem->getTranslatedLabel($locale) }}
                                <svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </a>
                            <div class="flex items-center" style="gap:20px;font-size:11px;color:#9B9BB0">
                                <span class="flex items-center" style="gap:5px">
                                    <svg style="width:13px;height:13px;color:#22c55e" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    {{ __('Official Warranty') }}
                                </span>
                                <span class="flex items-center" style="gap:5px">
                                    <svg style="width:13px;height:13px;color:#3b82f6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    {{ __('Fast Delivery') }}
                                </span>
                                <span class="flex items-center" style="gap:5px">
                                    <svg style="width:13px;height:13px;color:#f59e0b" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    {{ __('Expert Support') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Click-away layer (invisible, closes mega menu) --}}
            <div x-show="megaOpen" x-cloak
                 class="fixed inset-0" style="z-index:39"
                 @click="megaOpen = false"></div>
        @endif
    </nav>
</div>
