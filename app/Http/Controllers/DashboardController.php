<?php

namespace App\Http\Controllers;

use App\Models\MeetingGroup;
use App\Services\Meetings\DriveMeetingService;
use Illuminate\Support\Facades\Auth;

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
    public function reuniones(DriveMeetingService $driveMeetingService)
    {
        $user = Auth::user();

        [$meetings, $stats, $googleToken] = $driveMeetingService->getOverviewForUser($user);

        $userGroups = MeetingGroup::forUser($user)
            ->withCount('members')
            ->orderBy('name')
            ->get(['id', 'name', 'description']);

        return view('dashboard.reuniones.index', compact('stats', 'meetings', 'googleToken', 'userGroups'));
    }

}
