<x-guest-layout>
    <div class="text-center mb-8">
        <h1 class="font-area-inktrap text-3xl font-bold text-ddu-lavanda mb-2">
            Iniciar Sesión
        </h1>
        <p class="font-lato text-gray-600 text-sm">
            Accede a tu panel de administración DDU
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="font-lato block text-sm font-semibold text-gray-700 mb-2">
                Correo Electrónico
            </label>
            <input id="email"
                   type="email"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   autofocus
                   autocomplete="username"
                   class="font-lato block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-ddu-aqua focus:border-ddu-aqua transition-all duration-200 bg-gray-50 focus:bg-white"
                   placeholder="tu@email.com">
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="font-lato block text-sm font-semibold text-gray-700 mb-2">
                Contraseña
            </label>
            <input id="password"
                   type="password"
                   name="password"
                   required
                   autocomplete="current-password"
                   class="font-lato block w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-ddu-aqua focus:border-ddu-aqua transition-all duration-200 bg-gray-50 focus:bg-white"
                   placeholder="••••••••">
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- DDU Access Error (hidden, only for modal trigger) -->
        @if ($errors->has('ddu_access'))
            <div class="hidden">
                <x-input-error :messages="$errors->get('ddu_access')" class="mt-2" />
            </div>
        @endif

        <!-- Remember Me -->
        <div class="flex items-center">
            <input id="remember_me"
                   type="checkbox"
                   name="remember"
                   class="w-4 h-4 text-ddu-lavanda bg-gray-100 border-gray-300 rounded focus:ring-ddu-aqua focus:ring-2">
            <label for="remember_me" class="font-lato ml-3 text-sm text-gray-600">
                Recordar mi sesión
            </label>
        </div>

        <!-- Submit Button -->
        <button type="submit"
                class="w-full bg-ddu-lavenda hover:bg-ddu-aqua text-white font-lato font-semibold py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-ddu-aqua focus:ring-opacity-50 shadow-lg"
                style="background: linear-gradient(135deg, #6F78E4 0%, #6DDEDD 100%);">
            <span class="font-zuume tracking-wider">INICIAR SESIÓN</span>
        </button>

        <!-- Forgot Password Link -->
        @if (Route::has('password.request'))
            <div class="text-center mt-6">
                <a href="{{ route('password.request') }}"
                   class="font-lato text-sm text-ddu-lavanda hover:text-ddu-aqua transition-colors duration-200">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>
        @endif

        <!-- Register Link -->
        @if (Route::has('register'))
            <div class="text-center mt-4 pt-6 border-t border-gray-200">
                <p class="font-lato text-sm text-gray-600">
                    ¿No tienes cuenta?
                    <a href="{{ route('register') }}"
                       class="text-ddu-lavanda hover:text-ddu-aqua font-semibold transition-colors duration-200">
                        Regístrate aquí
                    </a>
                </p>
            </div>
        @endif
    </form>

    <!-- Modal de Acceso Denegado DDU -->
    @if ($errors->has('ddu_access'))
        <div id="ddu-access-modal" class="ddu-modal-overlay animate-fade-in-up">
            <div class="ddu-modal-content animate-bounce-in">
                <div class="text-center">
                    <!-- Icono de advertencia -->
                    <div class="ddu-modal-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.268 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>

                    <!-- Título -->
                    <h3 class="ddu-modal-title">
                        Acceso Denegado
                    </h3>

                    <!-- Mensaje -->
                    <div class="ddu-modal-message">
                        <p>
                            {{ $errors->first('ddu_access') }}
                        </p>
                    </div>

                    <!-- Botón de cerrar -->
                    <button type="button" class="ddu-modal-button">
                        <span>ENTENDIDO</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Estilos y Scripts DDU Modal -->
    @vite(['resources/css/ddu-modal.css', 'resources/js/ddu-modal.js'])
</x-guest-layout>
