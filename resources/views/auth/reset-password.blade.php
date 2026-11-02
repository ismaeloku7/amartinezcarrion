<x-auth.screen :title="__('Nueva contraseña')" :subtitle="__('Elige una contraseña nueva para tu cuenta.')">
    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ request()->route('token') }}">
        <x-ui.input name="email" type="email" :label="__('Correo electrónico')" value="{{ old('email', request('email')) }}" required autocomplete="email" />
        <x-ui.input name="password" type="password" :label="__('Contraseña')" required autocomplete="new-password" />
        <x-ui.input name="password_confirmation" type="password" :label="__('Repite la contraseña')" required autocomplete="new-password" />
        <x-ui.button type="submit" block class="mt-6">{{ __('Guardar') }}</x-ui.button>
    </form>
</x-auth.screen>
