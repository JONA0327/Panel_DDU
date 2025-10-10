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
                    @endphp
                    <div class="ddu-card hover:shadow-lg transition-shadow cursor-pointer" onclick="openMeetingModal({{ $meeting->id }})">
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

                                    @if ($juFilePath)
                                        <button type="button"
                                                class="btn btn-primary btn-xs btn-ver-detalles"
                                                data-meeting-id="{{ $meeting->id }}"
                                                data-path="{{ $juFilePath }}"
                                                data-audio-url="{{ $meeting->audio_drive_id ? route('download.audio', $meeting) : '' }}"
                                                data-title="{{ $meeting->meeting_name }}"
                                                onclick="event.stopPropagation(); openMeetingModal({{ $meeting->id }})"
                                                title="Ver transcripción">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>

                                @if (! $juFilePath)
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
    <div id="reunionModal" class="ju-modal modal-oculto">
        <div class="ju-modal-backdrop"></div>
        <div class="modal-contenido">
            <button id="cerrarModal" type="button" class="modal-cerrar" aria-label="Cerrar modal">
                &times;
            </button>
            <div class="space-y-6">
                <div>
                    <h2 id="modalTitulo" class="text-2xl font-semibold text-gray-900">Reunión</h2>
                    <p id="modalResumen" class="mt-2 text-gray-600">Selecciona una reunión para ver los detalles.</p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Puntos clave</h3>
                    <ul id="modalPuntosClave" class="mt-3 space-y-2 list-disc list-inside text-gray-700"></ul>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Transcripción</h3>
                    <div id="modalSegmentos" class="mt-3 space-y-3 text-gray-700"></div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Audio</h3>
                    <p id="modalAudioStatus" class="text-sm text-gray-500">Selecciona una reunión para cargar el audio.</p>
                    <audio id="modalAudio" controls class="w-full mt-3 hidden"></audio>
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
    const detailsRoute = "{{ route('reuniones.showDetails') }}";

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

    const modal = document.getElementById('reunionModal');
    if (!modal) {
        return;
    }

    const closeModalBtn = document.getElementById('cerrarModal');
    const modalTitle = document.getElementById('modalTitulo');
    const modalResumen = document.getElementById('modalResumen');
    const modalPuntosClave = document.getElementById('modalPuntosClave');
    const modalSegmentos = document.getElementById('modalSegmentos');
    const modalAudio = document.getElementById('modalAudio');
    const modalAudioStatus = document.getElementById('modalAudioStatus');

    const cleanupAudio = () => {
        if (!modalAudio) {
            return;
        }

        try {
            modalAudio.pause();
        } catch (error) {
            console.warn('No se pudo pausar el audio:', error);
        }

        if (modalAudio.dataset.objectUrl) {
            URL.revokeObjectURL(modalAudio.dataset.objectUrl);
            delete modalAudio.dataset.objectUrl;
        }

        modalAudio.removeAttribute('src');
        modalAudio.load();
        modalAudio.classList.add('hidden');
    };

    const resetModalContent = () => {
        modalResumen.textContent = 'Selecciona una reunión para ver los detalles.';
        modalPuntosClave.innerHTML = '';
        modalSegmentos.innerHTML = '';
        modalAudioStatus.textContent = 'Selecciona una reunión para cargar el audio.';
        cleanupAudio();
    };

    const closeModal = () => {
        if (modal.classList.contains('modal-oculto')) {
            return;
        }

        modal.classList.add('modal-oculto');
        document.body.classList.remove('modal-open');
        resetModalContent();
    };

    const formatTime = (value) => {
        if (value === undefined || value === null || value === '') {
            return null;
        }

        const seconds = Number(value);
        if (!Number.isFinite(seconds)) {
            return null;
        }

        const minutesPart = Math.floor(seconds / 60);
        const secondsPart = Math.floor(seconds % 60);

        return `${String(minutesPart).padStart(2, '0')}:${String(secondsPart).padStart(2, '0')}`;
    };

    const fillKeyPoints = (points) => {
        modalPuntosClave.innerHTML = '';

        if (!Array.isArray(points) || points.length === 0) {
            modalPuntosClave.innerHTML = '<li>No se encontraron puntos clave.</li>';
            return;
        }

        points.forEach((point) => {
            const item = document.createElement('li');
            item.className = 'pl-1';

            let description = '';
            if (typeof point === 'string') {
                description = point;
            } else if (point && typeof point === 'object') {
                description = point.description || point.text || point.title || point.summary || '';

                if (!description) {
                    const firstString = Object.values(point).find((value) => typeof value === 'string');
                    description = firstString || '';
                }
            }

            item.textContent = description || 'Punto sin descripción';
            modalPuntosClave.appendChild(item);
        });
    };

    const fillSegments = (segments) => {
        modalSegmentos.innerHTML = '';

        if (!Array.isArray(segments) || segments.length === 0) {
            modalSegmentos.innerHTML = '<p class="text-sm text-gray-500">No hay transcripción disponible.</p>';
            return;
        }

        segments.forEach((segment) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'p-4 bg-gray-50 rounded-lg border border-gray-100';

            const header = document.createElement('div');
            header.className = 'flex flex-wrap items-center justify-between text-sm text-gray-500';

            const speaker = document.createElement('span');
            speaker.className = 'font-medium text-gray-700';
            const speakerName = (segment && typeof segment === 'object')
                ? (segment.speaker || segment.expositor || segment.name || 'Hablante')
                : 'Hablante';
            speaker.textContent = speakerName;

            header.appendChild(speaker);

            if (segment && typeof segment === 'object') {
                const start = formatTime(segment.start ?? segment.start_time ?? segment.inicio);
                const end = formatTime(segment.end ?? segment.end_time ?? segment.fin);

                if (start || end) {
                    const time = document.createElement('span');
                    time.textContent = start && end ? `${start} - ${end}` : (start || end || '');
                    header.appendChild(time);
                }
            }

            const paragraph = document.createElement('p');
            paragraph.className = 'mt-2 text-gray-700 whitespace-pre-line';
            const segmentText = (segment && typeof segment === 'object')
                ? (segment.text || segment.content || segment.sentence || segment.fragment || '')
                : String(segment || '');
            paragraph.textContent = segmentText || 'Sin contenido para este segmento.';

            wrapper.appendChild(header);
            wrapper.appendChild(paragraph);

            modalSegmentos.appendChild(wrapper);
        });
    };

    const loadAudio = (audioUrl) => {
        if (!modalAudio) {
            return;
        }

        cleanupAudio();

        if (!audioUrl) {
            modalAudioStatus.textContent = 'No hay audio disponible para esta reunión.';
            return;
        }

        modalAudioStatus.textContent = 'Descargando audio...';

        fetch(audioUrl)
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Respuesta inesperada al obtener el audio');
                }

                return response.blob();
            })
            .then((blob) => {
                const objectUrl = URL.createObjectURL(blob);
                modalAudio.dataset.objectUrl = objectUrl;
                modalAudio.src = objectUrl;
                modalAudio.classList.remove('hidden');
                modalAudioStatus.textContent = 'Audio listo para reproducir.';
            })
            .catch((error) => {
                cleanupAudio();
                modalAudioStatus.textContent = 'No fue posible cargar el audio.';
                console.error('Error al cargar el audio:', error);
            });
    };

    const openModal = () => {
        modal.classList.remove('modal-oculto');
        document.body.classList.add('modal-open');
    };

    const buttons = document.querySelectorAll('.btn-ver-detalles');
    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            const filePath = button.getAttribute('data-path') || '';
            const meetingTitle = button.getAttribute('data-title') || 'Reunión';
            const initialAudioUrl = button.getAttribute('data-audio-url') || '';

            modalTitle.textContent = meetingTitle;
            modalResumen.textContent = 'Cargando...';
            modalPuntosClave.innerHTML = '';
            modalSegmentos.innerHTML = '';
            modalAudioStatus.textContent = initialAudioUrl ? 'Preparando el audio...' : 'No hay audio disponible para esta reunión.';
            cleanupAudio();

            openModal();

            if (!filePath) {
                modalResumen.textContent = 'No se encontró la transcripción asociada a esta reunión.';
                return;
            }

            const requestUrl = new URL(detailsRoute, window.location.origin);
            requestUrl.searchParams.set('path', filePath);
            if (initialAudioUrl) {
                requestUrl.searchParams.set('audio_url', initialAudioUrl);
            }

            fetch(requestUrl.toString(), {
                headers: {
                    'Accept': 'application/json',
                },
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }

                    return response.json();
                })
                .then((data) => {
                    if (data.error) {
                        throw new Error(data.error);
                    }

                    modalResumen.textContent = data.summary || 'Sin resumen disponible.';
                    fillKeyPoints(Array.isArray(data.key_points) ? data.key_points : []);
                    fillSegments(Array.isArray(data.segments) ? data.segments : []);

                    const audioUrl = data.audio_url || initialAudioUrl;
                    loadAudio(audioUrl);
                })
                .catch((error) => {
                    modalResumen.textContent = `Error al cargar los datos: ${error.message}`;
                    modalPuntosClave.innerHTML = '<li>No se pudieron cargar los puntos clave.</li>';
                    modalSegmentos.innerHTML = '<p class="text-sm text-gray-500">No se pudo cargar la transcripción.</p>';
                    modalAudioStatus.textContent = 'No fue posible cargar el audio.';
                    console.error('Error:', error);
                });
        });
    });

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeMeetingModal);
    }

    modal.addEventListener('click', (event) => {
        if (event.target === modal || event.target.classList.contains('ju-modal-backdrop')) {
            closeMeetingModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMeetingModal();
        }
    });
});

