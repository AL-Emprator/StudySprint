<?php
namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Sprint;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SprintController extends Controller
{
    public function index(Request $request, Group $group): JsonResponse
    {
        $this->ensureMember($request, $group);
        return response()->json($group->sprints()->with('tasks')->latest()->get());
    }

    public function store(Request $request, Group $group): JsonResponse
    {
        $this->ensureMember($request, $group);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:planned,active,finished'],
        ]);

        return response()->json($group->sprints()->create($validated), 201);
    }

    public function show(Request $request, Sprint $sprint): JsonResponse
    {
        $this->ensureMember($request, $sprint->group);
        $sprint->load('tasks');
        return response()->json($sprint);
    }

    public function update(Request $request, Sprint $sprint): JsonResponse
    {
        $this->ensureMember($request, $sprint->group);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:planned,active,finished'],
        ]);

        $sprint->update($validated);
        return response()->json($sprint);
    }

    public function destroy(Request $request, Sprint $sprint): JsonResponse
    {
        $this->ensureMember($request, $sprint->group);
        $sprint->delete();
        return response()->json(['message' => 'Sprint wurde gelöscht.']);
    }

    private function ensureMember(Request $request, Group $group): void
    {
        if (!$group->users()->where('users.id', $request->user()->id)->exists()) {
            abort(403, 'Kein Zugriff auf diese Gruppe.');
        }
    }
}
