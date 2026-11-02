{{-- Barra de acción pegada abajo, al alcance del pulgar. Deja sitio para la barra de pestañas si la hay. --}}
<div {{ $attributes->merge(['class' => 'sticky bottom-[calc(var(--tabbar)+env(safe-area-inset-bottom))] z-20 -mx-4 mt-6 bg-gradient-to-t from-canvas from-60% to-transparent px-4 pb-3 pt-6 md:bottom-0']) }}>
    <div class="flex items-center gap-2">{{ $slot }}</div>
</div>
