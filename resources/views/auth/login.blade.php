<x-auth.screen :title="__('Bienvenido de nuevo')" :subtitle="__('Entra para seguir con tus prácticas y tests.')">
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <x-ui.input name="email" type="email" :label="__('Correo electrónico')" value="{{ old('email') }}" required autofocus autocomplete="email" inputmode="email" />
        <x-ui.input name="password" type="password" :label="__('Contraseña')" required autocomplete="current-password" />
        <div class="flex items-center justify-between gap-3 text-sm">
            <label class="flex items-center gap-2 text-stone-600"><input type="checkbox" name="remember" class="rounded"> {{ __('Recordarme') }}</label>
            <a class="font-medium text-app" href="{{ route('password.request') }}">{{ __('¿Has olvidado la contraseña?') }}</a>
        </div>
        <x-ui.button type="submit" block class="mt-6">{{ __('Entrar') }}</x-ui.button>
    </form>
    <x-slot:footer>{{ __('¿Aún no tienes cuenta?') }} <a class="font-semibold text-app" href="{{ route('register') }}">{{ __('Regístrate') }}</a></x-slot:footer>
</x-auth.screen>
