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
    <div class="modal-content max-w-4xl">
        <div class="modal-header">
            <h3 class="modal-title" id="viewMeetingTitle">Detalles de la reunión</h3>
            <button class="modal-close" onclick="closeMeetingModal()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div id="meetingModalBody" class="space-y-6">
            <div class="py-12 text-center text-gray-500" id="meetingModalPlaceholder">
                Selecciona una reunión para ver sus detalles.
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
    modal.style.display = 'none';
    document.getElementById('viewMeetingTitle').textContent = 'Detalles de la reunión';
    document.getElementById('meetingModalBody').innerHTML = '<div class="py-12 text-center text-gray-500" id="meetingModalPlaceholder">Selecciona una reunión para ver sus detalles.</div>';

    if (meetingModalAbortController) {
        meetingModalAbortController.abort();
        meetingModalAbortController = null;
    }

    meetingAudioElement = null;
}

function renderMeetingModal(payload) {
    const { meeting = {}, ju = {}, tasks = [], ju_error = null, ju_needs_encryption = false } = payload || {};
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

    const tasksHtml = Array.isArray(tasks) && tasks.length
        ? `<div class="space-y-4">${tasks.map(task => renderTask(task)).join('')}</div>`
        : '<p class="text-sm text-gray-500">Sin tareas registradas para esta reunión.</p>';

    const segmentsSource = ju && Array.isArray(ju.segments) ? ju.segments : [];
    const segmentsHtml = buildSegmentsHtml(segmentsSource);

    const audioHtml = meeting.audio_url
        ? `<div class="ddu-card shadow-none border border-gray-200"><div class="p-4 space-y-3"><div><h4 class="text-lg font-semibold text-gray-900">Grabación</h4><p class="text-sm text-gray-500">Escucha el audio completo o salta a los segmentos específicos.</p></div><audio id="meetingAudioPlayer" controls class="w-full rounded-lg" src="${escapeAttribute(meeting.audio_url)}"></audio></div></div>`
        : '<div class="ddu-card shadow-none border border-gray-200"><div class="p-4 text-sm text-gray-500">No se encontró un audio asociado a esta reunión.</div></div>';

    const transcriptLink = meeting.transcript_url
        ? `<a class="btn btn-sm btn-outline" href="${escapeAttribute(meeting.transcript_url)}" target="_blank" rel="noopener">Abrir transcripción original</a>`
        : '';

    const descriptionHtml = meeting.description
        ? `<p class="text-gray-600">${escapeHtml(meeting.description)}</p>`
        : '';

    // Banner de información sobre desencriptación
    const decryptionBanner = renderDecryptionBanner(ju, ju_error, ju_needs_encryption);

    modalBody.innerHTML = `
        <div class="space-y-6">
            <div class="ddu-card shadow-none border border-gray-200">
                <div class="p-4 space-y-3">
                    <div class="flex flex-wrap items-center gap-3">${metaItems.join('')}</div>
                    ${descriptionHtml}
                    <div class="flex flex-wrap gap-2">${containersHtml}</div>
                    ${transcriptLink ? `<div>${transcriptLink}</div>` : ''}
                </div>
            </div>

            ${decryptionBanner}

            ${audioHtml}

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="ddu-card shadow-none border border-gray-200">
                    <div class="p-4 space-y-3">
                        <h4 class="text-lg font-semibold text-gray-900">Resumen</h4>
                        ${summary}
                    </div>
                </div>
                <div class="ddu-card shadow-none border border-gray-200">
                    <div class="p-4 space-y-3">
                        <h4 class="text-lg font-semibold text-gray-900">Puntos clave</h4>
                        ${keyPoints}
                    </div>
                </div>
            </div>

            <div class="ddu-card shadow-none border border-gray-200">
                <div class="p-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-lg font-semibold text-gray-900">Transcripción segmentada</h4>
                        <p class="text-xs text-gray-500">Haz clic en un segmento para reproducirlo.</p>
                    </div>
                    ${segmentsHtml}
                </div>
            </div>

            <div class="ddu-card shadow-none border border-gray-200">
                <div class="p-4 space-y-3">
                    <h4 class="text-lg font-semibold text-gray-900">Tareas de seguimiento</h4>
                    ${tasksHtml}
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

function buildSegmentsHtml(segments) {
    if (!Array.isArray(segments) || !segments.length) {
        return '<p class="text-sm text-gray-500">No se encontraron segmentos de transcripción.</p>';
    }

    let html = '';
    let currentSpeaker = null;

    segments.forEach((segment, index) => {
        const speaker = typeof segment.speaker === 'string' && segment.speaker.trim() !== '' ? segment.speaker.trim() : `Hablante ${index + 1}`;
        const startSeconds = parseTimeToSeconds(segment.start);
        const label = formatTimeLabel(segment.start);
        const text = typeof segment.text === 'string' ? segment.text : '';

        if (speaker !== currentSpeaker) {
            if (currentSpeaker !== null) {
                html += '</div>';
            }
            html += `<div class="space-y-2"><h5 class="text-sm font-semibold text-gray-900">${escapeHtml(speaker)}</h5>`;
            currentSpeaker = speaker;
        }

        html += `
            <button type="button" class="w-full text-left border border-gray-200 rounded-lg px-3 py-2 hover:bg-ddu-lavanda/10 focus:outline-none focus:ring-2 focus:ring-ddu-lavanda" data-start-seconds="${Number.isFinite(startSeconds) ? startSeconds : ''}" onclick="seekSegment(this)">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span>${escapeHtml(label)}</span>
                    <span>Segmento</span>
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
    modalBody.innerHTML = '<div class="py-12 text-center text-gray-500"><div class="animate-spin inline-block w-6 h-6 border-4 border-ddu-lavanda border-t-transparent rounded-full mr-2"></div>Desencriptando archivo .ju y cargando detalles...</div>';
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

            modalTitle.textContent = 'Detalles de la reunión';
            modalBody.innerHTML = `<div class="py-12 text-center text-red-500">${escapeHtml(error.message || 'Ocurrió un error al obtener la información.')}</div>`;
        });
}
</script>
@endsection
