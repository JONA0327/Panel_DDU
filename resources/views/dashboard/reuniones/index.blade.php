@extends('layouts.dashboard')

@section('page-title', 'Reuniones')
@section('page-description', 'Gestionar reuniones y participantes')

@section('content')
<div class="space-y-6 fade-in">
    <!-- Header con botón de crear -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Reuniones</h2>
            <p class="text-gray-600 mt-1">Organiza y gestiona las reuniones del equipo DDU</p>
        </div>


    </div>

    <!-- Filtros y búsqueda -->
    <div class="ddu-card">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Buscar reunión</label>
                    <input type="text"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ddu-lavanda focus:border-ddu-lavanda"
                           placeholder="Título, descripción..."
                           id="searchInput">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ddu-lavanda focus:border-ddu-lavanda"
                            id="statusFilter">
                        <option value="">Todos los estados</option>
                        <option value="programada">Programada</option>
                        <option value="en_curso">En Curso</option>
                        <option value="finalizada">Finalizada</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha</label>
                    <input type="date"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ddu-lavanda focus:border-ddu-lavanda"
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

    <!-- Estadísticas rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="stat-number text-gray-900">{{ $stats['total'] }}</div>
                    <div class="stat-label text-gray-600">Total Reuniones</div>
                </div>
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>

        <div class="stat-card primary">
            <div class="flex items-center justify-between">
                <div>
                    <div class="stat-number">{{ $stats['programadas'] }}</div>
                    <div class="stat-label">Programadas</div>
                </div>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="stat-number text-green-600">{{ $stats['finalizadas'] }}</div>
                    <div class="stat-label text-gray-600">Finalizadas</div>
                </div>
                <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="stat-number text-amber-600">{{ $stats['esta_semana'] }}</div>
                    <div class="stat-label text-gray-600">Esta Semana</div>
                </div>
                <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Lista de reuniones -->
        <div class="ddu-card">
            <div class="ddu-card-header">
                <div>
                    <h3 class="ddu-card-title">Reuniones sincronizadas</h3>
                    <p class="ddu-card-subtitle">Información consolidada desde Google Drive y contenedores locales</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                @forelse ($meetings as $meeting)
                    @php
                        $juFilePath = data_get($meeting->metadata, 'ju_local_path')
                            ?? data_get($meeting->metadata, 'ju_file_path')
                            ?? data_get($meeting->metadata, 'ju_path');
                        $hasTranscript = (bool) ($juFilePath ?: $meeting->transcript_drive_id);
                    @endphp
                    <div class="ddu-card hover:shadow-lg transition-shadow">
                        <div class="ddu-card-header">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gradient-to-r from-ddu-lavanda to-ddu-aqua rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="ddu-card-title truncate">{{ $meeting->meeting_name }}</h4>
                                    <p class="ddu-card-subtitle">Sincronizada automáticamente</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4">
                            @if ($meeting->meeting_description)
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ Str::limit($meeting->meeting_description, 100) }}</p>
                            @endif

                            <div class="space-y-2 text-sm text-gray-500">
                                @if ($meeting->started_at)
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 002 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>{{ $meeting->started_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                @endif
                                @if ($meeting->duration_minutes)
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>{{ $meeting->duration_minutes }} minutos</span>
                                    </div>
                                @endif
                                @if ($meeting->containers->isNotEmpty())
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h7" />
                                        </svg>
                                        <span class="truncate">{{ $meeting->containers->pluck('name')->implode(', ') }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $meeting->status_badge_color }}">
                                    {{ $meeting->status_label }}
                                </span>

                                <div class="flex space-x-1">
                                    @if ($meeting->audio_drive_id)
                                        <a href="{{ route('download.audio', $meeting) }}"
                                           class="btn btn-outline btn-xs"
                                           onclick="event.stopPropagation();"
                                           title="Descargar audio">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 14.142M6.343 6.343a8 8 0 000 11.314m15.314-11.314a8 8 0 000 11.314" />
                                            </svg>
                                        </a>
                                    @endif

                                    @if ($meeting->transcript_drive_id)
                                        <a href="{{ route('download.ju', $meeting) }}"
                                           class="btn btn-outline btn-xs"
                                           onclick="event.stopPropagation();"
                                           title="Descargar archivo .ju">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                            </svg>
                                        </a>
                                    @endif

                                    @if ($hasTranscript)
                                        <button type="button"
                                                class="btn btn-primary btn-xs btn-view-details"
                                                data-transcription-id="{{ $meeting->id }}"
                                                title="Ver detalles de la reunión">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                        </button>
                                    @endif
                                </div>

                                @if (! $hasTranscript)
                                    <span class="text-xs text-gray-400 italic">Transcripción no disponible</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No hay reuniones sincronizadas</h3>
                        <p class="text-gray-500 mb-6">Las reuniones aparecerán automáticamente cuando el proceso de Juntify termine la sincronización.</p>
                    </div>
                @endforelse
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

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            console.log('Buscar:', this.value);
        });
    }

    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function () {
            console.log('Filtrar por estado:', this.value);
        });
    }

    const dateFilter = document.getElementById('dateFilter');
    if (dateFilter) {
        dateFilter.addEventListener('change', function () {
            console.log('Filtrar por fecha:', this.value);
        });
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

    document.querySelectorAll('.btn-view-details').forEach((button) => {
        button.addEventListener('click', () => {
            const transcriptionId = button.dataset.transcriptionId;
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
        }
    });

    resetModalContent();
});
</script>

@endsection
