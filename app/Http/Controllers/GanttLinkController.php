<?php

namespace App\Http\Controllers;

use App\Models\GanttLink;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GanttLinkController extends Controller
{
    /**
     * Store a newly created Gantt link.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'project' => 'required|integer',
            'source' => 'required|integer',
            'target' => 'required|integer',
        ]);

        $link = GanttLink::create([
            'type' => $validated['type'],
            'project_id' => $validated['project'],
            'source' => $validated['source'],
            'target' => $validated['target'],
        ]);

        return response()->json([
            'action' => 'inserted',
            'tid' => $link->id,
        ]);
    }

    /**
     * Update the specified Gantt link.
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'source' => 'required|integer',
            'target' => 'required|integer',
        ]);

        $link = GanttLink::findOrFail($id);
        $link->update($validated);

        return response()->json([
            'action' => 'updated',
        ]);
    }

    /**
     * Remove the specified Gantt link.
     */
    public function destroy(int $id): JsonResponse
    {
        $link = GanttLink::findOrFail($id);
        $link->delete();

        return response()->json([
            'action' => 'deleted',
        ]);
    }

    /**
     * Update a task's start and due dates from Gantt chart interaction.
     */
    public function taskUpdateController(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:tasks,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        Task::where('id', $validated['id'])->update([
            'start_date' => Carbon::parse($validated['start_date']),
            'due_date' => Carbon::parse($validated['end_date'])->subDay(),
        ]);

        return response()->json([
            'action' => 'updated',
        ]);
    }
}
