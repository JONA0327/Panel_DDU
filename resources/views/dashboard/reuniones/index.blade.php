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

        <button class="btn btn-primary" onclick="showCreateMeetingModal()">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nueva Reunión
        </button>
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

            <div class="divide-y divide-gray-200">
                @forelse ($meetings as $meeting)
                    <div class="p-6 hover:bg-gray-50 transition-colors cursor-pointer" data-meeting-id="{{ $meeting->id }}" onclick="openMeetingModal({{ $meeting->id }})">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-gradient-to-r from-ddu-lavanda to-ddu-aqua rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900">{{ $meeting->meeting_name }}</h4>
                                        @if ($meeting->meeting_description)
                                            <p class="text-gray-600">{{ Str::limit($meeting->meeting_description, 140) }}</p>
                                        @endif
                                        <div class="flex flex-wrap items-center gap-4 mt-2 text-sm text-gray-500">
                                            @if ($meeting->started_at)
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    {{ $meeting->started_at->format('d/m/Y H:i') }}
                                                </span>
                                            @endif
                                            @if ($meeting->duration_minutes)
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ $meeting->duration_minutes }} min
                                                </span>
                                            @endif
                                            @if ($meeting->containers->isNotEmpty())
                                                <span class="flex items-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h7" />
                                                    </svg>
                                                    {{ $meeting->containers->pluck('name')->implode(', ') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <span class="px-3 py-1 text-xs font-medium rounded-full {{ $meeting->status_badge_color }}">
                                    {{ $meeting->status_label }}
                                </span>
                                <div class="flex space-x-2">
                                    @if ($meeting->transcript_download_url)
                                        <a class="btn btn-sm btn-outline" href="{{ $meeting->transcript_download_url }}" target="_blank" rel="noopener" onclick="event.stopPropagation();">
                                            Transcripción
                                        </a>
                                    @endif
                                    @if ($meeting->audio_download_url)
                                        <a class="btn btn-sm btn-primary" href="{{ $meeting->audio_download_url }}" target="_blank" rel="noopener" onclick="event.stopPropagation();">
                                            Audio
                                        </a>
                                    @endif
                                </div>
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
</div>

<!-- Modal para ver detalles de la reunión -->
<div id="viewMeetingModal" class="modal" style="display: none;">
    <div class="modal-content max-w-6xl max-h-[90vh] overflow-hidden">
        <div class="modal-header bg-gradient-to-r from-ddu-lavanda to-ddu-aqua text-white">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="modal-title text-xl font-semibold" id="viewMeetingTitle">Detalles de la reunión</h3>
            </div>
            <button class="modal-close text-white hover:text-gray-200 transition-colors" onclick="closeMeetingModal()">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div id="meetingModalBody" class="p-6 overflow-y-auto max-h-[calc(90vh-80px)] space-y-6">
            <div class="py-12 text-center text-gray-500 flex items-center justify-center" id="meetingModalPlaceholder">
                <div class="text-center">
                    <div class="animate-pulse mb-4">
                        <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <p class="text-lg font-medium text-gray-600">Selecciona una reunión para ver sus detalles</p>
                    <p class="text-sm text-gray-400 mt-2">La información se cargará automáticamente</p>
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

.segment-button {
    transition: all 0.2s ease;
}

.segment-button:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
</style>

<script>
const meetingsShowBaseUrl = "{{ url('/reuniones') }}/";
let meetingModalAbortController = null;
let meetingAudioElement = null;

document.getElementById('viewMeetingModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeMeetingModal();
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('viewMeetingModal');
        if (modal.style.display === 'flex') {
            closeMeetingModal();
        }
    }
});

function showCreateMeetingModal() {
    document.getElementById('createMeetingModal').style.display = 'flex';
    // Establecer fecha mínima como hoy
    const today = new Date().toISOString().split('T')[0];
    document.querySelector('input[name="date"]').setAttribute('min', today);
}

function hideCreateMeetingModal() {
    document.getElementById('createMeetingModal').style.display = 'none';
    document.getElementById('createMeetingForm').reset();
}



