<x-auth.screen :title="__('Recuperar contraseña')" :subtitle="__('Te enviaremos un enlace para crear una contraseña nueva.')">
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf
        <x-ui.input name="email" type="email" :label="__('Correo electrónico')" value="{{ old('email') }}" required autofocus autocomplete="email" inputmode="email" />
        <x-ui.button type="submit" block class="mt-6">{{ __('Enviar enlace') }}</x-ui.button>
    </form>
    <x-slot:footer><a class="font-semibold text-app" href="{{ route('login') }}">{{ __('Volver a entrar') }}</a></x-slot:footer>
</x-auth.screen>
