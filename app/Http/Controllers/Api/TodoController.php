<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TodoController extends Controller
{
    /** Filter: status */
    public function index(Request $request)
    {
        $q = $request->user()->todos();

        if ($v = $request->query('status')) $q->where('status', $v);

        $q->orderByRaw("status = 'completed'")
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->orderByRaw('deadline is null')
            ->orderBy('deadline');

        return $this->paged($q->paginate($this->perPage($request)));
    }

    public function store(Request $request)
    {
        $todo = $request->user()->todos()->create($request->validate($this->rules()));

        return $this->ok($todo->fresh(), 'To-do ditambahkan', 201);
    }

    public function show(Request $request, string $id)
    {
        return $this->ok($request->user()->todos()->findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $todo = $request->user()->todos()->findOrFail($id);
        $todo->update($request->validate($this->rules()));

        return $this->ok($todo->fresh(), 'To-do diperbarui');
    }

    public function destroy(Request $request, string $id)
    {
        $request->user()->todos()->findOrFail($id)->delete();

        return $this->ok(null, 'To-do dihapus');
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'deadline' => ['nullable', 'date_format:Y-m-d'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'status' => ['required', Rule::in(['pending', 'in_progress', 'completed'])],
        ];
    }
}