<?php
namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    public function index(Request $request, Group $group): JsonResponse
    {
        $this->ensureMember($request, $group);
        return response()->json($group->tasks()->with(['sprint', 'assignedUser'])->latest()->get());
    }

    public function store(Request $request, Group $group): JsonResponse
    {
        $this->ensureMember($request, $group);
        $validated = $request->validate([
            'sprint_id' => ['nullable', 'exists:sprints,id'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', 'in:open,in_progress,done'],
        ]);

        return response()->json($group->tasks()->create($validated), 201);
    }

    public function show(Request $request, Task $task): JsonResponse
    {
        $this->ensureMember($request, $task->group);
        $task->load(['group', 'sprint', 'assignedUser']);
        return response()->json($task);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $this->ensureMember($request, $task->group);
        $validated = $request->validate([
            'sprint_id' => ['nullable', 'exists:sprints,id'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', 'in:open,in_progress,done'],
        ]);

        $task->update($validated);
        $task->load(['sprint', 'assignedUser']);
        return response()->json($task);
    }

    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $this->ensureMember($request, $task->group);
        $validated = $request->validate(['status' => ['required', 'in:open,in_progress,done']]);
        $task->update(['status' => $validated['status']]);
        return response()->json($task);
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        $this->ensureMember($request, $task->group);
        $task->delete();
        return response()->json(['message' => 'Aufgabe wurde gelöscht.']);
    }

    private function ensureMember(Request $request, Group $group): void
    {
        if (!$group->users()->where('users.id', $request->user()->id)->exists()) {
            abort(403, 'Kein Zugriff auf diese Gruppe.');
        }
    }
}