function closeMeetingModal() {
    const modal = document.getElementById('viewMeetingModal');

    // Agregar animación de salida
    modal.style.animation = 'fadeOut 0.3s ease-out';
    const modalContent = modal.querySelector('.modal-content');
    modalContent.style.animation = 'slideOut 0.3s ease-out';

    setTimeout(() => {
        modal.style.display = 'none';
        modal.style.animation = '';
        modalContent.style.animation = '';
    }, 300);

    if (meetingModalAbortController) {
        meetingModalAbortController.abort();
        meetingModalAbortController = null;
    }

    if (meetingAudioElement) {
        meetingAudioElement.pause();
        meetingAudioElement = null;
    }

    const modalBody = document.getElementById('meetingModalBody');
    const modalTitle = document.getElementById('viewMeetingTitle');
    modalTitle.textContent = 'Detalles de la reunión';
    modalBody.innerHTML = `
        <div class="py-12 text-center text-gray-500 flex items-center justify-center">
            <div class="text-center">
                <div class="animate-pulse mb-4">
                    <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <p class="text-lg font-medium text-gray-600">Selecciona una reunión para ver sus detalles</p>
                <p class="text-sm text-gray-400 mt-2">La información se cargará automáticamente</p>
            </div>
        </div>
    `;
}

function renderMeetingModal(payload) {
    const {
        meeting = {},
        ju = {},
        tasks = [],
        ju_error = null,
        ju_needs_encryption = false,
        ju_action_items = [],
        ju_source = null,
    } = payload || {};
    const modalBody = document.getElementById('meetingModalBody');
    const modalTitle = document.getElementById('viewMeetingTitle');

    modalTitle.textContent = meeting.name ? escapeHtml(meeting.name) : 'Detalles de la reunión';

    const metaItems = [];
    if (meeting.started_at) {
        metaItems.push(`<span class="flex items-center text-sm text-gray-600"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>${formatDateTime(meeting.started_at)}</span>`);
    }
    if (meeting.duration_minutes) {
        metaItems.push(`<span class="flex items-center text-sm text-gray-600"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>${meeting.duration_minutes} min</span>`);
    }
    if (meeting.status) {
        metaItems.push(`<span class="px-3 py-1 text-xs font-medium rounded-full bg-ddu-lavanda/10 text-ddu-lavanda">${escapeHtml(meeting.status)}</span>`);
    }

    const containers = Array.isArray(meeting.containers) ? meeting.containers : [];
    const containersHtml = containers.length
        ? containers.map(container => `<span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-medium">${escapeHtml(container.name)}</span>`).join(' ')
        : '<span class="text-sm text-gray-500">Sin contenedores asociados.</span>';

    const summary = ju && typeof ju.summary === 'string' && ju.summary.trim() !== ''
        ? `<p class="text-gray-700 leading-relaxed whitespace-pre-line">${escapeHtml(ju.summary)}</p>`
        : '<p class="text-sm text-gray-500">Sin resumen disponible.</p>';

    const keyPointsSource = ju && Array.isArray(ju.key_points) ? ju.key_points : [];
    const keyPoints = keyPointsSource.length
        ? `<ul class="list-disc pl-6 space-y-2 text-gray-700">${keyPointsSource.map(point => `<li>${escapeHtml(point)}</li>`).join('')}</ul>`
        : '<p class="text-sm text-gray-500">Sin puntos clave registrados.</p>';

    let tasksHtml = '<p class="text-sm text-gray-500">Sin tareas registradas para esta reunión.</p>';
    if (Array.isArray(tasks) && tasks.length) {
        tasksHtml = `<div class="space-y-4">${tasks.map(task => renderTask(task)).join('')}</div>`;
    } else if (Array.isArray(ju_action_items) && ju_action_items.length) {
        tasksHtml = `<div class="space-y-4">${ju_action_items.map((item, index) => renderJuActionItem(item, index)).join('')}</div>`;
    }

    const segmentsSource = ju && Array.isArray(ju.segments) ? ju.segments : [];
    const segmentsHtml = buildSegmentsHtml(segmentsSource);

    const audioHtml = meeting.audio_url
        ? `<div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="p-6 space-y-4">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M9 12a1 1 0 01.117-.993l4-2.292c.359-.207.883-.207 1.242 0l4 2.292A1 1 0 0119 12v0a1 1 0 01-.883.993l-4 2.292c-.359.207-.883.207-1.242 0l-4-2.292A1 1 0 019 12z"></path>
                        </svg>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900">Grabación de audio</h4>
                            <p class="text-sm text-gray-500">Escucha el audio completo o usa los segmentos para navegar</p>
                        </div>
                    </div>
                    <audio id="meetingAudioPlayer" controls class="w-full rounded-lg bg-gray-50 border border-gray-200" src="${escapeAttribute(meeting.audio_url)}"></audio>
                </div>
            </div>`
        : `<div class="bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
                <div class="p-6 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18 12M6 6l12 12"></path>
                    </svg>
                    <p class="text-sm text-gray-500">No se encontró un audio asociado a esta reunión</p>
                </div>
            </div>`;

    const transcriptLink = meeting.transcript_url
        ? `<a class="btn btn-sm btn-outline" href="${escapeAttribute(meeting.transcript_url)}" target="_blank" rel="noopener">Abrir transcripción original</a>`
        : '';

    const descriptionHtml = meeting.description
        ? `<p class="text-gray-600">${escapeHtml(meeting.description)}</p>`
        : '';

    // Banner de información sobre desencriptación
    const decryptionBanner = renderDecryptionBanner(ju, ju_error, ju_needs_encryption);
    const juDetailsHtml = buildJuDetailsCard(ju, ju_source);

    modalBody.innerHTML = `
        <div class="space-y-6">
            <!-- Información general de la reunión -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6">
                <div class="flex flex-wrap items-center gap-4 mb-4">${metaItems.join('')}</div>
                ${descriptionHtml ? `<div class="mb-4">${descriptionHtml}</div>` : ''}
                <div class="flex flex-wrap gap-2">${containersHtml}</div>
                ${transcriptLink ? `<div class="mt-4">${transcriptLink}</div>` : ''}
            </div>

            ${decryptionBanner}

            ${juDetailsHtml}

            ${audioHtml}

            <!-- Resumen y puntos clave -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-6 space-y-4">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-ddu-lavanda mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h4 class="text-lg font-semibold text-gray-900">Resumen ejecutivo</h4>
                        </div>
                        <div class="pl-9">
                            ${summary}
                        </div>
                    </div>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                    <div class="p-6 space-y-4">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-ddu-aqua mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <h4 class="text-lg font-semibold text-gray-900">Puntos clave</h4>
                        </div>
                        <div class="pl-9">
                            ${keyPoints}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tareas de seguimiento -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="p-6 space-y-4">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                        <h4 class="text-lg font-semibold text-gray-900">Tareas de seguimiento</h4>
                    </div>
                    <div class="pl-9">
                        ${tasksHtml}
                    </div>
                </div>
            </div>

            <!-- Transcripción segmentada -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m3 0H4a2 2 0 00-2 2v11a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11h4M10 15h4"></path>
                            </svg>
                            <h4 class="text-lg font-semibold text-gray-900">Transcripción segmentada</h4>
                        </div>
                        <p class="text-xs text-gray-500 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path>
                            </svg>
                            Haz clic en un segmento para reproducirlo
                        </p>
                    </div>
                    <div class="pl-9 max-h-96 overflow-y-auto">
                        ${segmentsHtml}
                    </div>
                </div>
            </div>
        </div>
    `;

    meetingAudioElement = document.getElementById('meetingAudioPlayer');
}

