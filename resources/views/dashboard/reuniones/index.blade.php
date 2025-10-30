@extends('layouts.dashboard')

@section('page-title', 'Reuniones')
@section('page-description', 'Gestionar reuniones y participantes')

@section('content')
@php
    // Definir variables para contenedores únicos al inicio
    $containersCount = $meetings->flatMap->containers->unique('id')->count();
    $uniqueContainers = $meetings->flatMap->containers->unique('id');
    $groupsForJs = $userGroups->map(function ($group) {
        return [
            'id' => $group->id,
            'name' => $group->name,
            'description' => $group->description,
            'members_count' => $group->members_count,
        ];
    });
@endphp
<script>
    window.dduUserGroups = @json($groupsForJs);
</script>
<div class="space-y-6 fade-in">
    <!-- Header mejorado -->
    <div class="relative overflow-hidden bg-gradient-to-r from-ddu-lavanda via-purple-500 to-ddu-aqua rounded-2xl shadow-xl">
        <div class="absolute inset-0 bg-black opacity-20"></div>
        <div class="relative z-10 p-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Reuniones DDU</h1>
                    <p class="text-white/90 text-lg">Organiza y gestiona las reuniones del equipo</p>
                </div>
            </div>
        </div>
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/5 rounded-full -ml-12 -mb-12"></div>
    </div>

    <!-- Filtros y búsqueda mejorados -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-ddu-aqua/20 via-ddu-lavanda/10 to-ddu-aqua/15 px-6 py-4 border-b border-ddu-aqua/20">
            <h3 class="text-lg font-semibold text-ddu-lavanda flex items-center">
                <svg class="w-5 h-5 mr-2 text-ddu-lavanda" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                Filtros de búsqueda
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="space-y-2">
                    <label class="flex items-center text-sm font-semibold text-gray-700">
                        <svg class="w-4 h-4 mr-2 text-ddu-lavanda" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Buscar reunión
                    </label>
                    <div class="relative">
                        <input type="text"
                               class="w-full pl-11 pr-4 py-3 border-2 border-ddu-lavanda/20 rounded-xl focus:ring-3 focus:ring-ddu-lavanda/30 focus:border-ddu-lavanda transition-all duration-200 bg-ddu-lavanda/5 focus:bg-white"
                               placeholder="Título, descripción..."
                               id="searchInput">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-ddu-lavanda/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="flex items-center text-sm font-semibold text-gray-700">
                        <svg class="w-4 h-4 mr-2 text-ddu-aqua" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5v6m8-6v6m-8 4h8"></path>
                        </svg>
                        Contenedor
                    </label>
                    <select class="w-full px-4 py-3 border-2 border-ddu-aqua/20 rounded-xl focus:ring-3 focus:ring-ddu-aqua/30 focus:border-ddu-aqua transition-all duration-200 bg-ddu-aqua/5 focus:bg-white cursor-pointer"
                            id="containerFilter">
                        <option value="">Todos los contenedores</option>
                        @foreach ($uniqueContainers as $container)
                            <option value="{{ $container->id }}">{{ $container->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="flex items-center text-sm font-semibold text-gray-700">
                        <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Fecha
                    </label>
                    <input type="date"
                           class="w-full px-4 py-3 border-2 border-ddu-lavanda/20 rounded-xl focus:ring-3 focus:ring-ddu-lavanda/30 focus:border-ddu-lavanda transition-all duration-200 bg-ddu-lavanda/5 focus:bg-white cursor-pointer"
                           id="dateFilter">
                </div>
            </div>
        </div>
    </div>

    @if (empty($googleToken))
        <div class="ddu-card border border-amber-200 bg-amber-50 text-amber-800">
            <div class="p-6 flex flex-col space-y-3">
                <div class="flex items-center space-x-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-lg font-semibold">Sin conexión con Google Drive</h3>
                </div>
                <p class="text-sm leading-relaxed">
                    Para sincronizar tus reuniones necesitamos que vincules tu cuenta de Google desde Juntify. Una vez que el
                    token esté activo, se mostrarán aquí todas las grabaciones y transcripciones procesadas automáticamente.
                </p>
            </div>
        </div>
    @else
        @php
            $rootFolder = optional($googleToken->folders)->firstWhere('parent_id', null);
            $subfolders = optional($rootFolder)->subfolders ?? collect();
        @endphp
        <div class="ddu-card">
            <div class="p-6">
                <div class="flex flex-col space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="ddu-card-title">Carpeta de grabaciones en Drive</h3>
                            <p class="ddu-card-subtitle">Sincronizada automáticamente desde tu cuenta personal</p>
                        </div>
                        <span class="text-sm text-gray-500">ID: {{ $googleToken->recordings_folder_id ?? 'No configurado' }}</span>
                    </div>
                    @if ($subfolders->isNotEmpty())
                        <div class="flex flex-wrap gap-2">
                            @foreach ($subfolders as $subfolder)
                                <span class="px-3 py-1 rounded-full bg-ddu-lavanda/10 text-ddu-lavanda text-sm font-medium">
                                    {{ $subfolder->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No se detectaron subcarpetas configuradas todavía.</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Estadísticas mejoradas -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Total Reuniones -->
        <div class="group relative overflow-hidden bg-white rounded-2xl shadow-lg border border-ddu-lavanda/20 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:border-ddu-lavanda/40">
            <div class="absolute inset-0 bg-gradient-to-br from-ddu-lavanda/5 via-white to-ddu-lavanda/10"></div>
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-ddu-lavanda/20 to-transparent rounded-full -mr-16 -mt-16"></div>
            <div class="relative z-10 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-4xl font-bold text-gray-800 mb-2">{{ $stats['total'] }}</div>
                        <div class="text-lg font-semibold text-gray-600">Total Reuniones</div>
                        <div class="text-sm text-gray-500 mt-1">Sincronizadas</div>
                    </div>
                    <div class="w-16 h-16 bg-gradient-to-br from-ddu-lavanda to-purple-500 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Contenedores -->
        <div class="group relative overflow-hidden bg-white rounded-2xl shadow-lg border border-ddu-aqua/20 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 hover:border-ddu-aqua/40">
            <div class="absolute inset-0 bg-gradient-to-br from-ddu-aqua/5 via-white to-ddu-aqua/10"></div>
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-ddu-aqua/20 to-transparent rounded-full -mr-16 -mt-16"></div>
            <div class="relative z-10 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-4xl font-bold text-gray-800 mb-2">{{ $containersCount }}</div>
                        <div class="text-lg font-semibold text-gray-600">Contenedores</div>
                        <div class="text-sm text-gray-500 mt-1">Disponibles</div>
                    </div>
                    <div class="w-16 h-16 bg-gradient-to-br from-ddu-aqua to-blue-500 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5v6m8-6v6m-8 4h8"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de reuniones agrupadas por contenedor -->
    @php
        // Agrupar reuniones por contenedores
        $meetingsByContainer = collect();
        $meetingsWithoutContainer = collect();

        foreach ($meetings as $meeting) {
            if ($meeting->containers->isNotEmpty()) {
                foreach ($meeting->containers as $container) {
                    if (!$meetingsByContainer->has($container->id)) {
                        $meetingsByContainer->put($container->id, [
                            'container' => $container,
                            'meetings' => collect()
                        ]);
                    }
                    $meetingsByContainer->get($container->id)['meetings']->push($meeting);
                }
            } else {
                $meetingsWithoutContainer->push($meeting);
            }
        }
    @endphp

    @if($meetingsByContainer->isNotEmpty())
        @foreach($meetingsByContainer as $containerId => $group)
            <div class="container-group bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden" data-container-id="{{ $containerId }}">
                <!-- Header del contenedor mejorado -->
                <div class="relative bg-gradient-to-r from-ddu-lavanda via-purple-500 to-ddu-aqua p-6">
                    <div class="absolute inset-0 bg-black/10"></div>
                    <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -mr-12 -mt-12"></div>
                    <div class="relative z-10 flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5v6m8-6v6m-8 4h8"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white">{{ $group['container']->name }}</h3>
                                <p class="text-white/90">{{ $group['meetings']->count() }} reuniones disponibles</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-4 py-2 bg-white/20 backdrop-blur-sm rounded-xl text-white text-sm font-medium">
                                📁 Contenedor
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6 meetings-grid">
                    @foreach($group['meetings'] as $meeting)
                        @php
                            $juFilePath = data_get($meeting->metadata, 'ju_local_path')
                                ?? data_get($meeting->metadata, 'ju_file_path')
                                ?? data_get($meeting->metadata, 'ju_path');
                            $hasTranscript = (bool) ($juFilePath ?: $meeting->transcript_drive_id);
                        @endphp
                        <div class="group relative bg-white rounded-2xl shadow-lg border border-gray-100 hover:shadow-2xl transition-all duration-300 cursor-pointer meeting-card transform hover:-translate-y-2 overflow-hidden"
                             @if($hasTranscript) data-transcription-id="{{ $meeting->id }}" @endif
                             data-meeting-container="{{ $containerId }}"
                             data-meeting-title="{{ strtolower($meeting->meeting_name) }}"
                             data-meeting-description="{{ strtolower($meeting->meeting_description ?? '') }}"
                             data-meeting-groups="{{ $meeting->groups->pluck('id')->implode(',') }}">

                            <!-- Header de la tarjeta -->
                            <div class="relative bg-gradient-to-br from-ddu-lavanda/5 via-white to-ddu-aqua/5 p-4 border-b border-ddu-lavanda/20">
                                <div class="absolute top-0 right-0 w-16 h-16 bg-gradient-to-br from-ddu-lavanda/15 to-transparent rounded-full -mr-8 -mt-8"></div>
                                <div class="relative flex items-center space-x-3">
                                    <div class="w-14 h-14 bg-gradient-to-br from-ddu-lavanda to-purple-500 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-lg font-bold text-gray-900 truncate mb-1">{{ $meeting->meeting_name }}</h4>
                                        <div class="flex items-center text-sm text-gray-500">
                                            <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Sincronizada
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contenido de la tarjeta -->
                            <div class="p-6">
                                @if ($meeting->meeting_description)
                                    <div class="mb-4 p-3 bg-ddu-aqua/10 rounded-xl border-l-4 border-ddu-aqua">
                                        <p class="text-gray-700 text-sm leading-relaxed">{{ Str::limit($meeting->meeting_description, 120) }}</p>
                                    </div>
                                @endif

                                <div class="mb-4" data-meeting-groups-target @if ($meeting->groups->isEmpty()) style="display:none;" @endif>
                                    @if ($meeting->groups->isNotEmpty())
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Compartido con</p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($meeting->groups as $meetingGroup)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-ddu-lavanda/10 text-ddu-lavanda text-xs font-semibold">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857M9 7a3 3 0 106 0 3 3 0 00-6 0z"></path>
                                                    </svg>
                                                    {{ $meetingGroup->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <!-- Información de fechas y duración mejorada -->
                                <div class="space-y-3">
                                    @if ($meeting->started_at)
                                        <div class="flex items-center p-2 bg-ddu-lavanda/5 rounded-lg">
                                            <div class="w-8 h-8 bg-ddu-lavanda/20 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-ddu-lavanda" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-gray-800">{{ $meeting->started_at->format('d/m/Y') }}</div>
                                                <div class="text-xs text-gray-500">{{ $meeting->started_at->format('H:i') }}</div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($meeting->duration_minutes)
                                        <div class="flex items-center p-2 bg-green-50 rounded-lg">
                                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-gray-800">{{ $meeting->duration_minutes }} minutos</div>
                                                <div class="text-xs text-gray-500">Duración total</div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($meeting->ended_at)
                                        <div class="flex items-center p-2 bg-ddu-lavanda/10 rounded-lg">
                                            <div class="w-8 h-8 bg-ddu-lavanda/20 rounded-lg flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-ddu-lavanda" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-gray-800">{{ $meeting->ended_at->format('H:i') }}</div>
                                                <div class="text-xs text-gray-500">Finalizada</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Pie de tarjeta con diseño mejorado -->
                            <div class="bg-gradient-to-br from-ddu-lavanda/5 via-white to-ddu-aqua/5 px-6 py-4 rounded-b-2xl border-t border-ddu-lavanda/20">
                                <div class="flex items-center justify-between">
                                    <!-- Estado e información -->
                                    <div class="flex items-center space-x-3">
                                        <div class="flex items-center bg-gradient-to-r from-green-100 to-green-50 px-3 py-1.5 rounded-xl border border-green-200">
                                            <div class="w-2 h-2 bg-gradient-to-r from-green-400 to-green-500 rounded-full mr-2 animate-pulse"></div>
                                            <span class="text-xs font-semibold text-green-700">Procesada</span>
                                        </div>

                                        @if (! $hasTranscript)
                                            <span class="text-xs text-gray-400 italic bg-ddu-lavanda/10 px-2 py-1 rounded-lg">Sin transcripción</span>
                                        @else
                                            <div class="flex items-center text-ddu-lavanda">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                <span class="text-xs font-medium">Ver detalles</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex items-center space-x-3">
                                        <button type="button"
                                                class="inline-flex items-center px-3 py-1.5 rounded-xl bg-gradient-to-r from-ddu-aqua to-ddu-lavanda text-white text-xs font-semibold shadow-sm hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ddu-lavanda"
                                                data-open-group-modal
                                                data-meeting-id="{{ $meeting->id }}"
                                                data-meeting-name="{{ e($meeting->meeting_name) }}">
                                            <svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Añadir a grupo
                                        </button>

                                        <!-- Botones de descarga ocultos pero funcionales -->
                                        <div class="hidden">
                                            @if ($meeting->audio_drive_id)
                                                <a href="{{ route('download.audio', $meeting) }}"
                                                   onclick="event.stopPropagation();"
                                                   title="Descargar audio">Descargar audio</a>
                                            @endif

                                            @if ($meeting->transcript_drive_id)
                                                <a href="{{ route('download.ju', $meeting) }}"
                                                   onclick="event.stopPropagation();"
                                                   title="Descargar archivo .ju">Descargar .ju</a>
                                            @endif
                                        </div>

                                        <!-- ID de reunión con diseño mejorado -->
                                        @if ($meeting->meeting_id)
                                            <div class="bg-gradient-to-r from-ddu-aqua/10 to-ddu-lavanda/10 backdrop-blur-sm border border-gray-200 px-3 py-1.5 rounded-xl">
                                                <span class="text-xs font-mono text-gray-600 font-medium">#{{ substr($meeting->meeting_id, 0, 8) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif

    @if($meetingsWithoutContainer->isNotEmpty())
        <div class="ddu-card container-group" data-container-id="">
            <div class="ddu-card-header">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="ddu-card-title">Reuniones sin contenedor</h3>
                        <p class="ddu-card-subtitle">{{ $meetingsWithoutContainer->count() }} reuniones sin contenedor asignado</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="px-3 py-1 rounded-full bg-gradient-to-r from-orange-100 to-orange-50 text-orange-700 text-sm font-medium border border-orange-200">
                            Sin contenedor
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6 meetings-grid">
                @foreach($meetingsWithoutContainer as $meeting)
                    @php
                        $juFilePath = data_get($meeting->metadata, 'ju_local_path')
                            ?? data_get($meeting->metadata, 'ju_file_path')
                            ?? data_get($meeting->metadata, 'ju_path');
                        $hasTranscript = (bool) ($juFilePath ?: $meeting->transcript_drive_id);
                    @endphp
                    <!-- Tarjeta de reunión sin contenedor con diseño moderno -->
                    <div class="group bg-white rounded-2xl shadow-sm hover:shadow-2xl border border-gray-200 hover:border-ddu-lavanda/30 transition-all duration-300 cursor-pointer meeting-card transform hover:-translate-y-1"
                         @if($hasTranscript) data-transcription-id="{{ $meeting->id }}" @endif
                         data-meeting-container=""
                         data-meeting-title="{{ strtolower($meeting->meeting_name) }}"
                         data-meeting-description="{{ strtolower($meeting->meeting_description ?? '') }}"
                         data-meeting-groups="{{ $meeting->groups->pluck('id')->implode(',') }}">

                        <!-- Encabezado de tarjeta mejorado -->
                        <div class="bg-gradient-to-r from-ddu-lavanda/10 via-ddu-aqua/5 to-ddu-lavanda/15 p-5 rounded-t-2xl border-b border-ddu-lavanda/20">
                            <div class="flex items-center space-x-4">
                                <div class="relative">
                                    <div class="w-14 h-14 bg-gradient-to-br from-ddu-lavanda via-ddu-aqua to-purple-500 rounded-xl flex items-center justify-center shadow-lg transform group-hover:scale-105 transition-transform duration-200">
                                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-orange-400 rounded-full border-2 border-white flex items-center justify-center">
                                        <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-lg font-bold text-gray-900 truncate mb-1 group-hover:text-ddu-lavanda transition-colors duration-200">{{ $meeting->meeting_name }}</h4>
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 border border-orange-200">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                            Sin contenedor
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contenido de la tarjeta -->
                        <div class="p-6">
                            @if ($meeting->meeting_description)
                                <div class="mb-4 p-3 bg-ddu-lavanda/10 rounded-xl border-l-4 border-ddu-lavanda">
                                    <p class="text-gray-700 text-sm leading-relaxed">{{ Str::limit($meeting->meeting_description, 120) }}</p>
                                </div>
                            @endif

                            <div class="mb-4" data-meeting-groups-target @if ($meeting->groups->isEmpty()) style="display:none;" @endif>
                                @if ($meeting->groups->isNotEmpty())
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Compartido con</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($meeting->groups as $meetingGroup)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-ddu-lavanda/10 text-ddu-lavanda text-xs font-semibold">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857M9 7a3 3 0 106 0 3 3 0 00-6 0z"></path>
                                                </svg>
                                                {{ $meetingGroup->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Información de fechas y duración mejorada -->
                            <div class="space-y-3">
                                @if ($meeting->started_at)
                                    <div class="flex items-center p-2 bg-ddu-lavanda/10 rounded-lg">
                                        <div class="w-8 h-8 bg-ddu-lavanda/20 rounded-lg flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-ddu-lavanda" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-800">{{ $meeting->started_at->format('d/m/Y') }}</div>
                                            <div class="text-xs text-gray-500">{{ $meeting->started_at->format('H:i') }}</div>
                                        </div>
                                    </div>
                                @endif

                                @if ($meeting->duration_minutes)
                                    <div class="flex items-center p-2 bg-green-50 rounded-lg">
                                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-800">{{ $meeting->duration_minutes }} minutos</div>
                                            <div class="text-xs text-gray-500">Duración total</div>
                                        </div>
                                    </div>
                                @endif

                                @if ($meeting->ended_at)
                                    <div class="flex items-center p-2 bg-ddu-aqua/10 rounded-lg">
                                        <div class="w-8 h-8 bg-ddu-aqua/20 rounded-lg flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-ddu-aqua" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-800">{{ $meeting->ended_at->format('H:i') }}</div>
                                            <div class="text-xs text-gray-500">Finalizada</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Pie de tarjeta con diseño mejorado -->
                        <div class="bg-gradient-to-br from-ddu-lavanda/5 via-white to-ddu-aqua/5 px-6 py-4 rounded-b-2xl border-t border-ddu-lavanda/20">
                            <div class="flex items-center justify-between">
                                <!-- Estado e información -->
                                <div class="flex items-center space-x-3">
                                    <div class="flex items-center bg-gradient-to-r from-orange-100 to-orange-50 px-3 py-1.5 rounded-xl border border-orange-200">
                                        <div class="w-2 h-2 bg-gradient-to-r from-orange-400 to-orange-500 rounded-full mr-2"></div>
                                        <span class="text-xs font-semibold text-orange-700">Sin asignar</span>
                                    </div>

                                    @if (! $hasTranscript)
                                        <span class="text-xs text-gray-400 italic bg-ddu-lavanda/10 px-2 py-1 rounded-lg">Sin transcripción</span>
                                    @else
                                        <div class="flex items-center text-ddu-lavanda">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            <span class="text-xs font-medium">Ver detalles</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center space-x-3">
                                    <button type="button"
                                            class="inline-flex items-center px-3 py-1.5 rounded-xl bg-gradient-to-r from-ddu-aqua to-ddu-lavanda text-white text-xs font-semibold shadow-sm hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ddu-lavanda"
                                            data-open-group-modal
                                            data-meeting-id="{{ $meeting->id }}"
                                            data-meeting-name="{{ e($meeting->meeting_name) }}">
                                        <svg class="w-3.5 h-3.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Añadir a grupo
                                    </button>

                                    <!-- Botones de descarga ocultos pero funcionales -->
                                    <div class="hidden">
                                        @if ($meeting->audio_drive_id)
                                            <a href="{{ route('download.audio', $meeting) }}"
                                               onclick="event.stopPropagation();"
                                               title="Descargar audio">Descargar audio</a>
                                        @endif

                                        @if ($meeting->transcript_drive_id)
                                            <a href="{{ route('download.ju', $meeting) }}"
                                               onclick="event.stopPropagation();"
                                               title="Descargar archivo .ju">Descargar .ju</a>
                                        @endif
                                    </div>

                                    <!-- ID de reunión con diseño mejorado -->
                                    @if ($meeting->meeting_id)
                                        <div class="bg-gradient-to-r from-ddu-lavanda/10 to-ddu-aqua/10 backdrop-blur-sm border border-ddu-lavanda/30 px-3 py-1.5 rounded-xl">
                                            <span class="text-xs font-mono text-ddu-lavanda font-medium">#{{ substr($meeting->meeting_id, 0, 8) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($meetings->isEmpty())
        <div class="max-w-md mx-auto">
            <div class="bg-white rounded-3xl shadow-lg border border-gray-200 overflow-hidden">
                <!-- Encabezado con gradiente -->
                <div class="bg-gradient-to-r from-ddu-lavanda/20 via-purple-100 to-ddu-aqua/20 p-8 text-center">
                    <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-ddu-lavanda to-ddu-aqua rounded-full flex items-center justify-center shadow-lg">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>

                <!-- Contenido -->
                <div class="p-8 text-center">
                    <h3 class="text-xl font-bold text-gray-900 mb-3">No hay reuniones disponibles</h3>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        Las reuniones aparecerán automáticamente cuando el proceso de Juntify termine la sincronización.
                    </p>

                    <!-- Indicador de estado -->
                    <div class="inline-flex items-center px-4 py-2 bg-blue-50 rounded-full border border-blue-200">
                        <div class="w-2 h-2 bg-blue-400 rounded-full mr-2 animate-pulse"></div>
                        <span class="text-sm font-medium text-blue-700">Sistema en sincronización</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <!-- Modal para compartir en grupos -->
    <div id="group-selection-modal" class="ju-modal modal-oculto">
        <div class="ju-modal-backdrop"></div>
        <div class="modal-contenido max-w-xl">
            <button id="group-selection-close" type="button" class="modal-cerrar" aria-label="Cerrar selección de grupos">
                &times;
            </button>

            <div class="space-y-5">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">Compartir reunión</h2>
                    <p id="group-selection-title" class="mt-1 text-sm text-gray-500">Selecciona un grupo para compartir esta reunión.</p>
                </div>

                <div id="group-selection-feedback" class="hidden px-3 py-2 rounded-lg text-sm font-medium"></div>

                <div id="group-selection-list" class="space-y-3"></div>

                <div class="text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3">
                    Solo los miembros del grupo seleccionado podrán ver la reunión compartida.
                </div>
            </div>
        </div>
    </div>
    <!-- Modal de detalles de la reunión -->
    <div id="meeting-modal" class="ju-modal modal-oculto">
        <div class="ju-modal-backdrop"></div>
        <div class="modal-contenido">
            <button id="cerrarModal" type="button" class="modal-cerrar" aria-label="Cerrar modal">
                &times;
            </button>

            <div id="modal-loader" class="modal-loader" style="display: none;">
                <div class="flex items-center justify-center space-x-3">
                    <div class="animate-spin rounded-full h-8 w-8 border-4 border-ddu-lavanda border-t-transparent"></div>
                    <span class="text-sm font-medium text-gray-600">Cargando detalles de la reunión...</span>
                </div>
            </div>

            <div id="modal-data-content" class="space-y-6">
                <div>
                    <h2 class="text-2xl font-semibold text-gray-900">Resumen de la Reunión</h2>
                    <p id="modal-summary" class="mt-2 text-gray-600"></p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Audio de la Reunión</h3>
                    <audio id="modal-audio-player" controls class="w-full mt-3"></audio>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Puntos Clave</h3>
                    <ul id="modal-key-points" class="mt-3 space-y-2 list-disc list-inside text-gray-700"></ul>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Transcripción</h3>
                    <div id="modal-segments" class="mt-3 space-y-3 text-gray-700"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear reunión -->
<div id="createMeetingModal" class="modal" style="display: none;">
    <div class="modal-content max-w-2xl">
        <div class="modal-header">
            <h3 class="modal-title">Nueva Reunión</h3>
            <button class="modal-close" onclick="hideCreateMeetingModal()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="createMeetingForm" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Título de la reunión</label>
                    <input type="text" name="title" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ddu-lavanda focus:border-ddu-lavanda"
                           placeholder="Ej: Reunión semanal de equipo">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha</label>
                    <input type="date" name="date" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ddu-lavanda focus:border-ddu-lavanda">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hora</label>
                    <input type="time" name="time" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ddu-lavanda focus:border-ddu-lavanda">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Duración (minutos)</label>
                    <input type="number" name="duration" min="15" max="480" value="60"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ddu-lavanda focus:border-ddu-lavanda">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ubicación</label>
                    <input type="text" name="location"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ddu-lavanda focus:border-ddu-lavanda"
                           placeholder="Ej: Sala de conferencias, Zoom, Teams...">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                    <textarea name="description" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ddu-lavanda focus:border-ddu-lavanda"
                              placeholder="Descripción de la reunión, agenda, objetivos..."></textarea>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" class="btn btn-outline" onclick="hideCreateMeetingModal()">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    Crear Reunión
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.ju-modal {
    position: fixed;
    inset: 0;
    z-index: 50;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
}

.ju-modal.modal-oculto {
    display: none;
}

.ju-modal .ju-modal-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(17, 24, 39, 0.45);
    backdrop-filter: blur(4px);
}

.ju-modal .modal-contenido {
    position: relative;
    background: #ffffff;
    border-radius: 1rem;
    box-shadow: 0 25px 50px -12px rgba(30, 64, 175, 0.35);
    padding: 2rem;
    width: 100%;
    max-width: 42rem;
    max-height: 90vh;
    overflow-y: auto;
    animation: slideIn 0.3s ease-out;
}

.modal-oculto .modal-contenido {
    animation: slideOut 0.2s ease-in;
}

body.modal-open {
    overflow: hidden;
}

.modal-cerrar {
    position: absolute;
    top: 1rem;
    right: 1.25rem;
    font-size: 2rem;
    color: #4b5563;
    line-height: 1;
    transition: transform 0.2s ease, color 0.2s ease;
}

.modal-cerrar:hover {
    transform: scale(1.1);
    color: #111827;
}

body.modal-open {
    overflow: hidden;
}

.animation-delay-200 {
    animation-delay: 0.2s;
}
.animation-delay-400 {
    animation-delay: 0.4s;
}

.modal {
    backdrop-filter: blur(4px);
    animation: fadeIn 0.3s ease-out;
}

.modal-content {
    animation: slideIn 0.3s ease-out;
    transform: translateY(0);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes slideOut {
    from {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
    to {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
}

.modal-close:hover {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}

#modal-audio-player {
    display: none;
}

.modal-loader {
    margin: 1.5rem 0;
}

/* Estilos para tarjetas clickeables */
.meeting-card {
    transition: all 0.3s ease;
}

.meeting-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.meeting-card[data-transcription-id]:hover {
    border-color: #6F78E4;
}

.meeting-card[data-transcription-id] {
    position: relative;
}

/* Estilos para grupos de contenedores */
.container-group {
    margin-bottom: 2rem;
}

.container-group .ddu-card-header {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid #e2e8f0;
}

.container-group[data-container-id=""] .ddu-card-header {
    background: linear-gradient(135deg, #fafafa 0%, #f4f4f5 100%);
}

.meetings-grid {
    transition: all 0.3s ease;
}

/* Animaciones para filtrado */
.meeting-card {
    transition: all 0.3s ease;
}

.meeting-card[style*="display: none"] {
    opacity: 0;
    transform: scale(0.95);
}

/* Ocultar botones de descarga, texto de detalles y badge de estado */
.meeting-card .btn-outline {
    display: none !important;
}

.meeting-card .text-ddu-lavanda {
    display: none !important;
}

.meeting-card .text-gray-400.italic {
    display: none !important;
}

.meeting-card span[class*="rounded-full"] {
    display: none !important;
}

</style>

<script>
function showCreateMeetingModal() {
    const modal = document.getElementById('createMeetingModal');
    if (!modal) {
        return;
    }

    modal.style.display = 'flex';

    const dateInput = document.querySelector('input[name="date"]');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);
    }
}

function hideCreateMeetingModal() {
    const modal = document.getElementById('createMeetingModal');
    if (!modal) {
        return;
    }

    modal.style.display = 'none';

    const form = document.getElementById('createMeetingForm');
    if (form) {
        form.reset();
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const createMeetingForm = document.getElementById('createMeetingForm');
    if (createMeetingForm) {
        createMeetingForm.addEventListener('submit', function (event) {
            event.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            console.log('Datos de la reunión:', data);

            hideCreateMeetingModal();
            alert('Reunión creada exitosamente');
        });
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const groupModal = document.getElementById('group-selection-modal');
    const groupListEl = document.getElementById('group-selection-list');
    const groupModalTitle = document.getElementById('group-selection-title');
    const groupModalFeedback = document.getElementById('group-selection-feedback');
    const closeGroupModalBtn = document.getElementById('group-selection-close');
    const userGroups = Array.isArray(window.dduUserGroups) ? window.dduUserGroups : [];
    let currentGroupMeetingId = null;
    let currentGroupMeetingCard = null;

    function getMeetingGroupIds(meetingCard) {
        if (!meetingCard) {
            return [];
        }

        const raw = meetingCard.dataset.meetingGroups || '';
        if (!raw.trim()) {
            return [];
        }

        return raw.split(',').map(id => id.trim()).filter(Boolean);
    }

    function updateMeetingCardGroups(meetingCard, groups) {
        if (!meetingCard) {
            return;
        }

        const groupIds = Array.isArray(groups) ? groups.map(group => group.id).filter(Boolean) : [];
        meetingCard.dataset.meetingGroups = groupIds.join(',');

        const container = meetingCard.querySelector('[data-meeting-groups-target]');
        if (!container) {
            return;
        }

        if (!groupIds.length) {
            container.style.display = 'none';
            container.innerHTML = '';
            return;
        }

        container.style.display = '';
        container.innerHTML = '';

        const title = document.createElement('p');
        title.className = 'text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2';
        title.textContent = 'Compartido con';
        container.appendChild(title);

        const badges = document.createElement('div');
        badges.className = 'flex flex-wrap gap-2';

        groups.forEach(group => {
            const badge = document.createElement('span');
            badge.className = 'inline-flex items-center px-3 py-1 rounded-full bg-ddu-lavanda/10 text-ddu-lavanda text-xs font-semibold';

            const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            icon.setAttribute('class', 'w-3 h-3 mr-1');
            icon.setAttribute('fill', 'none');
            icon.setAttribute('stroke', 'currentColor');
            icon.setAttribute('viewBox', '0 0 24 24');
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('stroke-linecap', 'round');
            path.setAttribute('stroke-linejoin', 'round');
            path.setAttribute('stroke-width', '2');
            path.setAttribute('d', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857M9 7a3 3 0 106 0 3 3 0 00-6 0z');
            icon.appendChild(path);
            badge.appendChild(icon);

            const label = document.createElement('span');
            label.textContent = group.name;
            badge.appendChild(label);

            badges.appendChild(badge);
        });

        container.appendChild(badges);
    }

    function renderGroupList() {
        if (!groupListEl) {
            return;
        }

        const assignedIds = getMeetingGroupIds(currentGroupMeetingCard);
        groupListEl.innerHTML = '';

        if (!userGroups.length) {
            const emptyMessage = document.createElement('p');
            emptyMessage.className = 'text-sm text-gray-500 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3';
            emptyMessage.textContent = 'Aún no tienes grupos disponibles. Crea uno desde la pestaña Mis grupos.';
            groupListEl.appendChild(emptyMessage);
            return;
        }

        userGroups.forEach(group => {
            const wrapper = document.createElement('div');
            wrapper.className = 'flex items-center justify-between bg-gray-50 border border-gray-200 rounded-xl px-4 py-3';

            const info = document.createElement('div');
            info.className = 'flex-1 min-w-0 pr-3';

            const title = document.createElement('p');
            title.className = 'font-semibold text-gray-800 truncate';
            title.textContent = group.name;
            info.appendChild(title);

            if (group.description) {
                const description = document.createElement('p');
                description.className = 'text-xs text-gray-500 mt-1 truncate';
                description.textContent = group.description;
                info.appendChild(description);
            }

            const meta = document.createElement('p');
            meta.className = 'text-xs text-gray-400 mt-1';
            meta.textContent = `${group.members_count} ${group.members_count === 1 ? 'miembro' : 'miembros'}`;
            info.appendChild(meta);

            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.shareGroupId = group.id;
            button.className = 'inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold shadow-sm transition';

            const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            icon.setAttribute('class', 'w-3.5 h-3.5 mr-2');
            icon.setAttribute('fill', 'none');
            icon.setAttribute('stroke', 'currentColor');
            icon.setAttribute('viewBox', '0 0 24 24');
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('stroke-linecap', 'round');
            path.setAttribute('stroke-linejoin', 'round');
            path.setAttribute('stroke-width', '2');
            icon.appendChild(path);

            const label = document.createElement('span');

            if (assignedIds.includes(String(group.id))) {
                button.disabled = true;
                button.classList.add('bg-gray-200', 'text-gray-500', 'cursor-not-allowed');
                path.setAttribute('d', 'M5 13l4 4L19 7');
                label.textContent = 'Compartido';
            } else {
                button.classList.add('bg-gradient-to-r', 'from-ddu-aqua', 'to-ddu-lavanda', 'text-white', 'hover:shadow-lg', 'focus:outline-none', 'focus:ring-2', 'focus:ring-offset-2', 'focus:ring-ddu-lavanda');
                path.setAttribute('d', 'M12 4v16m8-8H4');
                label.textContent = 'Compartir';
            }

            button.appendChild(icon);
            button.appendChild(label);

            wrapper.appendChild(info);
            wrapper.appendChild(button);

            groupListEl.appendChild(wrapper);
        });
    }

    function openGroupSelectionModal(meetingId, meetingName, meetingCard) {
        if (!groupModal) {
            return;
        }

        currentGroupMeetingId = meetingId;
        currentGroupMeetingCard = meetingCard;

        if (groupModalTitle) {
            const titleText = meetingName ? `Selecciona un grupo para compartir "${meetingName}"` : 'Selecciona un grupo para compartir esta reunión.';
            groupModalTitle.textContent = titleText;
        }

        if (groupModalFeedback) {
            groupModalFeedback.classList.add('hidden');
            groupModalFeedback.textContent = '';
        }

        renderGroupList();

        groupModal.classList.remove('modal-oculto');
        document.body.classList.add('modal-open');
    }

    function closeGroupSelectionModal() {
        if (!groupModal) {
            return;
        }

        groupModal.classList.add('modal-oculto');
        document.body.classList.remove('modal-open');
        currentGroupMeetingId = null;
        currentGroupMeetingCard = null;
    }

    async function shareMeetingWithGroup(groupId) {
        if (!currentGroupMeetingId || !csrfToken) {
            return;
        }

        if (groupModalFeedback) {
            groupModalFeedback.textContent = 'Compartiendo reunión...';
            groupModalFeedback.className = 'px-3 py-2 rounded-lg text-sm font-medium bg-ddu-lavanda/10 text-ddu-lavanda';
            groupModalFeedback.classList.remove('hidden');
        }

        try {
            const response = await fetch(`/reuniones/${currentGroupMeetingId}/grupos`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ group_id: groupId }),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'No se pudo compartir la reunión.');
            }

            if (groupModalFeedback) {
                groupModalFeedback.textContent = data.message || 'Reunión añadida al grupo correctamente.';
                groupModalFeedback.className = 'px-3 py-2 rounded-lg text-sm font-medium bg-green-50 text-green-700 border border-green-200';
            }

            if (Array.isArray(data.meeting_groups)) {
                updateMeetingCardGroups(currentGroupMeetingCard, data.meeting_groups);
            }

            renderGroupList();
        } catch (error) {
            if (groupModalFeedback) {
                groupModalFeedback.textContent = error.message || 'Ocurrió un problema al compartir la reunión.';
                groupModalFeedback.className = 'px-3 py-2 rounded-lg text-sm font-medium bg-red-50 text-red-700 border border-red-200';
                groupModalFeedback.classList.remove('hidden');
            }
        }
    }

    document.querySelectorAll('[data-open-group-modal]').forEach(button => {
        button.addEventListener('click', (event) => {
            event.stopPropagation();
            const meetingId = button.dataset.meetingId;
            const meetingName = button.dataset.meetingName || '';
            const meetingCard = button.closest('.meeting-card');
            openGroupSelectionModal(meetingId, meetingName, meetingCard);
        });
    });

    if (groupListEl) {
        groupListEl.addEventListener('click', (event) => {
            const targetButton = event.target.closest('[data-share-group-id]');
            if (!targetButton || targetButton.disabled) {
                return;
            }

            const groupId = targetButton.dataset.shareGroupId;
            shareMeetingWithGroup(groupId);
        });
    }

    if (closeGroupModalBtn) {
        closeGroupModalBtn.addEventListener('click', (event) => {
            event.stopPropagation();
            closeGroupSelectionModal();
        });
    }

    if (groupModal) {
        groupModal.addEventListener('click', (event) => {
            if (event.target === groupModal || event.target.classList.contains('ju-modal-backdrop')) {
                closeGroupSelectionModal();
            }
        });
    }

    // Función para filtrar reuniones
    function filterMeetings() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const selectedContainer = document.getElementById('containerFilter').value;
        const selectedDate = document.getElementById('dateFilter').value;

        const containerGroups = document.querySelectorAll('.container-group');

        containerGroups.forEach(group => {
            const containerMeetings = group.querySelectorAll('.meeting-card');
            let visibleMeetingsCount = 0;

            // Filtrar por contenedor
            const groupContainerId = group.dataset.containerId;
            if (selectedContainer && selectedContainer !== groupContainerId) {
                group.style.display = 'none';
                return;
            }

            // Filtrar reuniones individuales
            containerMeetings.forEach(meetingCard => {
                let shouldShow = true;

                // Filtro de búsqueda por texto
                if (searchTerm) {
                    const title = meetingCard.dataset.meetingTitle || '';
                    const description = meetingCard.dataset.meetingDescription || '';
                    if (!title.includes(searchTerm) && !description.includes(searchTerm)) {
                        shouldShow = false;
                    }
                }

                // Filtro por estado (se puede implementar agregando data-status a las tarjetas)
                // if (selectedStatus) {
                //     // TODO: Implementar filtro por estado
                // }

                // Filtro por fecha (se puede implementar agregando data-date a las tarjetas)
                // if (selectedDate) {
                //     // TODO: Implementar filtro por fecha
                // }

                if (shouldShow) {
                    meetingCard.style.display = 'block';
                    visibleMeetingsCount++;
                } else {
                    meetingCard.style.display = 'none';
                }
            });

            // Mostrar/ocultar grupo completo basado en reuniones visibles
            if (visibleMeetingsCount > 0) {
                group.style.display = 'block';
                // Actualizar contador en el subtítulo
                const subtitle = group.querySelector('.ddu-card-subtitle');
                if (subtitle) {
                    const originalText = subtitle.textContent;
                    if (visibleMeetingsCount === containerMeetings.length) {
                        // Mostrar texto original
                        if (groupContainerId === '') {
                            subtitle.textContent = `${containerMeetings.length} reuniones sin contenedor asignado`;
                        } else {
                            subtitle.textContent = `${containerMeetings.length} reuniones en este contenedor`;
                        }
                    } else {
                        // Mostrar reuniones filtradas
                        if (groupContainerId === '') {
                            subtitle.textContent = `${visibleMeetingsCount} de ${containerMeetings.length} reuniones sin contenedor`;
                        } else {
                            subtitle.textContent = `${visibleMeetingsCount} de ${containerMeetings.length} reuniones en este contenedor`;
                        }
                    }
                }
            } else {
                group.style.display = 'none';
            }
        });
    }

    // Event listeners para filtros
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', filterMeetings);
    }

    const containerFilter = document.getElementById('containerFilter');
    if (containerFilter) {
        containerFilter.addEventListener('change', filterMeetings);
    }

    const dateFilter = document.getElementById('dateFilter');
    if (dateFilter) {
        dateFilter.addEventListener('change', filterMeetings);
    }

    const modal = document.getElementById('meeting-modal');
    if (!modal) {
        return;
    }

    const closeModalBtn = document.getElementById('cerrarModal');
    const modalLoader = document.getElementById('modal-loader');
    const modalDataContent = document.getElementById('modal-data-content');
    const summaryEl = document.getElementById('modal-summary');
    const audioPlayerEl = document.getElementById('modal-audio-player');
    const keyPointsEl = document.getElementById('modal-key-points');
    const segmentsEl = document.getElementById('modal-segments');

    const resetModalContent = () => {
        if (summaryEl) {
            summaryEl.textContent = '';
        }

        if (audioPlayerEl) {
            try {
                audioPlayerEl.pause();
            } catch (error) {
                console.warn('No se pudo pausar el audio:', error);
            }

            audioPlayerEl.removeAttribute('src');
            audioPlayerEl.load();
            audioPlayerEl.style.display = 'none';
        }

        if (keyPointsEl) {
            keyPointsEl.innerHTML = '';
        }

        if (segmentsEl) {
            segmentsEl.innerHTML = '';
        }
    };

    const populateModal = (data) => {
        if (summaryEl) {
            summaryEl.textContent = data.summary ?? 'Resumen no disponible.';
        }

        if (audioPlayerEl) {
            if (data.audio_url) {
                audioPlayerEl.src = data.audio_url;
                audioPlayerEl.style.display = 'block';
                audioPlayerEl.load();
            } else {
                audioPlayerEl.style.display = 'none';
            }
        }

        if (keyPointsEl) {
            keyPointsEl.innerHTML = '';

            if (Array.isArray(data.key_points) && data.key_points.length > 0) {
                data.key_points.forEach((point) => {
                    const li = document.createElement('li');
                    const description = typeof point === 'object' && point !== null
                        ? (point.description || point.title || point.text || '')
                        : String(point || '');

                    li.textContent = description || 'Elemento sin descripción.';
                    keyPointsEl.appendChild(li);
                });
            } else {
                const li = document.createElement('li');
                li.textContent = 'No se encontraron puntos clave.';
                keyPointsEl.appendChild(li);
            }
        }

        if (segmentsEl) {
            segmentsEl.innerHTML = '';

            if (Array.isArray(data.segments) && data.segments.length > 0) {
                data.segments.forEach((segment) => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'space-y-1';

                    const speaker = typeof segment === 'object' && segment !== null
                        ? (segment.speaker || segment.role || 'Hablante')
                        : 'Hablante';

                    const text = typeof segment === 'object' && segment !== null
                        ? (segment.text || segment.content || segment.sentence || '')
                        : String(segment || '');

                    const speakerEl = document.createElement('strong');
                    speakerEl.textContent = `${speaker}:`;

                    const textEl = document.createElement('p');
                    textEl.className = 'text-gray-700 whitespace-pre-line';
                    textEl.textContent = text || 'Sin contenido para este segmento.';

                    wrapper.appendChild(speakerEl);
                    wrapper.appendChild(textEl);

                    segmentsEl.appendChild(wrapper);
                });
            } else {
                const emptyMessage = document.createElement('p');
                emptyMessage.className = 'text-sm text-gray-500';
                emptyMessage.textContent = 'No hay transcripción disponible.';
                segmentsEl.appendChild(emptyMessage);
            }
        }
    };

    const openMeetingModal = (transcriptionId) => {
        modal.classList.remove('modal-oculto');
        document.body.classList.add('modal-open');

        if (modalLoader) {
            modalLoader.style.display = 'block';
        }

        if (modalDataContent) {
            modalDataContent.style.display = 'none';
        }

        resetModalContent();

        fetch(`/meeting-details/${transcriptionId}`)
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Error al cargar los datos del servidor.');
                }

                return response.json();
            })
            .then((data) => {
                // Debug: mostrar información de estructura en consola
                if (data.debug_info) {
                    console.log('=== DEBUG INFO DEL ARCHIVO .JU ===');
                    console.log('Claves disponibles:', data.debug_info.available_keys);
                    console.log('Raw key_points:', data.debug_info.raw_key_points);
                    console.log('Alternativas posibles:', data.debug_info.possible_key_alternatives);
                    console.log('===================================');
                }

                if (data.error) {
                    throw new Error(data.error || 'No se pudo obtener la información de la reunión.');
                }

                populateModal(data);
            })
            .catch((error) => {
                console.error('Error al obtener detalles de la reunión:', error);

                if (summaryEl) {
                    summaryEl.textContent = `Error: ${error.message}`;
                }

                if (keyPointsEl) {
                    keyPointsEl.innerHTML = '<li>No se pudieron cargar los puntos clave.</li>';
                }

                if (segmentsEl) {
                    segmentsEl.innerHTML = `<p class="text-sm text-red-500">No se pudo cargar la transcripción.</p>`;
                }

                if (audioPlayerEl) {
                    audioPlayerEl.style.display = 'none';
                }
            })
            .finally(() => {
                if (modalLoader) {
                    modalLoader.style.display = 'none';
                }

                if (modalDataContent) {
                    modalDataContent.style.display = 'block';
                }
            });
    };

    const closeModal = () => {
        if (modal.classList.contains('modal-oculto')) {
            return;
        }

        modal.classList.add('modal-oculto');
        document.body.classList.remove('modal-open');

        if (modalLoader) {
            modalLoader.style.display = 'none';
        }

        if (modalDataContent) {
            modalDataContent.style.display = 'block';
        }

        resetModalContent();
    };

    document.querySelectorAll('.meeting-card').forEach((card) => {
        card.addEventListener('click', (event) => {
            // Verificar si el click fue en un botón de descarga
            if (event.target.closest('a') || event.target.closest('button')) {
                return; // No abrir el modal si se hizo click en un botón
            }

            const transcriptionId = card.dataset.transcriptionId;
            if (transcriptionId) {
                openMeetingModal(transcriptionId);
            }
        });
    });

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }

    modal.addEventListener('click', (event) => {
        if (event.target === modal || event.target.classList.contains('ju-modal-backdrop')) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeModal();
            closeGroupSelectionModal();
        }
    });

    resetModalContent();

    // Abrir modal automáticamente si se pasa el parámetro 'show' en la URL
    const urlParams = new URLSearchParams(window.location.search);
    const showMeetingId = urlParams.get('show');
    if (showMeetingId) {
        // Buscar la tarjeta de reunión correspondiente
        const targetCard = document.querySelector(`[data-transcription-id="${showMeetingId}"]`);
        if (targetCard) {
            // Esperar un momento para que la página se cargue completamente
            setTimeout(() => {
                openMeetingModal(showMeetingId);
                // Limpiar el parámetro de la URL sin recargar la página
                const newUrl = new URL(window.location);
                newUrl.searchParams.delete('show');
                window.history.replaceState({}, '', newUrl);
            }, 500);
        }
    }
});
</script>

