@extends('layouts.dashboard')

@section('page-title', 'Asistente DDU')
@section('page-description', 'Tu asistente inteligente para optimizar el trabajo')

@section('content')
<div class="space-y-6 fade-in">
    <!-- Header del Asistente -->
    <div class="text-center">
        <div class="w-20 h-20 bg-gradient-to-r from-ddu-lavanda to-ddu-aqua rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
            </svg>
        </div>
        <h2 class="text-3xl font-bold text-gray-900 mb-2">Asistente DDU</h2>
        <p class="text-lg text-gray-600">Tu asistente inteligente para optimizar el trabajo en equipo</p>
    </div>

    <!-- Chat del Asistente -->
    <div class="max-w-4xl mx-auto">
        <div class="ddu-card">
            <!-- Área de conversación -->
            <div id="chatArea" class="h-96 overflow-y-auto p-6 space-y-4">
                <!-- Mensaje de bienvenida -->
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-r from-ddu-lavanda to-ddu-aqua rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <div class="bg-gray-100 rounded-lg p-4">
                            <p class="text-gray-800">
                                ¡Hola! Soy tu asistente DDU. Puedo ayudarte con:
                            </p>
                            <ul class="mt-2 text-sm text-gray-700 space-y-1">
                                <li>• Programar y gestionar reuniones</li>
                                <li>• Buscar información de miembros</li>
                                <li>• Generar reportes y estadísticas</li>
                                <li>• Responder preguntas sobre el sistema</li>
                                <li>• Optimizar tareas y procesos</li>
                            </ul>
                            <p class="mt-3 text-gray-800">¿En qué puedo ayudarte hoy?</p>
                        </div>
                        <span class="text-xs text-gray-500 mt-1 block">Asistente DDU • hace unos segundos</span>
                    </div>
                </div>
            </div>

            <!-- Barra de entrada de chat -->
            <div class="border-t p-4">
                <div class="flex space-x-4">
                    <div class="flex-1">
                        <input type="text"
                               id="chatInput"
                               placeholder="Escribe tu consulta aquí..."
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ddu-lavanda focus:border-ddu-lavanda"
                               onkeypress="handleEnterKey(event)">
                    </div>
                    <button id="sendButton"
                            onclick="sendMessage()"
                            class="btn btn-primary px-6">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </button>
                </div>

                <!-- Sugerencias rápidas -->
                <div class="mt-4">
                    <p class="text-sm font-medium text-gray-700 mb-2">Consultas sugeridas:</p>
                    <div class="flex flex-wrap gap-2">
                        <button class="suggestion-btn" onclick="setSuggestion('¿Cuántas reuniones tengo esta semana?')">
                            📅 Reuniones de esta semana
                        </button>
                        <button class="suggestion-btn" onclick="setSuggestion('¿Quiénes son los miembros activos?')">
                            👥 Miembros activos
                        </button>
                        <button class="suggestion-btn" onclick="setSuggestion('Crear una reunión para mañana')">
                            ➕ Crear reunión
                        </button>
                        <button class="suggestion-btn" onclick="setSuggestion('Generar reporte de actividades')">
                            📊 Generar reporte
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Funcionalidades del Asistente -->
    <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Programar Reunión -->
        <div class="ddu-card hover:shadow-lg transition-shadow cursor-pointer" onclick="setSuggestion('Programa una reunión para el equipo')">
            <div class="p-6">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Programar Reunión</h3>
                <p class="text-gray-600 text-sm">Agenda reuniones rápidamente con el equipo</p>
            </div>
        </div>

        <!-- Buscar Miembros -->
        <div class="ddu-card hover:shadow-lg transition-shadow cursor-pointer" onclick="setSuggestion('Busca información sobre los miembros del equipo')">
            <div class="p-6">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Buscar Miembros</h3>
                <p class="text-gray-600 text-sm">Encuentra información de contacto y roles</p>
            </div>
        </div>

        <!-- Generar Reportes -->
        <div class="ddu-card hover:shadow-lg transition-shadow cursor-pointer" onclick="setSuggestion('Genera un reporte de actividades del mes')">
            <div class="p-6">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Generar Reportes</h3>
                <p class="text-gray-600 text-sm">Obtén estadísticas y reportes detallados</p>
            </div>
        </div>

        <!-- Gestionar Tareas -->
        <div class="ddu-card hover:shadow-lg transition-shadow cursor-pointer" onclick="setSuggestion('¿Qué tareas tengo pendientes?')">
            <div class="p-6">
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Gestionar Tareas</h3>
                <p class="text-gray-600 text-sm">Organiza y da seguimiento a tareas</p>
            </div>
        </div>

        <!-- Ayuda Sistema -->
        <div class="ddu-card hover:shadow-lg transition-shadow cursor-pointer" onclick="setSuggestion('¿Cómo funciona el sistema DDU?')">
            <div class="p-6">
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Ayuda del Sistema</h3>
                <p class="text-gray-600 text-sm">Obtén ayuda sobre funcionalidades</p>
            </div>
        </div>

        <!-- Configuración -->
        <div class="ddu-card hover:shadow-lg transition-shadow cursor-pointer" onclick="setSuggestion('¿Cómo puedo personalizar mi perfil?')">
            <div class="p-6">
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Configuración</h3>
                <p class="text-gray-600 text-sm">Personaliza tu experiencia DDU</p>
            </div>
        </div>
    </div>

    <!-- Indicador de escritura -->
    <div id="typingIndicator" class="max-w-4xl mx-auto" style="display: none;">
        <div class="ddu-card">
            <div class="p-4">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-r from-ddu-lavanda to-ddu-aqua rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <div class="flex space-x-1">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                    <span class="text-gray-500 text-sm">El asistente está escribiendo...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let chatHistory = [];

