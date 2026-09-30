<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NoteController extends Controller
{
    /** Filter: tag, q (judul atau isi) */
    public function index(Request $request)
    {
        $q = $request->user()->notes();

        if ($v = $request->query('tag')) $q->where('tag', $v);

        if ($s = trim((string) $request->query('q', ''))) {
            $q->where(fn ($w) => $w->where('title', 'like', "%{$s}%")->orWhere('content', 'like', "%{$s}%"));
        }

        $q->orderByDesc('date')->orderByDesc('id');

        return $this->paged($q->paginate($this->perPage($request)));
    }

    public function store(Request $request)
    {
        $note = $request->user()->notes()->create($request->validate($this->rules()));

        return $this->ok($note->fresh(), 'Catatan disimpan', 201);
    }

    public function show(Request $request, string $id)
    {
        return $this->ok($request->user()->notes()->findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $note = $request->user()->notes()->findOrFail($id);
        $note->update($request->validate($this->rules()));

        return $this->ok($note->fresh(), 'Catatan diperbarui');
    }

    public function destroy(Request $request, string $id)
    {
        $request->user()->notes()->findOrFail($id)->delete();

        return $this->ok(null, 'Catatan dihapus');
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'content' => ['nullable', 'string', 'max:50000'],
            'date' => ['required', 'date_format:Y-m-d'],
            'tag' => ['required', Rule::in(['kuliah', 'project', 'ide', 'pkm', 'pribadi'])],
        ];
    }
}