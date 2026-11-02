<x-layouts.app :title="__('Crear cuenta')">
    <x-ui.card class="mx-auto max-w-md">
        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <x-ui.input name="name" :label="__('Nombre')" value="{{ old('name') }}" required autofocus />
            <x-ui.input name="email" type="email" :label="__('Correo electrónico')" value="{{ old('email') }}" required />
            <x-ui.input name="password" type="password" :label="__('Contraseña')" required autocomplete="new-password" />
            <x-ui.input name="password_confirmation" type="password" :label="__('Repite la contraseña')" required />
            <x-ui.select name="locale" :label="__('Idioma')">
                @foreach (\App\Models\Language::query()->where('active', true)->orderBy('position')->get() as $lang)
                    <option value="{{ $lang->code }}" @selected(old('locale', app()->getLocale()) === $lang->code)>{{ $lang->name }}</option>
                @endforeach
            </x-ui.select>
            <input type="hidden" name="timezone" id="timezone" value="{{ old('timezone', 'Europe/Madrid') }}">
            <script>document.getElementById('timezone').value = Intl.DateTimeFormat().resolvedOptions().timeZone || 'Europe/Madrid';</script>
            <div class="flex items-center justify-between">
                <a class="text-sm text-stone-600 underline" href="{{ route('login') }}">{{ __('Ya tengo cuenta') }}</a>
                <x-ui.button type="submit">{{ __('Registrarme') }}</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
