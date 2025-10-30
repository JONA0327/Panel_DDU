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
                'meetings:id,meeting_name,status'
            ])
            ->orderBy('name')
            ->get();

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
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $member = User::where('email', $validated['email'])->firstOrFail();

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

        $group->meetings()->syncWithoutDetaching([$meeting->id]);
        $meeting->load('groups:id,name');

        return response()->json([
            'message' => 'Reunión añadida al grupo correctamente.',
            'meeting_groups' => $meeting->groups->map(fn ($group) => [
                'id' => $group->id,
                'name' => $group->name,
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
}
