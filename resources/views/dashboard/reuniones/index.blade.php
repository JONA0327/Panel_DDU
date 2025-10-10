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
                    @php
                        $juFilePath = data_get($meeting->metadata, 'ju_local_path')
                            ?? data_get($meeting->metadata, 'ju_file_path')
                            ?? data_get($meeting->metadata, 'ju_path');
                    @endphp
                    <div class="p-6 hover:bg-gray-50 transition-colors">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
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

                            <div class="flex flex-col items-start sm:items-end gap-3">
                                <span class="px-3 py-1 text-xs font-medium rounded-full {{ $meeting->status_badge_color }}">
                                    {{ $meeting->status_label }}
                                </span>
                                @if ($juFilePath)
                                    <button type="button"
                                            class="btn btn-outline btn-sm btn-ver-detalles"
                                            data-path="{{ $juFilePath }}"
                                            data-audio-url="{{ $meeting->audio_download_url }}"
                                            data-title="{{ $meeting->meeting_name }}">
                                        Ver detalles
                                    </button>
                                @else
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
});
</script>
@endsection