function handleEnterKey(event) {
    if (event.key === 'Enter') {
        sendMessage();
    }
}

function setSuggestion(message) {
    document.getElementById('chatInput').value = message;
    document.getElementById('chatInput').focus();
}

function sendMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();

    if (!message) return;

    // Agregar mensaje del usuario al chat
    addMessageToChat('user', message);

    // Limpiar input
    input.value = '';

    // Mostrar indicador de escritura
    showTypingIndicator();

    // Simular respuesta del asistente (aquí se conectaría con IA real)
    setTimeout(() => {
        hideTypingIndicator();
        generateAssistantResponse(message);
    }, 1500 + Math.random() * 2000); // Simular tiempo de procesamiento variable
}

function addMessageToChat(sender, message) {
    const chatArea = document.getElementById('chatArea');
    const messageDiv = document.createElement('div');

    if (sender === 'user') {
        messageDiv.innerHTML = `
            <div class="flex items-start space-x-3 justify-end">
                <div class="flex-1 max-w-xs lg:max-w-md">
                    <div class="bg-gradient-to-r from-ddu-lavanda to-ddu-aqua text-white rounded-lg p-4">
                        <p>${message}</p>
                    </div>
                    <span class="text-xs text-gray-500 mt-1 block text-right">Tú • ahora</span>
                </div>
                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>
        `;
    } else {
        messageDiv.innerHTML = `
            <div class="flex items-start space-x-3">
                <div class="w-8 h-8 bg-gradient-to-r from-ddu-lavanda to-ddu-aqua rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="bg-gray-100 rounded-lg p-4">
                        <p class="text-gray-800">${message}</p>
                    </div>
                    <span class="text-xs text-gray-500 mt-1 block">Asistente DDU • ahora</span>
                </div>
            </div>
        `;
    }

    chatArea.appendChild(messageDiv);
    chatArea.scrollTop = chatArea.scrollHeight;
}

function showTypingIndicator() {
    document.getElementById('typingIndicator').style.display = 'block';
}

function hideTypingIndicator() {
    document.getElementById('typingIndicator').style.display = 'none';
}

