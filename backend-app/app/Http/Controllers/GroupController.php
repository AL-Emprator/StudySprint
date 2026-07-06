<?php
namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GroupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $groups = $request->user()->groups()->with('owner')->get();
        return response()->json($groups);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $group = Group::create([
            'owner_id' => $request->user()->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $group->users()->attach($request->user()->id, ['role' => 'owner']);
        $group->load(['owner', 'users']);

        return response()->json($group, 201);
    }

    public function show(Request $request, Group $group): JsonResponse
    {
        $this->ensureMember($request, $group);
        $group->load(['owner', 'users', 'goals', 'sprints', 'tasks', 'meetings']);
        return response()->json($group);
    }

    public function update(Request $request, Group $group): JsonResponse
    {
        $this->ensureOwner($request, $group);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $group->update($validated);
        return response()->json($group);
    }

    public function destroy(Request $request, Group $group): JsonResponse
    {
        $this->ensureOwner($request, $group);
        $group->delete();
        return response()->json(['message' => 'Gruppe wurde gelöscht.']);
    }

    public function addMember(Request $request, Group $group): JsonResponse
    {
        $this->ensureOwner($request, $group);
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'role' => ['required', 'in:owner,member'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'Benutzer wurde nicht gefunden.'], 404);
        }

        $alreadyMember = $group->users()->where('users.id', $user->id)->exists();

        if ($alreadyMember) {
            return response()->json(['message' => 'Benutzer ist bereits Mitglied dieser Gruppe.'], 422);
        }

        $group->users()->attach($user->id, ['role' => $validated['role']]);

        return response()->json(['message' => 'Mitglied wurde erfolgreich hinzugefügt.'], 201);
    }

    public function removeMember(Request $request, Group $group, User $user): JsonResponse
    {
        $this->ensureOwner($request, $group);

        if ($user->id === $group->owner_id) {
            return response()->json(['message' => 'Der Gruppenbesitzer kann nicht entfernt werden.'], 422);
        }

        $isMember = $group->users()->where('users.id', $user->id)->exists();

        if (!$isMember) {
            return response()->json(['message' => 'Benutzer ist kein Mitglied dieser Gruppe.'], 404);
        }

        $group->users()->detach($user->id);
        return response()->json(['message' => 'Mitglied wurde erfolgreich entfernt.']);
    }

    private function ensureMember(Request $request, Group $group): void
    {
        $isMember = $group->users()->where('users.id', $request->user()->id)->exists();

        if (!$isMember) {
            abort(403, 'Kein Zugriff auf diese Gruppe.');
        }
    }

    private function ensureOwner(Request $request, Group $group): void
    {
        if ($group->owner_id !== $request->user()->id) {
            abort(403, 'Nur der Gruppenbesitzer darf diese Aktion ausführen.');
        }
    }
}