function renderDecryptionBanner(ju, juError, needsEncryption) {
    if (juError) {
        return `
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-red-800">Error al procesar archivo .ju</h4>
                        <p class="text-sm text-red-700 mt-1">${escapeHtml(juError)}</p>
                        <button onclick="retryDecryption()" class="mt-2 text-sm text-red-600 hover:text-red-800 font-medium">
                            Reintentar desencriptación
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    if (needsEncryption) {
        return `
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-yellow-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-yellow-800">Archivo .ju requiere desencriptación</h4>
                        <p class="text-sm text-yellow-700 mt-1">El archivo contiene información encriptada que necesita ser procesada.</p>
                    </div>
                </div>
            </div>
        `;
    }

    if (ju && (ju.summary || ju.key_points?.length || ju.segments?.length)) {
        return `
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-green-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-green-800">Archivo .ju procesado exitosamente</h4>
                        <p class="text-sm text-green-700 mt-1">Se ha extraído y desencriptado la información de la reunión correctamente.</p>
                    </div>
                </div>
            </div>
        `;
    }

    return ''; // No mostrar banner si no hay información relevante
}

function buildJuDetailsCard(ju, source) {
    if (!ju || typeof ju !== 'object') {
        return '';
    }

    const infoBadges = [];

    if (ju.timestamp) {
        const timestampLabel = formatDateTime(ju.timestamp);
        infoBadges.push(`<span class="inline-flex items-center px-2 py-1 rounded-full bg-ddu-lavanda/10 text-ddu-lavanda text-xs font-medium">${escapeHtml(timestampLabel)}</span>`);
    }

    if (ju.duration !== null && ju.duration !== undefined && ju.duration !== '') {
        const durationLabel = formatDurationLabel(ju.duration);
        if (durationLabel) {
            infoBadges.push(`<span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-medium">${escapeHtml(durationLabel)}</span>`);
        }
    }

    const sourceLabel = formatJuSourceLabel(source);

    const participants = Array.isArray(ju.participants)
        ? ju.participants
            .map(participant => {
                if (!participant || typeof participant !== 'object') {
                    return null;
                }

                const name = participant.name || participant.nombre || '';
                if (!name) {
                    return null;
                }

                const role = participant.role || participant.rol || '';
                return {
                    name: escapeHtml(name),
                    role: role ? escapeHtml(role) : null,
                };
            })
            .filter(Boolean)
        : [];

    const metadataEntries = ju.metadata && typeof ju.metadata === 'object' && !Array.isArray(ju.metadata)
        ? Object.entries(ju.metadata)
            .filter(([key, value]) => key && value !== null && value !== '')
            .map(([key, value]) => [escapeHtml(String(key)), escapeHtml(formatMetadataValue(value))])
        : [];

    if (!infoBadges.length && !participants.length && !metadataEntries.length && !sourceLabel) {
        return '';
    }

    const badgesHtml = infoBadges.length
        ? `<div class="flex flex-wrap gap-2">${infoBadges.join('')}</div>`
        : '';

    const sourceHtml = sourceLabel
        ? `<p class="text-xs text-gray-500">Origen: ${escapeHtml(sourceLabel)}</p>`
        : '';

    const participantsHtml = participants.length
        ? `<div class="space-y-2">
                <h5 class="text-sm font-semibold text-gray-900">Participantes</h5>
                <div class="flex flex-wrap gap-2">
                    ${participants.map(({ name, role }) => `<span class="inline-flex items-center px-3 py-1 rounded-full bg-ddu-aqua/10 text-ddu-aqua text-sm font-medium">${name}${role ? `<span class="ml-2 text-xs text-ddu-aqua/80">${role}</span>` : ''}</span>`).join('')}
                </div>
            </div>`
        : '';

    const metadataHtml = metadataEntries.length
        ? `<div class="space-y-2">
                <h5 class="text-sm font-semibold text-gray-900">Metadata</h5>
                <dl class="space-y-1 text-sm text-gray-600">
                    ${metadataEntries.map(([key, value]) => `<div class="flex items-start justify-between gap-2"><dt class="font-medium text-gray-700">${key}</dt><dd class="text-right flex-1">${value}</dd></div>`).join('')}
                </dl>
            </div>`
        : '';

    return `
        <div class="ddu-card shadow-none border border-gray-200">
            <div class="p-4 space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-lg font-semibold text-gray-900">Detalles del archivo .ju</h4>
                </div>
                ${badgesHtml}
                ${sourceHtml}
                ${participantsHtml}
                ${metadataHtml}
            </div>
        </div>
    `;
}