function generateAssistantResponse(userMessage) {
    const message = userMessage.toLowerCase();
    let response;

    // Respuestas basadas en el contenido del mensaje
    if (message.includes('reunión') || message.includes('programar')) {
        response = "Te puedo ayudar a programar una reunión. Para esto necesitaré algunos datos:\n\n• ¿Cuál es el tema de la reunión?\n• ¿Para qué fecha la necesitas?\n• ¿Qué duración aproximada?\n• ¿Quiénes deberían participar?\n\n¿Podrías proporcionarme estos detalles?";
    } else if (message.includes('miembros') || message.includes('equipo')) {
        response = "Actualmente hay {{ $stats['total_members'] ?? '0' }} miembros activos en el sistema DDU. Puedo ayudarte a:\n\n• Buscar información específica de un miembro\n• Mostrar la lista completa de miembros\n• Filtrar por roles (administrador, colaborador, lector)\n• Ver estadísticas del equipo\n\n¿Qué información específica necesitas?";
    } else if (message.includes('reporte') || message.includes('estadística')) {
        response = "Puedo generar varios tipos de reportes para ti:\n\n📊 **Reportes disponibles:**\n• Actividad de reuniones (semanal/mensual)\n• Participación de miembros\n• Estadísticas generales del sistema\n• Reporte de tareas y seguimientos\n\n¿Qué tipo de reporte te interesa generar?";
    } else if (message.includes('semana') && message.includes('reunión')) {
        response = "Esta semana tienes {{ $stats['esta_semana'] ?? '0' }} reuniones programadas:\n\n📅 **Próximas reuniones:**\n• Reunión semanal de equipo - Lunes 10:00 AM\n• Revisión de proyectos - Miércoles 2:00 PM\n\n¿Te gustaría ver más detalles de alguna reunión específica?";
    } else if (message.includes('sistema') || message.includes('ayuda')) {
        response = "El sistema DDU te permite:\n\n🎯 **Funcionalidades principales:**\n• Gestionar reuniones y participantes\n• Administrar miembros y permisos\n• Generar reportes y estadísticas\n• Usar este asistente inteligente\n\n💡 **Consejos:**\n• Usa el sidebar para navegar entre secciones\n• Los administradores tienen acceso completo\n• Puedes filtrar y buscar en todas las listas\n\n¿Hay alguna función específica que quieras conocer mejor?";
    } else if (message.includes('perfil') || message.includes('personalizar')) {
        response = "Para personalizar tu perfil puedes:\n\n⚙️ **Opciones de personalización:**\n• Actualizar tu información personal\n• Cambiar tu contraseña\n• Configurar notificaciones\n• Establecer preferencias de idioma\n\nPuedes acceder a estas opciones desde el menú de usuario en la esquina superior derecha. ¿Necesitas ayuda con alguna configuración específica?";
    } else {
        // Respuesta genérica inteligente
        const genericResponses = [
            "Entiendo tu consulta. ¿Podrías ser más específico sobre lo que necesitas? Puedo ayudarte con reuniones, miembros, reportes y configuración del sistema.",
            "Estoy aquí para ayudarte. Mi especialidad es gestionar actividades DDU como reuniones, miembros y generar reportes. ¿En qué área específica puedo asistirte?",
            "Gracias por tu consulta. Para brindarte la mejor ayuda, ¿podrías especificar si necesitas ayuda con reuniones, gestión de miembros, reportes o alguna otra funcionalidad del sistema?",
        ];
        response = genericResponses[Math.floor(Math.random() * genericResponses.length)];
    }

    // Agregar la respuesta al chat
    addMessageToChat('assistant', response);

    // Guardar en historial (para futuras implementaciones de contexto)
    chatHistory.push({
        user: userMessage,
        assistant: response,
        timestamp: new Date()
    });
}

// Estilo para botones de sugerencia
const style = document.createElement('style');
style.textContent = `
    .suggestion-btn {
        @apply px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition-colors cursor-pointer border border-gray-200;
    }
    .suggestion-btn:hover {
        @apply bg-ddu-lavanda bg-opacity-10 border-ddu-lavanda text-ddu-lavanda;
    }
    .modal {
        @apply fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50;
    }
`;
document.head.appendChild(style);
</script>
@endsection
