<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::query()->active()->latest();

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tasks = $query->paginate(10)->withQueryString();
        $viewMode = 'index';
        return view('index', compact('tasks', 'viewMode'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $task = Task::create($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Task created',
                'task' => $task,
            ], 201);
        }

        return back()->with('ok', 'Task created');
    }


    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
        $task->update($data);
        return back()->with('ok', 'Task updated');
    }

    public function destroy(Request $request, Task $task)
    {
        $task->delete();
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Task deleted']);
        }
        return back()->with('ok', 'Task deleted');
    }

    public function toggle(Request $request, Task $task)
    {
        $task->is_done = ! $task->is_done;
        $task->completed_at = $task->is_done ? now() : null;
        $task->save();

        return response()->json([
            'message' => 'Status updated',
            'id' => $task->id,
            'is_done' => $task->is_done,
            'completed_at' => optional($task->completed_at)->toDateTimeString(),
        ]);
    }
    public function history(Request $request)
    {
        $query = Task::query()->history()->latest('completed_at');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tasks = $query->paginate(10)->withQueryString();
        $viewMode = 'history';
        return view('history', compact('tasks', 'viewMode'));
    }
    public function clearHistory()
    {
        Task::query()->history()->delete();
        return back()->with('ok', 'History cleared');
    }
}
