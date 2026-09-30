<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AgendaResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgendaController extends Controller
{
    /** Filter: date=YYYY-MM-DD, from=YYYY-MM-DD (mendatang), category */
    public function index(Request $request)
    {
        $q = $request->user()->agendas();

        if ($v = $request->query('date')) $q->whereDate('date', $v);
        if ($v = $request->query('from')) $q->whereDate('date', '>=', $v);
        if ($v = $request->query('category')) $q->where('category', $v);

        $q->orderBy('date')->orderBy('start_time');

        return $this->paged($q->paginate($this->perPage($request)), AgendaResource::class);
    }

    public function store(Request $request)
    {
        $agenda = $request->user()->agendas()->create($request->validate($this->rules()));

        return $this->ok(new AgendaResource($agenda), 'Agenda ditambahkan', 201);
    }

    public function show(Request $request, string $id)
    {
        return $this->ok(new AgendaResource($request->user()->agendas()->findOrFail($id)));
    }

    public function update(Request $request, string $id)
    {
        $agenda = $request->user()->agendas()->findOrFail($id);
        $agenda->update($request->validate($this->rules()));

        return $this->ok(new AgendaResource($agenda->fresh()), 'Agenda diperbarui');
    }

    public function destroy(Request $request, string $id)
    {
        // Pengingat terkait ikut terhapus (cascade di database)
        $request->user()->agendas()->findOrFail($id)->delete();

        return $this->ok(null, 'Agenda dihapus');
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'category' => ['required', Rule::in(['kuliah', 'organisasi', 'pkm', 'belajar', 'olahraga', 'acara', 'pribadi'])],
            'reminder' => ['required', 'boolean'],
        ];
    }
}