function renderTask(task) {
    const priority = task.prioridad ? `<span class="px-2 py-0.5 rounded-full text-xs font-medium bg-ddu-lavanda/10 text-ddu-lavanda">${escapeHtml(task.prioridad)}</span>` : '';
    const dates = [];
    if (task.fecha_inicio) {
        dates.push(`Inicio: ${formatDate(task.fecha_inicio)}`);
    }
    if (task.fecha_limite) {
        dates.push(`Entrega: ${formatDate(task.fecha_limite)}`);
    }

    const description = task.descripcion ? `<p class="text-sm text-gray-600">${escapeHtml(task.descripcion)}</p>` : '';
    const progress = Number.isFinite(Number(task.progreso)) ? Math.max(0, Math.min(100, Number(task.progreso))) : 0;

    return `
        <div class="border border-gray-200 rounded-lg p-4 space-y-2">
            <div class="flex items-center justify-between">
                <h5 class="font-semibold text-gray-900">${escapeHtml(task.tarea)}</h5>
                ${priority}
            </div>
            ${dates.length ? `<p class="text-xs text-gray-500">${dates.join(' • ')}</p>` : ''}
            ${description}
            <div>
                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span>Progreso</span>
                    <span>${progress}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-ddu-lavanda h-2 rounded-full" style="width: ${progress}%"></div>
                </div>
            </div>
        </div>
    `;
}