<style>
/* Estilos adicionales para mejorar la experiencia visual */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-10px);
    }
    60% {
        transform: translateY(-5px);
    }
}

.meeting-card {
    animation: fadeInUp 0.6s ease-out;
    animation-fill-mode: both;
}

.meeting-card:nth-child(2) {
    animation-delay: 0.1s;
}

.meeting-card:nth-child(3) {
    animation-delay: 0.2s;
}

.meeting-card:nth-child(4) {
    animation-delay: 0.3s;
}

.container-group {
    animation: slideInRight 0.8s ease-out;
}

/* Mejora de los efectos hover */
.meeting-card:hover .w-14,
.meeting-card:hover .w-12 {
    transform: scale(1.1) rotate(5deg);
}

.statistics-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

/* Efecto de loading suave */
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .5;
    }
}

/* Mejoras en inputs */
input:focus {
    box-shadow: 0 0 0 4px rgba(var(--ddu-lavanda-rgb), 0.1);
    border-color: var(--ddu-lavanda);
}

/* Transiciones suaves globales */
* {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Mejora del scroll */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, var(--ddu-lavanda), var(--ddu-aqua));
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, rgba(var(--ddu-lavanda-rgb), 0.8), rgba(var(--ddu-aqua-rgb), 0.8));
}
</style>

@endsection
