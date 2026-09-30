<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->user()->courses()
            ->withCount([
                'tasks',
                'tasks as active_tasks_count' => fn ($t) => $t->where('status', '!=', 'completed'),
            ]);

        if ($s = trim((string) $request->query('q', ''))) {
            $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")
                ->orWhere('code', 'like', "%{$s}%")
                ->orWhere('lecturer', 'like', "%{$s}%"));
        }

        $q->orderBy('day')->orderBy('start_time');

        return $this->paged($q->paginate($this->perPage($request)), CourseResource::class);
    }

    public function store(Request $request)
    {
        $course = $request->user()->courses()->create($request->validate($this->rules($request)));

        return $this->ok(new CourseResource($course), 'Mata kuliah ditambahkan', 201);
    }

    public function show(Request $request, string $id)
    {
        $course = $request->user()->courses()->withCount('tasks')->findOrFail($id);

        return $this->ok(new CourseResource($course));
    }

    public function update(Request $request, string $id)
    {
        $course = $request->user()->courses()->findOrFail($id);
        $course->update($request->validate($this->rules($request, $course->id)));

        return $this->ok(new CourseResource($course->fresh()), 'Mata kuliah diperbarui');
    }

    public function destroy(Request $request, string $id)
    {
        // Tugas terkait ikut terhapus (cascade di database)
        $request->user()->courses()->findOrFail($id)->delete();

        return $this->ok(null, 'Mata kuliah dihapus');
    }

    private function rules(Request $request, ?int $ignoreId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => [
                'required', 'string', 'max:20',
                Rule::unique('courses', 'code')->where('user_id', $request->user()->id)->ignore($ignoreId),
            ],
            'sks' => ['required', 'integer', 'between:1,6'],
            'lecturer' => ['nullable', 'string', 'max:100'],
            'day' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'room' => ['nullable', 'string', 'max:50'],
            'semester' => ['nullable', 'integer', 'between:1,14'],
            'description' => ['nullable', 'string', 'max:2000'],
            'icon' => ['sometimes', 'string', 'max:30'],
            'tone' => ['sometimes', Rule::in(['primary', 'accent'])],
            'stripe' => ['sometimes', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }
}