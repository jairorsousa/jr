<x-guest-layout>
    <h2 class="text-xl font-bold text-mono-900 text-center mb-6">Entrar no Sistema</h2>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email -->
        <x-jr.input
            label="E-mail"
            icon="email"
            type="email"
            name="email"
            :value="old('email')"
            required
            autofocus
            autocomplete="username"
            placeholder="seu@email.com"
            :error="$errors->first('email')"
        />

        <!-- Password -->
        <x-jr.input
            label="Senha"
            icon="vpn_key"
            type="password"
            name="password"
            required
            autocomplete="current-password"
            placeholder="Sua senha"
            :error="$errors->first('password')"
        />

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                       class="rounded border-mono-200 text-primary-500 focus:ring-primary-500"
                       name="remember">
                <span class="ms-2 text-sm text-mono-600">Lembrar de mim</span>
            </label>
        </div>

        <x-jr.button type="submit" variant="primary" class="w-full">
            Entrar
        </x-jr.button>
    </form>
</x-guest-layout>
