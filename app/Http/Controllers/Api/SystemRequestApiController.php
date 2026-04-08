<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemRequestApiController extends Controller
{
    public function index(Request $request)
    {
        $query = SystemRequest::with(['department', 'developer', 'creator']);

        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('priority') && $request->priority != 'all') {
            $query->where('priority', $request->priority);
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $requests
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:pending,in_progress,completed,on_hold,cancelled',
            'department_id' => 'nullable|exists:departments,id',
            'requested_by' => 'nullable|string',
            'reported_at' => 'nullable|date',
            'target_due_date' => 'nullable|date',
            'effort_hours' => 'nullable|numeric',
            'developer_name' => 'nullable|string',
            'developer_id' => 'nullable|exists:users,id',
            'comments' => 'nullable|string',
        ]);

        if (empty($validated['developer_name'])) {
            $validated['developer_name'] = 'Mohammed';
        }

        $validated['created_by'] = Auth::id();

        $systemRequest = SystemRequest::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'System request created successfully',
            'data' => $systemRequest->load(['department', 'developer', 'creator'])
        ]);
    }

    public function show($id)
    {
        $systemRequest = SystemRequest::with(['department', 'developer', 'creator'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $systemRequest
        ]);
    }

    public function update(Request $request, $id)
    {
        $systemRequest = SystemRequest::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'status' => 'sometimes|required|in:pending,in_progress,completed,on_hold,cancelled',
            'department_id' => 'nullable|exists:departments,id',
            'requested_by' => 'nullable|string',
            'reported_at' => 'nullable|date',
            'target_due_date' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'effort_hours' => 'nullable|numeric',
            'developer_name' => 'nullable|string',
            'developer_id' => 'nullable|exists:users,id',
            'comments' => 'nullable|string',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'completed' && !$systemRequest->completed_at) {
            $validated['completed_at'] = now();
        }

        $systemRequest->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'System request updated successfully',
            'data' => $systemRequest->load(['department', 'developer', 'creator'])
        ]);
    }

    public function destroy($id)
    {
        $systemRequest = SystemRequest::findOrFail($id);
        $systemRequest->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'System request deleted successfully'
        ]);
    }
}
