@extends('layouts.dashboard')

@section('page-title', 'Administrar Miembros')
@section('page-description', 'Gestionar usuarios y permisos del sistema DDU')

@section('content')
<div class="space-y-6 fade-in">
    <!-- Header con acciones -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-4 sm:space-y-0">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Administrar Miembros</h2>
            <p class="text-gray-600 mt-1">Gestiona usuarios, roles y permisos del sistema DDU</p>
        </div>

        <button class="btn btn-primary" onclick="showAddMemberModal()">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Agregar Miembro
        </button>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="ddu-card">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Buscar miembro</label>
                    <input type="text"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Nombre o email..."
                           id="searchInput">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rol</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            id="roleFilter">
                        <option value="">Todos los roles</option>
                        <option value="administrador">Administrador</option>
                        <option value="administracion">Administración</option>
                        <option value="ventas">Ventas</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Permiso</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            id="permissionFilter">
                        <option value="">Todos los permisos</option>
                        <option value="colaborador">Colaborador</option>
                        <option value="lector">Lector</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            id="statusFilter">
                        <option value="">Todos</option>
                        <option value="active">Activos</option>
                        <option value="inactive">Inactivos</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="stat-card primary">
            <div class="flex items-center justify-between">
                <div>
                    <div class="stat-number">{{ $stats['total'] }}</div>
                    <div class="stat-label">Total Miembros</div>
                </div>
                <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="stat-number text-green-600">{{ $stats['active'] }}</div>
                    <div class="stat-label text-gray-600">Activos</div>
                </div>
                <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="stat-number text-amber-600">{{ $stats['admins'] }}</div>
                    <div class="stat-label text-gray-600">Administradores</div>
                </div>
                <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                </svg>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="stat-number text-blue-600">{{ $stats['collaborators'] }}</div>
                    <div class="stat-label text-gray-600">Colaboradores</div>
                </div>
                <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Lista de miembros -->
    <div class="ddu-card">
        <div class="ddu-card-header">
            <div>
                <h3 class="ddu-card-title">Miembros del Sistema</h3>
                <p class="ddu-card-subtitle">Lista completa de usuarios registrados</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="ddu-table">
                <thead>
                    <tr>
                        <th class="text-left">Usuario</th>
                        <th class="text-left">Email</th>
                        <th class="text-left">Rol</th>
                        <th class="text-left">Permiso</th>
                        <th class="text-left">Estado</th>
                        <th class="text-left">Último Acceso</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="membersTableBody">
                    @forelse($members as $member)
                    @if($member->user)
                    <tr data-member-id="{{ $member->id }}">
                        <td>
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-blue-600 rounded-full flex items-center justify-center">
                                    <span class="text-white font-semibold text-sm">
                                        @php
                                            $fullName = $member->user->full_name ?? 'Usuario Sin Nombre';
                                            $initials = strtoupper(substr($fullName, 0, 1) . (strpos($fullName, ' ') ? substr($fullName, strpos($fullName, ' ')+1, 1) : substr($fullName, 1, 1)));
                                        @endphp
                                        {{ $initials }}
                                    </span>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $member->user->full_name ?? 'Usuario Sin Nombre' }}</div>
                                    <div class="text-sm text-gray-500">{{ $member->user->username ?? 'sin_usuario' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-gray-900">{{ $member->user->email ?? 'sin-email@ejemplo.com' }}</td>
                        <td>
                            <span class="px-3 py-1 text-xs font-medium rounded-full
                                @if(in_array($member->role, ['administracion', 'administrador']))
                                    bg-red-100 text-red-800
                                @else
                                    bg-purple-100 text-purple-800
                                @endif">
                                @if($member->role === 'administrador')
                                    Administrador
                                @elseif($member->role === 'administracion')
                                    Administración
                                @else
                                    {{ ucfirst($member->role) }}
                                @endif
                            </span>
                        </td>
                        <td>
                            <span class="px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                {{ ucfirst($member->permission->name ?? 'Sin asignar') }}
                            </span>
                        </td>
                        <td>
                            <span class="px-3 py-1 text-xs font-medium rounded-full
                                @if($member->is_active)
                                    bg-green-100 text-green-800
                                @else
                                    bg-red-100 text-red-800
                                @endif">
                                {{ $member->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="text-gray-500">{{ $member->updated_at->diffForHumans() }}</td>
                        <td>
                            <div class="flex justify-center space-x-2">
                                <button class="btn btn-sm btn-outline" onclick="editMember({{ $member->id }})" title="Editar miembro">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button class="btn btn-sm {{ $member->is_active ? 'btn-secondary' : 'btn-primary' }}"
                                        onclick="toggleMemberStatus({{ $member->id }})"
                                        title="{{ $member->is_active ? 'Desactivar' : 'Activar' }} miembro">
                                    @if($member->is_active)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @endif
                                </button>
                                <button class="btn btn-sm text-red-600 hover:bg-red-50" onclick="removeMember({{ $member->id }})" title="Eliminar miembro">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endif
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8">
                            <div class="text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-1">No hay miembros registrados</h3>
                                <p class="text-gray-600">Comienza agregando el primer miembro al sistema DDU</p>
                                <button class="btn btn-primary mt-4" onclick="showAddMemberModal()">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Agregar Primer Miembro
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para agregar miembro -->
<div id="addMemberModal" class="ddu-modal-overlay" style="display: none;">
    <div class="ddu-modal-content max-w-2xl animate-bounce-in">
        <div class="ddu-modal-header">
            <h3 class="ddu-modal-title">Agregar Nuevo Miembro</h3>
            <button class="ddu-modal-close" onclick="hideAddMemberModal()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <form id="addMemberForm" class="space-y-6">
            <!-- Búsqueda de usuario -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar usuario existente</label>
                <div class="relative">
                    <input type="text" id="userSearchInput"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Buscar por username, email o nombre completo..."
                           autocomplete="off">
                    <div id="userSearchResults" class="absolute top-full left-0 right-0 bg-white border border-gray-300 rounded-lg shadow-lg z-10 max-h-60 overflow-y-auto" style="display: none;">
                        <!-- Los resultados se llenarán dinámicamente -->
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-1">Escribe al menos 2 caracteres para buscar</p>
            </div>

            <!-- Usuario seleccionado -->
            <div id="selectedUserInfo" style="display: none;" class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h4 class="font-medium text-blue-900 mb-2">Usuario seleccionado:</h4>
                <div id="selectedUserDetails" class="space-y-1 text-sm text-blue-800">
                    <!-- Se llenará dinámicamente -->
                </div>
                <button type="button" onclick="clearSelectedUser()" class="text-sm text-blue-600 hover:text-blue-800 mt-2">
                    Seleccionar otro usuario
                </button>
                <input type="hidden" id="selectedUserId" name="user_id">
            </div>

            <!-- Configuración de rol y permisos -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rol en DDU</label>
                    <select name="role" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleccionar rol</option>
                        <option value="administrador">Administrador</option>
                        <option value="administracion">Administración</option>
                        <option value="ventas">Ventas</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nivel de permiso</label>
                    <select name="permission" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleccionar permiso</option>
                        <option value="colaborador">Colaborador (Lectura/Escritura)</option>
                        <option value="lector">Lector (Solo lectura)</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t">
                <button type="button" class="btn btn-outline" onclick="hideAddMemberModal()">
                    Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    Agregar Miembro
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Variables globales
let searchTimeout;
let selectedUser = null;

// Modal functions
function showAddMemberModal() {
    document.getElementById('addMemberModal').style.display = 'flex';
    document.getElementById('userSearchInput').focus();
}

function hideAddMemberModal() {
    document.getElementById('addMemberModal').style.display = 'none';
    document.getElementById('addMemberForm').reset();
    clearSelectedUser();
    hideUserSearchResults();
}

function clearSelectedUser() {
    selectedUser = null;
    document.getElementById('selectedUserId').value = '';
    document.getElementById('selectedUserInfo').style.display = 'none';
    document.getElementById('userSearchInput').value = '';
    document.getElementById('userSearchInput').style.display = 'block';
}

function hideUserSearchResults() {
    document.getElementById('userSearchResults').style.display = 'none';
}

function selectUser(user) {
    selectedUser = user;
    document.getElementById('selectedUserId').value = user.id;

    // Mostrar información del usuario seleccionado
    const userDetails = document.getElementById('selectedUserDetails');
    userDetails.innerHTML = `
        <div><strong>Nombre:</strong> ${user.full_name}</div>
        <div><strong>Username:</strong> ${user.username}</div>
        <div><strong>Email:</strong> ${user.email}</div>
        <div><strong>Organización:</strong> ${user.organization}</div>
    `;

    document.getElementById('selectedUserInfo').style.display = 'block';
    document.getElementById('userSearchInput').style.display = 'none';
    hideUserSearchResults();
}

// Búsqueda de usuarios
document.getElementById('userSearchInput').addEventListener('input', function() {
    const search = this.value.trim();

    clearTimeout(searchTimeout);

    if (search.length < 2) {
        hideUserSearchResults();
        return;
    }

    searchTimeout = setTimeout(() => {
        fetch(`{{ route('admin.members.search.users') }}?search=${encodeURIComponent(search)}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                displaySearchResults(data.users);
            })
            .catch(error => {
                console.error('Error searching users:', error);
            });
    }, 300);
});

function displaySearchResults(users) {
    const resultsContainer = document.getElementById('userSearchResults');

    if (users.length === 0) {
        resultsContainer.innerHTML = '<div class="p-3 text-gray-500 text-center">No se encontraron usuarios</div>';
        resultsContainer.style.display = 'block';
        return;
    }

    const resultsHTML = users.map(user => `
        <div class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
             onclick="selectUser(${JSON.stringify(user).replace(/"/g, '&quot;')})">
            <div class="font-medium text-gray-900">${user.full_name}</div>
            <div class="text-sm text-gray-500">${user.username} • ${user.email}</div>
            <div class="text-xs text-gray-400">${user.organization}</div>
        </div>
    `).join('');

    resultsContainer.innerHTML = resultsHTML;
    resultsContainer.style.display = 'block';
}

// Cerrar resultados cuando se hace clic fuera
document.addEventListener('click', function(event) {
    const searchContainer = document.getElementById('userSearchResults');
    const searchInput = document.getElementById('userSearchInput');

    if (!searchContainer.contains(event.target) && !searchInput.contains(event.target)) {
        hideUserSearchResults();
    }
});

// Manejo del formulario
document.getElementById('addMemberForm').addEventListener('submit', function(e) {
    e.preventDefault();

    if (!selectedUser) {
        alert('Por favor selecciona un usuario primero');
        return;
    }

    const formData = new FormData(this);
    const submitButton = this.querySelector('button[type="submit"]');

    submitButton.disabled = true;
    submitButton.textContent = 'Agregando...';

    fetch('{{ route("admin.members.add") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            hideAddMemberModal();
            location.reload(); // Recargar la página para mostrar el nuevo miembro
        } else {
            alert(data.message || 'Error al agregar el miembro');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al agregar el miembro');
    })
    .finally(() => {
        submitButton.disabled = false;
        submitButton.textContent = 'Agregar Miembro';
    });
});

// Funciones de gestión de miembros
function editMember(id) {
    // TODO: Implementar modal de edición
    console.log('Editar miembro', id);
    alert('Función de edición en desarrollo');
}

function toggleMemberStatus(id) {
    if (!confirm('¿Estás seguro de cambiar el estado de este miembro?')) {
        return;
    }

    fetch(`{{ url('admin/members') }}/${id}/toggle-status`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error al cambiar el estado');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cambiar el estado del miembro');
    });
}

function removeMember(id) {
    if (!confirm('¿Estás seguro de eliminar este miembro? Esta acción no se puede deshacer.')) {
        return;
    }

    fetch(`{{ url('admin/members') }}/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error al eliminar el miembro');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al eliminar el miembro');
    });
}

// Filtros en tiempo real (para implementar más adelante)
document.getElementById('searchInput').addEventListener('input', function() {
    // TODO: Implementar filtrado local o con AJAX
    console.log('Buscar en tabla:', this.value);
});

document.getElementById('roleFilter').addEventListener('change', function() {
    // TODO: Implementar filtrado por rol
    console.log('Filtrar por rol:', this.value);
});

document.getElementById('permissionFilter').addEventListener('change', function() {
    // TODO: Implementar filtrado por permiso
    console.log('Filtrar por permiso:', this.value);
});

document.getElementById('statusFilter').addEventListener('change', function() {
    // TODO: Implementar filtrado por estado
    console.log('Filtrar por estado:', this.value);
});
</script>
@endsection
