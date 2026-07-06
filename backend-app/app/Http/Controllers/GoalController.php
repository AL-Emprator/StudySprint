<?php
namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GoalController extends Controller
{
    public function index(Request $request, Group $group): JsonResponse
    {
        $this->ensureMember($request, $group);
        return response()->json($group->goals()->latest()->get());
    }

    public function store(Request $request, Group $group): JsonResponse
    {
        $this->ensureMember($request, $group);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', 'in:open,active,completed'],
        ]);

        return response()->json($group->goals()->create($validated), 201);
    }

    public function update(Request $request, Goal $goal): JsonResponse
    {
        $this->ensureMember($request, $goal->group);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', 'in:open,active,completed'],
        ]);

        $goal->update($validated);
        return response()->json($goal);
    }

    public function destroy(Request $request, Goal $goal): JsonResponse
    {
        $this->ensureMember($request, $goal->group);
        $goal->delete();
        return response()->json(['message' => 'Ziel wurde gelöscht.']);
    }

    private function ensureMember(Request $request, Group $group): void
    {
        if (!$group->users()->where('users.id', $request->user()->id)->exists()) {
            abort(403, 'Kein Zugriff auf diese Gruppe.');
        }
    }
}
