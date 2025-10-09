<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        // Verificar que el usuario esté autenticado
        $user = auth()->user();

        // Para simplificar, asignar rol por defecto
        // TODO: Implementar lógica de roles después de que las relaciones estén configuradas
        $userRole = 'administrador'; // Por ahora todos son admin para probar

        // Obtener estadísticas básicas para el dashboard
        $stats = [
            'total_members' => 12, // Datos de ejemplo
            'recent_meetings' => 3, // Datos de ejemplo
            'pending_tasks' => 7,   // Datos de ejemplo
            'user_role' => $userRole
        ];

        return view('dashboard.index', compact('stats'));
    }

    /**
     * Reuniones index
     */
    public function reuniones()
    {
        // Estadísticas de ejemplo para reuniones
        $stats = [
            'total' => 15,
            'programadas' => 3,
            'finalizadas' => 10,
            'esta_semana' => 2
        ];

        return view('dashboard.reuniones.index', compact('stats'));
    }

    /**
     * Asistente index
     */
    public function asistente()
    {
        // Estadísticas para el asistente
        $stats = [
            'total_members' => \App\Models\UserPanelMiembro::count(),
        ];

        return view('dashboard.asistente.index', compact('stats'));
    }
}