function renderJuActionItem(item, index) {
    if (!item || typeof item !== 'object') {
        return '';
    }

    const title = escapeHtml(item.title || item.description || `Tarea ${index + 1}`);
    const priority = item.priority ? `<span class="px-2 py-0.5 rounded-full text-xs font-medium bg-ddu-lavanda/10 text-ddu-lavanda">${escapeHtml(item.priority)}</span>` : '';

    const chips = [];
    if (item.owner) {
        chips.push(`<span class="inline-flex items-center px-2 py-0.5 rounded-full bg-ddu-aqua/10 text-ddu-aqua text-xs font-medium">${escapeHtml(item.owner)}</span>`);
    }
    if (item.status) {
        chips.push(`<span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-xs font-medium">${escapeHtml(item.status)}</span>`);
    }

    const dates = [];
    if (item.start_date) {
        dates.push(`Inicio: ${escapeHtml(formatDate(item.start_date))}`);
    }
    if (item.due_date) {
        dates.push(`Entrega: ${escapeHtml(formatDate(item.due_date))}`);
    }

    const description = item.description ? `<p class="text-sm text-gray-600">${escapeHtml(item.description)}</p>` : '';

    const progressValue = Number.isFinite(Number(item.progress)) ? Math.max(0, Math.min(100, Number(item.progress))) : null;
    const progressHtml = Number.isFinite(progressValue)
        ? `
            <div>
                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span>Progreso</span>
                    <span>${progressValue}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-ddu-lavanda h-2 rounded-full" style="width: ${progressValue}%"></div>
                </div>
            </div>
        `
        : '';

    const chipsHtml = chips.length ? `<div class="flex flex-wrap gap-2 text-xs text-gray-500">${chips.join('')}</div>` : '';
    const datesHtml = dates.length ? `<p class="text-xs text-gray-500">${dates.join(' • ')}</p>` : '';

    return `
        <div class="border border-gray-200 rounded-lg p-4 space-y-2">
            <div class="flex items-center justify-between">
                <h5 class="font-semibold text-gray-900">${title}</h5>
                ${priority}
            </div>
            ${chipsHtml}
            ${datesHtml}
            ${description}
            ${progressHtml}
        </div>
    `;
}

function buildSegmentsHtml(segments) {
    if (!Array.isArray(segments) || !segments.length) {
        return '<p class="text-sm text-gray-500">No se encontraron segmentos de transcripción.</p>';
    }

    let html = '';
    let currentSpeaker = null;

    segments.forEach((segment, index) => {
        const speaker = typeof segment.speaker === 'string' && segment.speaker.trim() !== '' ? segment.speaker.trim() : `Hablante ${index + 1}`;
        const startValue = segment.start ?? segment.timestamp ?? segment.start_time ?? segment.from ?? null;
        const labelSource = segment.label ?? startValue;
        const startSeconds = parseTimeToSeconds(startValue);
        const label = formatTimeLabel(labelSource ?? index * 60);
        const text = typeof segment.text === 'string' ? segment.text : '';

        if (speaker !== currentSpeaker) {
            if (currentSpeaker !== null) {
                html += '</div>';
            }
            html += `<div class="space-y-2"><h5 class="text-sm font-semibold text-gray-900">${escapeHtml(speaker)}</h5>`;
            currentSpeaker = speaker;
        }

        html += `
            <button type="button" class="segment-button w-full text-left border border-gray-200 rounded-lg px-4 py-3 hover:bg-ddu-lavanda/10 hover:border-ddu-lavanda/30 focus:outline-none focus:ring-2 focus:ring-ddu-lavanda focus:border-ddu-lavanda" data-start-seconds="${Number.isFinite(startSeconds) ? startSeconds : ''}" onclick="seekSegment(this)">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                    <span class="font-medium">${escapeHtml(label)}</span>
                    <span class="flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h1m4 0h1"></path>
                        </svg>
                        Reproducir
                    </span>
                </div>
                <p class="text-sm text-gray-700 leading-relaxed">${escapeHtml(text)}</p>
            </button>
        `;
    });

    if (currentSpeaker !== null) {
        html += '</div>';
    }

    return html;
}

