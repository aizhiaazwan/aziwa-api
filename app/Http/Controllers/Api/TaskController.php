<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * Filter: status, priority, course_id, deadline (today|week|overdue), q
     * Sort: sort = deadline (default) | priority | newest
     */
    public function index(Request $request)
    {
        $q = $request->user()->tasks();

        if ($v = $request->query('status')) $q->where('status', $v);
        if ($v = $request->query('priority')) $q->where('priority', $v);
        if ($v = $request->query('course_id')) $q->where('course_id', $v);

        if ($s = trim((string) $request->query('q', ''))) {
            $q->where(fn ($w) => $w->where('title', 'like', "%{$s}%")
                ->orWhereHas('course', fn ($c) => $c->where('name', 'like', "%{$s}%")));
        }

        match ($request->query('deadline')) {
            'today' => $q->whereDate('deadline', now()->toDateString()),
            'week' => $q->where('deadline', '<=', now()->addDays(7)),
            'overdue' => $q->where('deadline', '<', now())->where('status', '!=', 'completed'),
            default => null,
        };

        // Tugas selesai selalu di bawah, kecuali diurutkan "terbaru"
        match ($request->query('sort')) {
            'priority' => $q->orderByRaw("status = 'completed'")
                ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")->orderBy('deadline'),
            'newest' => $q->orderByDesc('id'),
            default => $q->orderByRaw("status = 'completed'")->orderBy('deadline'),
        };

        return $this->paged($q->paginate($this->perPage($request)), TaskResource::class);
    }

    public function store(Request $request)
    {
        $data = $this->prepare($request->validate($this->rules($request)));
        $task = $request->user()->tasks()->create($data);

        return $this->ok(new TaskResource($task), 'Task created successfully', 201);
    }

    public function show(Request $request, string $id)
    {
        return $this->ok(new TaskResource($request->user()->tasks()->findOrFail($id)));
    }

    public function update(Request $request, string $id)
    {
        $task = $request->user()->tasks()->findOrFail($id);
        $data = $this->prepare($request->validate($this->rules($request)), $task);
        $task->update($data);

        return $this->ok(new TaskResource($task->fresh()), 'Task updated successfully');
    }

    public function destroy(Request $request, string $id)
    {
        $request->user()->tasks()->findOrFail($id)->delete();

        return $this->ok(null, 'Task deleted successfully');
    }

    private function rules(Request $request): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            // course_id harus milik user yang sedang login
            'course_id' => [
                'nullable', 'integer',
                Rule::exists('courses', 'id')->where('user_id', $request->user()->id),
            ],
            'deadline' => ['required', 'date'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
            'subtasks_total' => ['nullable', 'integer', 'min:0', 'max:100'],
            'subtasks_done' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }

    private function prepare(array $data, ?Task $task = null): array
    {
        // Simpan sebagai WIB apa pun zona waktu yang dikirim klien
        $data['deadline'] = Carbon::parse($data['deadline'])->setTimezone(config('app.timezone'));

        $total = $data['subtasks_total'] ?? 0;
        $data['subtasks_total'] = $total;
        $data['subtasks_done'] = min($data['subtasks_done'] ?? 0, $total);

        $data['completed_at'] = $data['status'] === 'completed'
            ? ($task?->completed_at ?? now())
            : null;

        return $data;
    }
}