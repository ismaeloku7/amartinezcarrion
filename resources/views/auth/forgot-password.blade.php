<x-layouts.app :title="__('Recuperar contraseña')">
    <x-ui.card class="mx-auto max-w-md">
        <p class="mb-4 text-sm text-stone-600">{{ __('Te enviaremos un enlace para crear una contraseña nueva.') }}</p>
        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <x-ui.input name="email" type="email" :label="__('Correo electrónico')" value="{{ old('email') }}" required autofocus />
            <x-ui.button type="submit">{{ __('Enviar enlace') }}</x-ui.button>
        </form>
    </x-ui.card>
</x-layouts.app>
