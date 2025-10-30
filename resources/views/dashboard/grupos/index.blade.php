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
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-white border border-ddu-aqua text-ddu-navy-dark">
                                Grupo activo
                            </span>
                            <button onclick="showDeleteGroupModal({{ $group->id }}, '{{ addslashes($group->name) }}')"
                                    class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors duration-200"
                                    title="Eliminar grupo">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
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
                                                <div class="flex items-center gap-2 ml-3">
                                                    <button
                                                        onclick="showMeetingDetails('{{ $meeting->id }}')"
                                                        class="text-xs px-2 py-1 rounded-full bg-ddu-lavanda/10 text-ddu-lavanda font-semibold hover:bg-ddu-lavanda/20 transition-colors cursor-pointer">
                                                        Ver detalles
                                                    </button>
                                                    @if ($meeting->pivot->shared_by == auth()->id())
                                                        <button
                                                            onclick="showUnshareMeetingModal({{ $meeting->id }}, {{ $group->id }}, '{{ addslashes($meeting->meeting_name) }}', '{{ addslashes($group->name) }}')"
                                                            class="text-xs px-2 py-1 rounded-full bg-red-50 text-red-600 font-semibold hover:bg-red-100 transition-colors"
                                                            title="Dejar de compartir">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </div>
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

    <!-- Modal de confirmación para eliminar grupo -->
    <div id="deleteGroupModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                </div>

                <div class="text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-2" id="deleteGroupTitle">
                        ¿Eliminar grupo?
                    </h3>
                    <div class="text-sm text-gray-500 space-y-2 mb-6">
                        <p class="font-medium">Esta acción:</p>
                        <ul class="text-left space-y-1">
                            <li class="flex items-center">
                                <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                                Eliminará el grupo permanentemente
                            </li>
                            <li class="flex items-center">
                                <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                                Sacará a todos los miembros del grupo
                            </li>
                            <li class="flex items-center">
                                <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                                Quitará el acceso a todas las reuniones compartidas
                            </li>
                            <li class="flex items-center">
                                <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                                No se puede deshacer
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button"
                            onclick="closeDeleteGroupModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-ddu-lavanda">
                        Cancelar
                    </button>
                    <button type="button"
                            onclick="confirmDeleteGroup()"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Eliminar grupo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para dejar de compartir reunión -->
    <div id="unshareMeetingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100">
                        <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L5.732 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                </div>

                <div class="text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        ¿Dejar de compartir reunión?
                    </h3>
                    <p class="text-sm text-gray-500 mb-6" id="unshareMeetingMessage">
                        Los miembros del grupo perderán acceso a esta reunión.
                    </p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button"
                            onclick="closeUnshareMeetingModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-ddu-lavanda">
                        Cancelar
                    </button>
                    <button type="button"
                            onclick="confirmUnshareMeeting()"
                            class="px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        Dejar de compartir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variables globales para los modales
        let currentGroupData = null;
        let currentMeetingData = null;

        function showMeetingDetails(meetingId) {
            // Redirigir a la página de reuniones con el modal abierto para esa reunión específica
            window.location.href = "{{ route('reuniones.index') }}?show=" + meetingId;
        }

        // Funciones para el modal de eliminar grupo
        function showDeleteGroupModal(groupId, groupName) {
            currentGroupData = { id: groupId, name: groupName };

            // Actualizar el título del modal
            document.getElementById('deleteGroupTitle').textContent = `¿Eliminar grupo "${groupName}"?`;

            // Mostrar el modal
            const modal = document.getElementById('deleteGroupModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Bloquear el scroll del body
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteGroupModal() {
            const modal = document.getElementById('deleteGroupModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            // Restaurar el scroll del body
            document.body.style.overflow = '';

            // Limpiar datos
            currentGroupData = null;
        }

        function confirmDeleteGroup() {
            if (!currentGroupData) return;

            // Crear un formulario oculto para enviar la petición DELETE
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/grupos/${currentGroupData.id}`;
            form.style.display = 'none';

            // Token CSRF
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            // Método DELETE
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        }

        // Funciones para el modal de dejar de compartir reunión
        function showUnshareMeetingModal(meetingId, groupId, meetingName, groupName) {
            currentMeetingData = {
                meetingId: meetingId,
                groupId: groupId,
                meetingName: meetingName,
                groupName: groupName
            };

            // Actualizar el mensaje del modal
            document.getElementById('unshareMeetingMessage').textContent =
                `¿Dejar de compartir "${meetingName}" con el grupo "${groupName}"? Los miembros del grupo perderán acceso a esta reunión.`;

            // Mostrar el modal
            const modal = document.getElementById('unshareMeetingModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Bloquear el scroll del body
            document.body.style.overflow = 'hidden';
        }

        function closeUnshareMeetingModal() {
            const modal = document.getElementById('unshareMeetingModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            // Restaurar el scroll del body
            document.body.style.overflow = '';

            // Limpiar datos
            currentMeetingData = null;
        }

        function confirmUnshareMeeting() {
            if (!currentMeetingData) return;

            // Crear un formulario oculto para enviar la petición DELETE
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/grupos/${currentMeetingData.groupId}/meetings/${currentMeetingData.meetingId}`;
            form.style.display = 'none';

            // Token CSRF
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            // Método DELETE
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        }

        // Cerrar modales con la tecla Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeDeleteGroupModal();
                closeUnshareMeetingModal();
            }
        });

        // Cerrar modales haciendo clic fuera de ellos
        document.getElementById('deleteGroupModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeDeleteGroupModal();
            }
        });

        document.getElementById('unshareMeetingModal').addEventListener('click', function(event) {
            if (event.target === this) {
                closeUnshareMeetingModal();
            }
        });
    </script>
@endsection