function seekSegment(button) {
    if (!meetingAudioElement) {
        return;
    }

    const seconds = parseFloat(button.getAttribute('data-start-seconds'));
    if (!Number.isFinite(seconds)) {
        return;
    }

    meetingAudioElement.currentTime = Math.max(0, seconds);
    meetingAudioElement.play();
}

function parseTimeToSeconds(value) {
    if (value === null || value === undefined) {
        return NaN;
    }

    if (typeof value === 'number') {
        return value;
    }

    const stringValue = String(value).trim();
    if (stringValue === '') {
        return NaN;
    }

    if (/^\d+(\.\d+)?$/.test(stringValue)) {
        return parseFloat(stringValue);
    }

    const parts = stringValue.split(':').map(Number);
    if (parts.some(number => Number.isNaN(number))) {
        return NaN;
    }

    let seconds = 0;
    for (let i = 0; i < parts.length; i++) {
        const valuePart = parts[parts.length - 1 - i];
        seconds += valuePart * Math.pow(60, i);
    }

    return seconds;
}

function formatTimeLabel(value) {
    if (value === null || value === undefined) {
        return '0:00';
    }

    if (typeof value === 'number') {
        return formatSeconds(value);
    }

    const stringValue = String(value).trim();
    if (stringValue === '') {
        return '0:00';
    }

    if (/^\d+(\.\d+)?$/.test(stringValue)) {
        return formatSeconds(parseFloat(stringValue));
    }

    return stringValue;
}

function formatSeconds(totalSeconds) {
    const seconds = Math.max(0, Math.floor(totalSeconds));
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const remainingSeconds = seconds % 60;

    const formattedMinutes = String(minutes).padStart(hours > 0 ? 2 : 1, '0');
    const formattedSeconds = String(remainingSeconds).padStart(2, '0');

    return hours > 0
        ? `${hours}:${formattedMinutes}:${formattedSeconds}`
        : `${formattedMinutes}:${formattedSeconds}`;
}

function formatDateTime(isoString) {
    try {
        const date = new Date(isoString);
        if (Number.isNaN(date.getTime())) {
            return isoString;
        }

        return date.toLocaleString('es-ES', {
            dateStyle: 'medium',
            timeStyle: 'short'
        });
    } catch (error) {
        return isoString;
    }
}

