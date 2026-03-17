<div class="flex items-center gap-3">
    <a wire:navigate href="{{ $links['ka'] }}" class="{{ $locale === 'ka' ? 'font-bold' : 'opacity-90 hover:opacity-100' }} transition">GE</a>
    <span class="opacity-30">|</span>
    <a wire:navigate href="{{ $links['en'] }}" class="{{ $locale === 'en' ? 'font-bold' : 'opacity-90 hover:opacity-100' }} transition">EN</a>
    <span class="opacity-30">|</span>
    <a wire:navigate href="{{ $links['ru'] }}" class="{{ $locale === 'ru' ? 'font-bold' : 'opacity-90 hover:opacity-100' }} transition">RU</a>
</div>
