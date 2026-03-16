@php
    $locale = app()->getLocale();
    $menu = \Illuminate\Support\Facades\Cache::remember('menu_main', 3600, function () {
        return \App\Models\Menu::with(['items' => fn ($q) => $q->where('is_active', true)->whereNull('parent_id')->orderBy('position')->with(['children' => fn ($q2) => $q2->where('is_active', true)->orderBy('position')])])
            ->where('handle', 'main')->first();
    });
    $drawerItems = $menu?->items ?? collect();
    $prefix = $locale === 'ka' ? '' : '/' . $locale;
@endphp

{{-- Backdrop --}}
<div x-show="mobileNav" x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50" style="background:rgba(0,0,0,0.4)"
     @click="mobileNav = false"></div>

{{-- Drawer (slides from right) --}}
<div x-show="mobileNav" x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="translate-x-full"
     x-transition:enter-end="translate-x-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="translate-x-0"
     x-transition:leave-end="translate-x-full"
     class="fixed top-0 right-0 bottom-0 w-80 max-w-[85vw] bg-white z-50 overflow-y-auto" style="box-shadow:-8px 0 32px rgba(0,0,0,0.1)">
    <div class="flex items-center justify-between p-4" style="border-bottom:1px solid rgba(80,82,157,0.08)">
        <img src="/images/logo.png" alt="GN Industrial" class="h-8 w-auto">
        <button @click="mobileNav = false" class="p-1.5" style="color:#9B9BB0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <ul class="py-1">
        @foreach($drawerItems as $item)
            @php
                $label = $item->getTranslatedLabel($locale);
                $url = $item->getResolvedUrl($locale);
                $hasChildren = $item->children->isNotEmpty();
            @endphp
            <li x-data="{ expanded: false }">
                <div class="flex items-center">
                    <a wire:navigate href="{{ $url }}" @click="mobileNav = false"
                       class="flex-1 px-5 py-3 text-[14px] font-medium" style="color:#2D2D3F">{{ $label }}</a>
                    @if($hasChildren)
                        <button @click="expanded = !expanded" class="px-4 py-3" style="color:#9B9BB0">
                            <svg class="w-4 h-4 transition-transform duration-200" :class="expanded && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    @endif
                </div>
                @if($hasChildren)
                    <ul x-show="expanded" x-cloak x-collapse style="background:#FAFAFC;border-top:1px solid rgba(80,82,157,0.05);border-bottom:1px solid rgba(80,82,157,0.05)">
                        @foreach($item->children as $child)
                            <li>
                                <a wire:navigate href="{{ $child->getResolvedUrl($locale) }}" @click="mobileNav = false"
                                   class="flex items-center gap-3 px-6 py-2.5 text-[13px]" style="color:#4A4A5F">
                                    <span style="width:6px;height:6px;min-width:6px;border-radius:50%;background:#D4D4DE"></span>
                                    {{ $child->getTranslatedLabel($locale) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </li>
        @endforeach
    </ul>
    {{-- Language switcher in drawer --}}
    <div class="px-5 py-3" style="border-top:1px solid rgba(80,82,157,0.06)">
        @livewire('storefront.language-switcher', [], key('mobile-lang'))
    </div>

    <div class="p-5 space-y-3" style="border-top:1px solid rgba(80,82,157,0.06)">
        <a href="tel:+995593737673" class="flex items-center gap-3 text-[13px]" style="color:#4A4A5F">
            <div style="width:32px;height:32px;border-radius:8px;background:rgba(80,82,157,0.06);display:flex;align-items:center;justify-content:center">
                <svg style="width:16px;height:16px;color:#50529D" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            </div>
            +995 593 73 76 73
        </a>
        <a href="mailto:info@gn.ge" class="flex items-center gap-3 text-[13px]" style="color:#4A4A5F">
            <div style="width:32px;height:32px;border-radius:8px;background:rgba(80,82,157,0.06);display:flex;align-items:center;justify-content:center">
                <svg style="width:16px;height:16px;color:#50529D" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            info@gn.ge
        </a>
    </div>
</div>