// Variables globales para el modal
let currentMeetingId = null;
const meetingsShowBaseUrl = "{{ url('/reuniones') }}/";

// Función para abrir el modal de reunión
function openMeetingModal(meetingId) {
    console.log('openMeetingModal llamada con ID:', meetingId);

    // Verificar que el DOM esté listo
    if (document.readyState === 'loading') {
        console.log('DOM aún cargando, esperando...');
        document.addEventListener('DOMContentLoaded', () => openMeetingModal(meetingId));
        return;
    }

    currentMeetingId = meetingId;
    const modal = document.getElementById('reunionModal');
    const modalTitle = document.getElementById('modalTitulo');
    const modalResumen = document.getElementById('modalResumen');

    console.log('Elementos encontrados:', { modal, modalTitle, modalResumen });

    if (!modal) {
        console.error('Modal no encontrado - ID: reunionModal');
        return;
    }

    if (!modalTitle) {
        console.error('modalTitle no encontrado - ID: modalTitulo');
        return;
    }

    if (!modalResumen) {
        console.error('modalResumen no encontrado - ID: modalResumen');
        return;
    }

    // Mostrar el modal
    console.log('Mostrando modal - clases antes:', modal.className);
    modal.classList.remove('modal-oculto');
    document.body.classList.add('modal-open');
    console.log('Modal mostrado - clases después:', modal.className);

    // Mostrar estado de carga
    console.log('Estableciendo contenido de carga...');
    modalTitle.textContent = 'Cargando reunión...';
    modalResumen.innerHTML = `
        <div class="flex items-center justify-center py-8">
            <div class="relative">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-ddu-lavanda border-t-transparent"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-6 h-6 text-ddu-lavanda" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
            </div>
            <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-700">Procesando reunión</h3>
                <p class="text-gray-500">Desencriptando archivo .ju y cargando detalles...</p>
                <p class="text-sm text-gray-400">ID de reunión: ${meetingId}</p>
            </div>
        </div>
    `;

    console.log('Contenido establecido, iniciando fetch...');

    // Hacer la petición para obtener los datos básicos de la reunión
    fetch(`${meetingsShowBaseUrl}${meetingId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('No se pudieron cargar los detalles de la reunión.');
        }
        return response.json();
    })
    .then(basicData => {
        // Renderizar la información básica primero
        renderBasicMeetingInfo(basicData);

        // Buscar el botón que se clickeó para obtener la información del archivo .ju
        const clickedButton = document.querySelector(`[data-meeting-id="${meetingId}"]`);
        const juFilePath = clickedButton?.dataset.path;
        const audioUrl = clickedButton?.dataset.audioUrl;

        if (juFilePath) {
            // Cargar los detalles del archivo .ju
            loadJuDetails(juFilePath, audioUrl);
        } else {
            // No hay archivo .ju, mostrar solo la información básica
            showNoJuFileMessage();
        }
    })
    .catch(error => {
        console.error('Error cargando reunión:', error);
        modalTitle.textContent = 'Error al cargar reunión';
        modalResumen.innerHTML = `
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">No se pudo cargar la reunión</h3>
                <p class="text-gray-500 mb-4">${error.message || 'Ocurrió un error inesperado'}</p>
                <button onclick="openMeetingModal(${meetingId})" class="btn btn-primary mr-2">
                    Reintentar
                </button>
                <button onclick="closeMeetingModal()" class="btn btn-outline">
                    Cerrar
                </button>
            </div>
        `;
    });
}

// Función para renderizar la información básica de la reunión
function renderBasicMeetingInfo(data) {
    const { meeting = {}, tasks = [] } = data || {};
    const modalTitle = document.getElementById('modalTitulo');
    const modalResumen = document.getElementById('modalResumen');

    // Actualizar título
    modalTitle.textContent = meeting.name || 'Detalles de la reunión';

    // Mostrar información básica
    const basicInfo = [];
    if (meeting.description) {
        basicInfo.push(`<strong>Descripción:</strong> ${meeting.description}`);
    }
    if (meeting.started_at) {
        const startDate = new Date(meeting.started_at);
        basicInfo.push(`<strong>Fecha:</strong> ${startDate.toLocaleString()}`);
    }
    if (meeting.duration_minutes) {
        basicInfo.push(`<strong>Duración:</strong> ${meeting.duration_minutes} minutos`);
    }
    if (meeting.status) {
        basicInfo.push(`<strong>Estado:</strong> ${meeting.status}`);
    }

    modalResumen.innerHTML = basicInfo.length > 0
        ? basicInfo.join('<br>')
        : 'Cargando información detallada...';
}

// Función para cargar los detalles del archivo .ju
function loadJuDetails(juFilePath, audioUrl) {
    const modalResumen = document.getElementById('modalResumen');

    // Mostrar estado de carga para el archivo .ju
    modalResumen.innerHTML += `
        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg" id="ju-loading">
            <div class="flex items-center">
                <div class="animate-spin rounded-full h-5 w-5 border-2 border-blue-600 border-t-transparent mr-3"></div>
                <div>
                    <h4 class="text-sm font-medium text-blue-800">Procesando archivo .ju</h4>
                    <p class="text-sm text-blue-700">Desencriptando y cargando contenido detallado...</p>
                </div>
            </div>
        </div>
    `;

    // Hacer petición a showDetails
    const detailsUrl = "{{ route('reuniones.showDetails') }}";
    const params = new URLSearchParams({
        path: juFilePath,
        audio_url: audioUrl || ''
    });

    fetch(`${detailsUrl}?${params}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error al cargar los detalles del archivo .ju');
        }
        return response.json();
    })
    .then(juData => {
        // Remover el indicador de carga
        const loadingElement = document.getElementById('ju-loading');
        if (loadingElement) {
            loadingElement.remove();
        }

        // Mostrar banner de éxito
        showSuccessBanner('Archivo .ju procesado exitosamente', 'Información detallada cargada correctamente.');

        // Actualizar resumen
        const summary = juData.summary || 'Sin resumen disponible.';
        modalResumen.innerHTML = modalResumen.innerHTML.replace('Cargando información detallada...', '') +
            `<div class="mt-4"><strong>Resumen:</strong><br>${summary}</div>`;

        // Mostrar puntos clave
        fillKeyPoints(juData.key_points || []);

        // Mostrar segmentos
        fillSegments(juData.segments || []);

        // Cargar audio
        if (juData.audio_url) {
            loadAudio(juData.audio_url);
        }
    })
    .catch(error => {
        console.error('Error cargando detalles .ju:', error);

        // Remover el indicador de carga
        const loadingElement = document.getElementById('ju-loading');
        if (loadingElement) {
            loadingElement.remove();
        }

        // Mostrar banner de error
        showErrorBanner('Error al procesar archivo .ju', error.message || 'No se pudo desencriptar el archivo.');

        // Limpiar secciones que no se pudieron cargar
        fillKeyPoints([]);
        fillSegments([]);
    });
}

