<article class="-mx-4 -mt-6">
    <header class="px-4 pb-10 pt-10 text-white" style="background: linear-gradient(160deg, {{ $content->color() }} 0%, {{ $content->color() }}dd 60%, #1c1917 140%)">
        <div class="mx-auto max-w-3xl">
            <div class="flex flex-wrap items-center gap-2 text-xs uppercase tracking-wide opacity-80">
                @if ($content->topic)<a href="{{ route('contents.topic', $content->topic) }}" class="underline-offset-2 hover:underline">{{ $content->topic->name }}</a> ·@endif
                <span>{{ $content->type === 'course' ? __('Curso') : __('Artículo') }}</span> · <span>{{ __(':n min de lectura', ['n' => $content->readingMinutes()]) }}</span>
            </div>
            <h1 class="mt-3 text-3xl font-bold leading-tight tracking-tight sm:text-4xl">{{ $content->title }}</h1>
            @if ($content->excerpt)<p class="mt-3 max-w-2xl text-base opacity-90 sm:text-lg">{{ $content->excerpt }}</p>@endif
            @if ($content->author)<div class="mt-4 text-sm opacity-80">{{ $content->author }}</div>@endif
        </div>
    </header>

    <div class="mx-auto max-w-3xl px-4 py-8">
        @if ($locked === 'login')
            <x-ui.alert>{{ __('Inicia sesión para leer este material.') }} <a class="underline" href="{{ route('login') }}">{{ __('Entrar') }}</a></x-ui.alert>
        @elseif ($locked === 'paywall')
            <x-ui.alert type="warning">{{ __('Este material forma parte de un contenido de pago.') }} <a class="underline" href="{{ route('paywall', ['product' => $content->product->key]) }}">{{ __('Ver planes') }}</a></x-ui.alert>
        @else
            <div class="article" style="--article-color: {{ $content->color() }}">{!! $html !!}</div>
        @endif

        @if ($related->isNotEmpty())
            <section class="mt-10 border-t border-stone-200 pt-6">
                <h2 class="mb-3 text-lg font-semibold">{{ __('Relacionados') }}</h2>
                <div class="space-y-3">@foreach ($related as $r)<x-contents.card :content="$r" />@endforeach</div>
            </section>
        @endif
    </div>
</article>
