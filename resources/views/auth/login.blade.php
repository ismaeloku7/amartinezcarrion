<x-layouts.app :title="__('Entrar')">
    <x-ui.card class="mx-auto max-w-md">
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <x-ui.input name="email" type="email" :label="__('Correo electrónico')" value="{{ old('email') }}" required autofocus autocomplete="email" />
            <x-ui.input name="password" type="password" :label="__('Contraseña')" required autocomplete="current-password" />
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" class="rounded"> {{ __('Recordarme') }}</label>
            <div class="flex items-center justify-between">
                <a class="text-sm text-stone-600 underline" href="{{ route('password.request') }}">{{ __('¿Has olvidado la contraseña?') }}</a>
                <x-ui.button type="submit">{{ __('Entrar') }}</x-ui.button>
            </div>
        </form>
        <p class="mt-6 text-center text-sm text-stone-600">{{ __('¿Aún no tienes cuenta?') }} <a class="underline" href="{{ route('register') }}">{{ __('Regístrate') }}</a></p>
    </x-ui.card>
</x-layouts.app>
