@extends('layouts.dashboard')

@section('page-title', 'Mis grupos')
@section('page-description', 'Organiza equipos y comparte las reuniones con otros miembros de la plataforma')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
    <div class="space-y-6">
        @if (session('status'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg shadow-sm">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg shadow-sm">
                <ul class="list-disc list-inside space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="ddu-card">
            <div class="ddu-card-header">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Crear nuevo grupo</h2>
                    <p class="text-sm text-gray-500">Diseña espacios colaborativos para compartir tus reuniones con miembros específicos.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('grupos.store') }}" class="space-y-4" data-validate>
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre del grupo</label>
                        <input type="text" id="name" name="name" required maxlength="255"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-ddu-lavanda focus:border-ddu-lavanda"
                               placeholder="Equipo de proyectos" value="{{ old('name') }}">
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descripción (opcional)</label>
                        <input type="text" id="description" name="description" maxlength="1000"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-ddu-lavanda focus:border-ddu-lavanda"
                               placeholder="Define el propósito del grupo" value="{{ old('description') }}">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn-ddu inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Crear grupo
                    </button>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @forelse ($groups as $group)
                <div class="ddu-card">
                    <div class="ddu-card-header">
                        <div>
                            <h3 class="ddu-card-title">{{ $group->name }}</h3>
                            <p class="ddu-card-subtitle">
                                {{ $group->members_count }} {{ Str::plural('miembro', $group->members_count) }} · {{ $group->meetings->count() }} {{ Str::plural('reunión', $group->meetings->count()) }}
                            </p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-white border border-ddu-aqua text-ddu-navy-dark">
                            Grupo activo
                        </span>
                    </div>

                    @if ($group->description)
                        <div class="p-4 mb-4 bg-ddu-aqua/10 border border-ddu-aqua/30 rounded-xl text-sm text-ddu-navy-dark">
                            {{ $group->description }}
                        </div>
                    @endif

                    <div class="space-y-5">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">Miembros</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($group->members as $member)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-ddu-lavanda/10 text-ddu-lavanda text-xs font-semibold">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A4 4 0 017 7h10a4 4 0 011.879 7.596M15 21h-6m3-4v4"></path>
                                        </svg>
                                        {{ $member->full_name ?? $member->username ?? $member->email }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">Añadir miembro</h4>
                            <form method="POST" action="{{ route('grupos.members.store', $group) }}" class="flex flex-col sm:flex-row gap-3" data-validate>
                                @csrf
                                <input type="email" name="email" required placeholder="correo@ejemplo.com"
                                       class="flex-1 rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-ddu-lavanda focus:border-ddu-lavanda">
                                <button type="submit" class="btn-ddu inline-flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Invitar
                                </button>
                            </form>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">Reuniones compartidas</h4>
                            @if ($group->meetings->isEmpty())
                                <p class="text-sm text-gray-500">Aún no has compartido reuniones con este grupo. Desde la sección de reuniones podrás añadirlas.</p>
                            @else
                                <ul class="space-y-3 text-sm text-gray-700">
                                    @foreach ($group->meetings->take(4) as $meeting)
                                        <li class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1 min-w-0">
                                                    <span class="font-medium text-gray-900 block truncate">{{ $meeting->meeting_name }}</span>
                                                    @if (isset($meeting->shared_by_user))
                                                        <p class="text-xs text-gray-500 mt-1">
                                                            Compartida por <span class="font-medium text-ddu-lavanda">{{ $meeting->shared_by_user->full_name ?? $meeting->shared_by_user->username }}</span>
                                                            @if ($meeting->pivot->created_at)
                                                                · {{ $meeting->pivot->created_at->diffForHumans() }}
                                                            @endif
                                                        </p>
                                                    @endif
                                                </div>
                                                <button
                                                    onclick="showMeetingDetails('{{ $meeting->id }}')"
                                                    class="ml-3 text-xs px-2 py-1 rounded-full bg-ddu-lavanda/10 text-ddu-lavanda font-semibold hover:bg-ddu-lavanda/20 transition-colors cursor-pointer">
                                                    Ver detalles
                                                </button>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                                @if ($group->meetings->count() > 4)
                                    <p class="mt-2 text-xs text-gray-500">y {{ $group->meetings->count() - 4 }} reuniones más compartidas.</p>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="ddu-card text-center">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Aún no tienes grupos creados</h3>
                    <p class="text-sm text-gray-500">Crea tu primer grupo para compartir reuniones con otros usuarios de la plataforma.</p>
                </div>
            @endforelse
        </div>
    </div>

    <script>
        function showMeetingDetails(meetingId) {
            // Redirigir a la página de reuniones con el modal abierto para esa reunión específica
            window.location.href = "{{ route('reuniones.index') }}?show=" + meetingId;
        }
    </script>
@endsection
