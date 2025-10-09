<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Panel DDU') }} - Dashboard</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- DDU Custom Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/dashboard.css', 'resources/js/dashboard.js', 'resources/css/ddu-modal.css'])

    <!-- Custom DDU Colors -->
    <style>
        :root {
            --ddu-lavanda: #6F78E4;
            --ddu-aqua: #6DDEDD;
            --ddu-lavanda-light: #8B93EA;
            --ddu-aqua-light: #8DE8E3;
            --ddu-gradient: linear-gradient(135deg, #6F78E4 0%, #6DDEDD 100%);
        }
    </style>
</head>
<body class="font-lato antialiased bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <nav class="w-64 bg-white shadow-lg border-r border-gray-200">
            <!-- Logo/Brand -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--ddu-gradient);">
                        <span class="text-white font-bold text-lg">DDU</span>
                    </div>
                    <div>
                        <h1 class="font-bold text-gray-900 text-lg">Panel DDU</h1>
                        <p class="text-sm text-gray-500">Dashboard</p>
                    </div>
                </div>
            </div>

            <!-- User Info -->
            @php
                $authUser = Auth::user();
                $authEmail = $authUser->email ?? 'no-email';

                // Buscar miembro activo por email del usuario autenticado
                $currentMember = App\Models\UserPanelMiembro::where('is_active', true)
                    ->whereHas('user', function($query) use ($authEmail) {
                        $query->where('email', $authEmail);
                    })->first();

                // Obtener el usuario correcto directamente por su UUID desde el miembro
                $correctUser = null;
                if ($currentMember) {
                    $correctUser = App\Models\User::find($currentMember->user_id);
                }
            @endphp
            <div class="p-4 border-b border-gray-200 bg-gray-50">


                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-ddu-lavanda rounded-full flex items-center justify-center">
                        <span class="text-white font-semibold text-sm">
                            @if($currentMember && $correctUser)
                                {{ substr($correctUser->full_name ?? $correctUser->username ?? 'U', 0, 1) }}
                            @else
                                {{ substr(Auth::user()->full_name ?? Auth::user()->username ?? 'U', 0, 1) }}
                            @endif
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        @if($currentMember && $correctUser)
                            <p class="font-medium text-gray-900 text-sm truncate">{{ $correctUser->full_name ?? $correctUser->username ?? 'Usuario DDU' }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ ucfirst($currentMember->role) }} - {{ $correctUser->username ?? $correctUser->email }}</p>
                        @else
                            <p class="font-medium text-gray-900 text-sm truncate">{{ Auth::user()->full_name ?? Auth::user()->username ?? 'Usuario DDU' }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->username ?? Auth::user()->email }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Navigation Menu -->
            <div class="py-4">
                <nav class="space-y-1">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}"
                       class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5v6m8-6v6m-8 4h8"></path>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Reuniones -->
                    <a href="{{ route('reuniones.index') }}"
                       class="nav-item {{ request()->routeIs('reuniones.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span>Reuniones</span>
                    </a>

                    <!-- Asistente -->
                    <a href="{{ route('asistente.index') }}"
                       class="nav-item {{ request()->routeIs('asistente.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                        <span>Asistente</span>
                    </a>

                    <!-- Administrar Miembros (Solo para administradores) -->
                    @if(auth()->user()->panelMemberships()->where('role', 'administrador')->exists())
                    <a href="{{ route('admin.members.index') }}"
                       class="nav-item {{ request()->routeIs('admin.members.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        <span>Administrar Miembros</span>
                    </a>
                    @endif
                </nav>
            </div>

            <!-- Logout -->
            <div class="absolute bottom-0 w-64 p-4 border-t border-gray-200 bg-white">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-3 px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span>Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">@yield('page-title', 'Dashboard')</h2>
                        <p class="text-sm text-gray-500 mt-1">@yield('page-description', 'Panel de control DDU')</p>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- Notifications -->
                        <button class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-3.5-3.5a8.38 8.38 0 010-11L18 1h-5a8.38 8.38 0 00-6 2.5A8.38 8.38 0 001 1h5l1.5 1.5a8.38 8.38 0 000 11L6 17h5a8.38 8.38 0 006-2.5A8.38 8.38 0 0023 17h-5"></path>
                            </svg>
                        </button>

                        <!-- User Menu -->
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center" style="background: var(--ddu-gradient);">
                                <span class="text-white font-semibold text-sm">{{ substr(Auth::user()->name ?? Auth::user()->email, 0, 1) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
