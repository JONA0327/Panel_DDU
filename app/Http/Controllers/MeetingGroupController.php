<?php

namespace App\Http\Controllers;

use App\Models\MeetingGroup;
use App\Models\MeetingTranscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MeetingGroupController extends Controller
{
    /**
     * Display the group management page.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $groups = MeetingGroup::query()
            ->forUser($user)
            ->with([
                'members' => function ($query) {
                    $query->select('users.id', 'users.full_name', 'users.username', 'users.email');
                },
                'meetings' => function ($query) {
                    $query->select('transcriptions_laravel.id', 'transcriptions_laravel.meeting_name')
                          ->withPivot('shared_by', 'created_at');
                },
                'meetings.groups.owner:id,full_name,username'
            ])
            ->orderBy('name')
            ->get();

        // Cargar información de los usuarios que compartieron las reuniones
        $sharedByUserIds = $groups->flatMap(function ($group) {
            return $group->meetings->pluck('pivot.shared_by');
        })->filter()->unique();

        $sharedByUsers = User::whereIn('id', $sharedByUserIds)
            ->select('id', 'full_name', 'username')
            ->get()
            ->keyBy('id');

        // Agregar información del usuario que compartió a cada reunión
        $groups->each(function ($group) use ($sharedByUsers) {
            $group->meetings->each(function ($meeting) use ($sharedByUsers) {
                if ($meeting->pivot->shared_by && isset($sharedByUsers[$meeting->pivot->shared_by])) {
                    $meeting->shared_by_user = $sharedByUsers[$meeting->pivot->shared_by];
                }
            });
        });

        return view('dashboard.grupos.index', [
            'groups' => $groups,
        ]);
    }

    /**
     * Store a new meeting group.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($user, $validated) {
            $group = MeetingGroup::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'owner_id' => $user->id,
            ]);

            $group->members()->syncWithoutDetaching([$user->id]);
        });

        return redirect()
            ->route('grupos.index')
            ->with('status', 'Grupo creado correctamente.');
    }

    /**
     * Add a new member to the group.
     */
    public function storeMember(Request $request, MeetingGroup $group): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeGroupManagement($group, $user);

        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Buscar usuario por email
        $member = User::where('email', $validated['email'])->first();

        if (!$member) {
            return back()->withErrors(['email' => 'No existe un usuario con este email.']);
        }

        // Validar que el usuario pertenezca a la organización DDU
        $isDDUMember = \App\Models\UserPanelMiembro::where('user_id', $member->id)
            ->where('is_active', true)
            ->exists();

        if (!$isDDUMember) {
            return back()->withErrors(['email' => 'El usuario debe pertenecer a la organización DDU para ser invitado.']);
        }

        if ($group->members()->where('users.id', $member->id)->exists()) {
            return back()->with('status', 'El usuario ya pertenece a este grupo.');
        }

        $group->members()->attach($member->id);

        return back()->with('status', 'Usuario añadido correctamente al grupo.');
    }

    /**
     * Attach a meeting to a group so members can access it.
     */
    public function attachMeeting(Request $request, MeetingTranscription $meeting): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'group_id' => ['required', 'exists:meeting_groups,id'],
        ]);

        $group = MeetingGroup::forUser($user)->findOrFail($validated['group_id']);

        if (! $this->canManageMeeting($meeting, $user)) {
            return response()->json([
                'message' => 'No tienes permisos para compartir esta reunión.',
            ], 403);
        }

        // Verificar si la reunión ya está compartida con el grupo
        $existingPivot = $group->meetings()->where('meeting_id', $meeting->id)->first();

        if (!$existingPivot) {
            // Agregar la reunión al grupo con información de quién la compartió
            $group->meetings()->attach($meeting->id, ['shared_by' => $user->id]);
        }

        $meeting->load(['groups' => function($query) {
            $query->select('meeting_groups.id', 'meeting_groups.name')
                  ->withPivot('shared_by', 'created_at');
        }, 'groups.owner:id,full_name,username']);

        return response()->json([
            'message' => 'Reunión añadida al grupo correctamente.',
            'meeting_groups' => $meeting->groups->map(fn ($group) => [
                'id' => $group->id,
                'name' => $group->name,
                'shared_by' => $group->pivot->shared_by,
                'shared_at' => $group->pivot->created_at,
            ])->values(),
        ]);
    }

    /**
     * Ensure the authenticated user can manage the given group.
     */
    protected function authorizeGroupManagement(MeetingGroup $group, User $user): void
    {
        $isOwner = $group->owner_id === $user->id;
        $isAdmin = $user->panelMemberships()->where('role', 'administrador')->exists();

        if (! $isOwner && ! $isAdmin) {
            abort(403, 'No tienes permisos para gestionar este grupo.');
        }
    }

    /**
     * Determine if the user can manage sharing for the meeting.
     */
    protected function canManageMeeting(MeetingTranscription $meeting, User $user): bool
    {
        if ($meeting->user_id === $user->id || $meeting->username === $user->username) {
            return true;
        }

        return $meeting->groups()
            ->whereHas('members', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->exists();
    }

    /**
     * Delete a group and remove all associated data.
     */
    public function destroy(MeetingGroup $group): RedirectResponse
    {
        $user = auth()->user();

        // Verificar que el usuario es el propietario del grupo
        if ($group->owner_id !== $user->id) {
            return redirect()->back()->withErrors(['error' => 'No tienes permisos para eliminar este grupo.']);
        }

        DB::beginTransaction();
        
        try {
            // Obtener el nombre del grupo antes de eliminarlo
            $groupName = $group->name;
            $membersCount = $group->members->count();
            $meetingsCount = $group->meetings->count();

            // Eliminar las relaciones con reuniones (meeting_group_meeting)
            $group->meetings()->detach();

            // Eliminar las relaciones con miembros (meeting_group_user)
            $group->members()->detach();

            // Eliminar el grupo
            $group->delete();

            DB::commit();

            return redirect()->route('grupos.index')->with('status', 
                "El grupo \"{$groupName}\" ha sido eliminado correctamente. Se removieron {$membersCount} miembros y se dejaron de compartir {$meetingsCount} reuniones."
            );

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al eliminar el grupo. Inténtalo de nuevo.']);
        }
    }

    /**
     * Detach a meeting from a group (stop sharing).
     */
    public function detachMeeting(MeetingGroup $group, MeetingTranscription $meeting): RedirectResponse
    {
        $user = auth()->user();

        // Verificar que el usuario puede gestionar esta reunión
        if (!$this->canManageMeeting($meeting, $user)) {
            return redirect()->back()->withErrors(['error' => 'No tienes permisos para dejar de compartir esta reunión.']);
        }

        // Verificar que la reunión esté compartida en el grupo
        if (!$group->meetings()->where('transcriptions_laravel.id', $meeting->id)->exists()) {
            return redirect()->back()->withErrors(['error' => 'Esta reunión no está compartida en el grupo especificado.']);
        }

        // Verificar que el usuario que trata de dejar de compartir sea quien la compartió originalmente
        $pivot = $group->meetings()->where('transcriptions_laravel.id', $meeting->id)->first();
        if ($pivot && $pivot->pivot->shared_by !== $user->id) {
            return redirect()->back()->withErrors(['error' => 'Solo quien compartió la reunión puede dejar de compartirla.']);
        }

        try {
            // Quitar la reunión del grupo
            $group->meetings()->detach($meeting->id);

            return redirect()->back()->with('status', 
                "La reunión \"{$meeting->meeting_name}\" ya no se comparte con el grupo \"{$group->name}\"."
            );

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al dejar de compartir la reunión. Inténtalo de nuevo.']);
        }
    }
}