// Función para mostrar mensaje cuando no hay archivo .ju
function showNoJuFileMessage() {
    showWarningBanner('Sin archivo .ju disponible', 'Esta reunión no tiene archivo de transcripción procesado.');
    fillKeyPoints([]);
    fillSegments([]);
}

// Funciones para mostrar banners de estado
function showSuccessBanner(title, message) {
    showBanner('success', title, message, 'green');
}

function showErrorBanner(title, message) {
    showBanner('error', title, message, 'red');
}

function showWarningBanner(title, message) {
    showBanner('warning', title, message, 'yellow');
}

function showBanner(type, title, message, color) {
    const modalResumen = document.getElementById('modalResumen');
    const banner = document.createElement('div');
    banner.className = `bg-${color}-50 border border-${color}-200 rounded-lg p-4 mb-4 status-banner`;

    let icon = '';
    if (type === 'success') {
        icon = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>';
    } else if (type === 'error') {
        icon = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>';
    } else {
        icon = '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>';
    }

    banner.innerHTML = `
        <div class="flex items-start">
            <svg class="w-5 h-5 text-${color}-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                ${icon}
            </svg>
            <div>
                <h4 class="text-sm font-medium text-${color}-800">${title}</h4>
                <p class="text-sm text-${color}-700 mt-1">${message}</p>
            </div>
        </div>
    `;

    modalResumen.parentNode.insertBefore(banner, modalResumen);
}

