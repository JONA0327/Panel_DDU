<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserPanelMiembro;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MemberManagementController extends Controller
{
    /**
     * Display the member management page.
     */
    public function index()
    {
        // Obtener estadísticas de miembros (solo los que tienen usuarios válidos)
        $totalMembers = UserPanelMiembro::whereHas('user')->count();
        $activeMembers = UserPanelMiembro::whereHas('user')->where('is_active', true)->count();
        $adminMembers = UserPanelMiembro::whereHas('user')
            ->whereIn('role', ['administrador', 'administracion'])->count(); // Soportar ambos valores
        $collaboratorMembers = UserPanelMiembro::whereHas('user')
            ->whereHas('permission', function ($query) {
                $query->where('name', 'colaborador');
            })->count();

        // Obtener lista de miembros actuales con información del usuario
        $members = UserPanelMiembro::with(['user', 'permission'])
            ->whereHas('user') // Solo miembros con usuarios válidos
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total' => $totalMembers,
            'active' => $activeMembers,
            'admins' => $adminMembers,
            'collaborators' => $collaboratorMembers
        ];

        return view('admin.members.index', compact('members', 'stats'));
    }

    /**
     * Search users by username or email.
     */
    public function searchUsers(Request $request)
    {
        try {
            $search = $request->get('search');

            if (empty($search) || strlen($search) < 2) {
                return response()->json(['users' => []]);
            }

            // Buscar usuarios que no sean miembros DDU todavía
            $existingMemberUserIds = UserPanelMiembro::pluck('user_id');

            $users = User::where(function ($query) use ($search) {
                    $query->where('username', 'LIKE', "%{$search}%")
                          ->orWhere('email', 'LIKE', "%{$search}%")
                          ->orWhere('full_name', 'LIKE', "%{$search}%");
                })
                ->whereNotIn('id', $existingMemberUserIds)
                ->limit(10)
                ->get(['id', 'username', 'full_name', 'email']);

            return response()->json(['users' => $users]);

        } catch (\Exception $e) {
            Log::error('Error en searchUsers: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al buscar usuarios',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add a user as DDU member.
     */
    public function addMember(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:administrador,administracion,ventas',
            'permission' => 'required|in:colaborador,lector'
        ]);

        try {
            DB::beginTransaction();

            // Verificar que el usuario no sea ya miembro
            $existingMember = UserPanelMiembro::where('user_id', $request->user_id)->first();
            if ($existingMember) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este usuario ya es miembro del sistema DDU.'
                ], 422);
            }

            // Obtener el permiso
            $permission = Permission::where('name', $request->permission)->first();
            if (!$permission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permiso no válido.'
                ], 422);
            }

            // Crear el miembro
            $member = UserPanelMiembro::create([
                'panel_id' => 1, // Asumiendo que DDU tiene ID 1
                'user_id' => $request->user_id,
                'role' => $request->role,
                'permission_id' => $permission->id,
                'is_active' => true
            ]);

            DB::commit();

            // Cargar relaciones para la respuesta
            $member->load(['user', 'permission']);

            return response()->json([
                'success' => true,
                'message' => 'Miembro agregado exitosamente.',
                'member' => $member
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Error al agregar el miembro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update member role or permission.
     */
    public function updateMember(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:administrador,administracion,ventas',
            'permission' => 'required|in:colaborador,lector'
        ]);

        try {
            $member = UserPanelMiembro::findOrFail($id);

            // Obtener el permiso
            $permission = Permission::where('name', $request->permission)->first();
            if (!$permission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permiso no válido.'
                ], 422);
            }

            $member->update([
                'role' => $request->role,
                'permission_id' => $permission->id
            ]);

            $member->load(['user', 'permission']);

            return response()->json([
                'success' => true,
                'message' => 'Miembro actualizado exitosamente.',
                'member' => $member
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el miembro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle member status (active/inactive).
     */
    public function toggleStatus($id)
    {
        try {
            $member = UserPanelMiembro::findOrFail($id);
            $member->update(['is_active' => !$member->is_active]);

            $member->load(['user', 'permission']);

            return response()->json([
                'success' => true,
                'message' => $member->is_active ? 'Miembro activado.' : 'Miembro desactivado.',
                'member' => $member
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado del miembro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove member from DDU.
     */
    public function removeMember($id)
    {
        try {
            $member = UserPanelMiembro::findOrFail($id);
            $userName = $member->user->full_name;

            $member->delete();

            return response()->json([
                'success' => true,
                'message' => "Miembro {$userName} eliminado exitosamente."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el miembro: ' . $e->getMessage()
            ], 500);
        }
    }
}
