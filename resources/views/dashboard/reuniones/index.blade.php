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
                <h3 class="ddu-card-title">Próximas Reuniones</h3>
                <p class="ddu-card-subtitle">Reuniones programadas y recientes</p>
            </div>
        </div>

        <div class="divide-y divide-gray-200">
            <!-- Ejemplo de reunión programada -->
            <div class="p-6 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-gradient-to-r from-ddu-lavanda to-ddu-aqua rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900">Reunión Semanal de Equipo</h4>
                                <p class="text-gray-600">Revisión de proyectos y planificación semanal</p>
                                <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Próximo lunes 10:00 AM
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Sala de Conferencias
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                        </svg>
                                        8 participantes
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <span class="px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                            Programada
                        </span>
                        <div class="flex space-x-2">
                            <button class="btn btn-sm btn-outline" onclick="editMeeting(1)">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button class="btn btn-sm btn-primary" onclick="viewMeeting(1)">
                                Ver Detalles
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado vacío cuando no hay reuniones -->
            <div class="p-12 text-center" style="display: none;" id="emptyState">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No hay reuniones programadas</h3>
                <p class="text-gray-500 mb-6">Comienza creando tu primera reunión</p>
                <button class="btn btn-primary" onclick="showCreateMeetingModal()">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Crear Primera Reunión
                </button>
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

function editMeeting(id) {
    // TODO: Implementar edición de reunión
    console.log('Editar reunión', id);
}

function viewMeeting(id) {
    // TODO: Implementar vista de detalles
    console.log('Ver reunión', id);
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
</script>
@endsection