// Función para cerrar el modal
function closeMeetingModal() {
    console.log('closeMeetingModal llamada');
    const modal = document.getElementById('reunionModal');
    if (modal) {
        modal.classList.add('modal-oculto');
        document.body.classList.remove('modal-open');

        // Limpiar contenido
        const modalTitle = document.getElementById('modalTitulo');
        const modalResumen = document.getElementById('modalResumen');
        const modalPuntosClave = document.getElementById('modalPuntosClave');
        const modalSegmentos = document.getElementById('modalSegmentos');

        if (modalTitle) modalTitle.textContent = 'Reunión';
        if (modalResumen) modalResumen.textContent = 'Selecciona una reunión para ver los detalles.';
        if (modalPuntosClave) modalPuntosClave.innerHTML = '';
        if (modalSegmentos) modalSegmentos.innerHTML = '';

        // Limpiar banners de estado
        const banners = modal.querySelectorAll('.status-banner, .bg-red-50, .bg-yellow-50, .bg-green-50, .bg-blue-50');
        banners.forEach(banner => banner.remove());

        // Limpiar audio
        const modalAudio = document.getElementById('modalAudio');
        const modalAudioStatus = document.getElementById('modalAudioStatus');
        if (modalAudio) {
            modalAudio.pause();
            modalAudio.removeAttribute('src');
            modalAudio.classList.add('hidden');
        }
        if (modalAudioStatus) {
            modalAudioStatus.textContent = 'Selecciona una reunión para cargar el audio.';
        }

        // Resetear variable global
        currentMeetingId = null;
    }
}

</script>
@endsection
