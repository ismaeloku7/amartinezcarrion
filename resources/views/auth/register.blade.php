<x-auth.screen :title="__('Crear cuenta')" :subtitle="__('Libros, prácticas y tests de Antonio Martínez.')">
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf
        <x-ui.input name="name" :label="__('Nombre')" value="{{ old('name') }}" required autofocus autocomplete="name" />
        <x-ui.input name="email" type="email" :label="__('Correo electrónico')" value="{{ old('email') }}" required autocomplete="email" inputmode="email" />
        <x-ui.input name="password" type="password" :label="__('Contraseña')" required autocomplete="new-password" />
        <x-ui.input name="password_confirmation" type="password" :label="__('Repite la contraseña')" required autocomplete="new-password" />
        <x-ui.select name="locale" :label="__('Idioma')">
            @foreach (\App\Models\Language::query()->where('active', true)->orderBy('position')->get() as $lang)
                <option value="{{ $lang->code }}" @selected(old('locale', app()->getLocale()) === $lang->code)>{{ $lang->name }}</option>
            @endforeach
        </x-ui.select>
        <input type="hidden" name="timezone" id="timezone" value="{{ old('timezone', 'Europe/Madrid') }}">
        <script>document.getElementById('timezone').value = Intl.DateTimeFormat().resolvedOptions().timeZone || 'Europe/Madrid';</script>
        <x-ui.button type="submit" block class="mt-6">{{ __('Registrarme') }}</x-ui.button>
    </form>
    <x-slot:footer>{{ __('¿Ya tienes cuenta?') }} <a class="font-semibold text-app" href="{{ route('login') }}">{{ __('Entrar') }}</a></x-slot:footer>
</x-auth.screen>