function formatDate(isoString) {
    try {
        const date = new Date(isoString);
        if (Number.isNaN(date.getTime())) {
            return isoString;
        }

        return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (error) {
        return isoString;
    }
}

function formatDurationLabel(value) {
    if (value === null || value === undefined) {
        return '';
    }

    if (typeof value === 'number' && !Number.isNaN(value)) {
        if (value <= 0) {
            return '';
        }

        if (value > 120) {
            return formatSeconds(value);
        }

        return `${Math.round(value)} min`;
    }

    const stringValue = String(value).trim();
    if (stringValue === '') {
        return '';
    }

    if (/^\d+(\.\d+)?$/.test(stringValue)) {
        const numeric = parseFloat(stringValue);
        if (numeric > 120) {
            return formatSeconds(numeric);
        }

        return `${Math.round(numeric)} min`;
    }

    return stringValue;
}

function formatMetadataValue(value) {
    if (value === null || value === undefined) {
        return '';
    }

    if (Array.isArray(value)) {
        return value
            .map(item => {
                if (item === null || item === undefined) {
                    return '';
                }

                if (typeof item === 'object') {
                    return JSON.stringify(item);
                }

                return String(item);
            })
            .filter(Boolean)
            .join(', ');
    }

    if (typeof value === 'object') {
        try {
            return JSON.stringify(value);
        } catch (error) {
            return String(value);
        }
    }

    return String(value);
}

function formatJuSourceLabel(source) {
    switch (source) {
        case 'metadata':
            return 'Metadatos de la reunión';
        case 'filesystem':
            return 'Archivo .ju del sistema de archivos';
        default:
            return '';
    }
}

function escapeHtml(value) {
    if (value === null || value === undefined) {
        return '';
    }

    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function escapeAttribute(value) {
    return escapeHtml(value).replace(/`/g, '&#096;');
}

function editMeeting(id) {
    // TODO: Implementar edición de reunión
    console.log('Editar reunión', id);
}

function viewMeeting(id) {
    openMeetingModal(id);
}

// Manejo del formulario
document.getElementById('createMeetingForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // TODO: Implementar envío al backend
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    console.log('Datos de la reunión:', data);

    // Simular creación exitosa
    hideCreateMeetingModal();

    // Mostrar mensaje de éxito (temporal)
    alert('Reunión creada exitosamente');
});

// Filtros en tiempo real
document.getElementById('searchInput').addEventListener('input', function() {
    // TODO: Implementar filtrado
    console.log('Buscar:', this.value);
});

document.getElementById('statusFilter').addEventListener('change', function() {
    // TODO: Implementar filtrado por estado
    console.log('Filtrar por estado:', this.value);
});

document.getElementById('dateFilter').addEventListener('change', function() {
    // TODO: Implementar filtrado por fecha
    console.log('Filtrar por fecha:', this.value);
});

// Función para reintentar desencriptación
let currentMeetingId = null;

function retryDecryption() {
    if (currentMeetingId) {
        openMeetingModal(currentMeetingId);
    }
}

// Función mejorada para abrir modal con ID tracking
function openMeetingModal(meetingId) {
    currentMeetingId = meetingId;
    const modal = document.getElementById('viewMeetingModal');
    const modalBody = document.getElementById('meetingModalBody');
    const modalTitle = document.getElementById('viewMeetingTitle');

    modal.style.display = 'flex';
    modalTitle.textContent = 'Cargando reunión...';
    modalBody.innerHTML = `
        <div class="py-16 text-center">
            <div class="flex justify-center mb-6">
                <div class="relative">
                    <div class="animate-spin rounded-full h-12 w-12 border-4 border-ddu-lavanda border-t-transparent"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <svg class="w-6 h-6 text-ddu-lavanda" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Procesando reunión</h3>
            <p class="text-gray-500 max-w-md mx-auto">
                Desencriptando archivo .ju y cargando todos los detalles de la reunión.
                Esto puede tardar unos momentos...
            </p>
            <div class="mt-6 flex justify-center space-x-4 text-xs text-gray-400">
                <span class="flex items-center">
                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                    Extrayendo información
                </span>
                <span class="flex items-center">
                    <div class="w-2 h-2 bg-blue-400 rounded-full mr-2 animate-pulse animation-delay-200"></div>
                    Procesando contenido
                </span>
                <span class="flex items-center">
                    <div class="w-2 h-2 bg-purple-400 rounded-full mr-2 animate-pulse animation-delay-400"></div>
                    Cargando transcripción
                </span>
            </div>
        </div>
    `;
    meetingAudioElement = null;

    if (meetingModalAbortController) {
        meetingModalAbortController.abort();
    }

    meetingModalAbortController = new AbortController();

    fetch(`${meetingsShowBaseUrl}${meetingId}`, {
        headers: {
            'Accept': 'application/json'
        },
        signal: meetingModalAbortController.signal
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('No se pudieron cargar los detalles de la reunión.');
            }

            return response.json();
        })
        .then(data => {
            renderMeetingModal(data);
        })
        .catch(error => {
            if (error.name === 'AbortError') {
                return;
            }

            modalTitle.textContent = 'Error al cargar reunión';
            modalBody.innerHTML = `
                <div class="py-16 text-center">
                    <div class="flex justify-center mb-6">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">No se pudo cargar la reunión</h3>
                    <p class="text-gray-500 max-w-md mx-auto mb-6">
                        ${escapeHtml(error.message || 'Ocurrió un error inesperado al obtener la información de la reunión.')}
                    </p>
                    <div class="flex justify-center space-x-3">
                        <button onclick="openMeetingModal(${currentMeetingId || 'null'})" class="btn btn-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Reintentar
                        </button>
                        <button onclick="closeMeetingModal()" class="btn btn-outline">
                            Cerrar
                        </button>
                    </div>
                </div>
            `;
        });
}
</script>
@endsection
