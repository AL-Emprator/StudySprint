<?php
namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MeetingController extends Controller
{
    public function index(Request $request, Group $group): JsonResponse
    {
        $this->ensureMember($request, $group);
        return response()->json($group->meetings()->orderBy('meeting_date')->orderBy('meeting_time')->get());
    }

    public function store(Request $request, Group $group): JsonResponse
    {
        $this->ensureMember($request, $group);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'meeting_date' => ['required', 'date'],
            'meeting_time' => ['required'],
            'location_or_link' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        return response()->json($group->meetings()->create($validated), 201);
    }

    public function show(Request $request, Meeting $meeting): JsonResponse
    {
        $this->ensureMember($request, $meeting->group);
        return response()->json($meeting);
    }

    public function update(Request $request, Meeting $meeting): JsonResponse
    {
        $this->ensureMember($request, $meeting->group);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'meeting_date' => ['required', 'date'],
            'meeting_time' => ['required'],
            'location_or_link' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $meeting->update($validated);
        return response()->json($meeting);
    }

    public function destroy(Request $request, Meeting $meeting): JsonResponse
    {
        $this->ensureMember($request, $meeting->group);
        $meeting->delete();
        return response()->json(['message' => 'Termin wurde gelöscht.']);
    }

    private function ensureMember(Request $request, Group $group): void
    {
        if (!$group->users()->where('users.id', $request->user()->id)->exists()) {
            abort(403, 'Kein Zugriff auf diese Gruppe.');
        }
    }
}
