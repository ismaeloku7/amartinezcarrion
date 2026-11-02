<x-layouts.app :title="__('Nueva contraseña')">
    <x-ui.card class="mx-auto max-w-md">
        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ request()->route('token') }}">
            <x-ui.input name="email" type="email" :label="__('Correo electrónico')" value="{{ old('email', request('email')) }}" required />
            <x-ui.input name="password" type="password" :label="__('Contraseña')" required autocomplete="new-password" />
            <x-ui.input name="password_confirmation" type="password" :label="__('Repite la contraseña')" required />
            <x-ui.button type="submit">{{ __('Guardar') }}</x-ui.button>
        </form>
    </x-ui.card>
</x-layouts.app>
