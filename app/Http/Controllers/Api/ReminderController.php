<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReminderController extends Controller
{
    /** Filter: enabled=1|0 */
    public function index(Request $request)
    {
        $q = $request->user()->reminders()
            ->with(['task:id,title,deadline', 'agenda:id,title,date,start_time']);

        if ($request->has('enabled')) {
            $q->where('enabled', $request->boolean('enabled'));
        }

        $q->orderBy('id');

        return $this->paged($q->paginate($this->perPage($request)));
    }

    public function store(Request $request)
    {
        $data = $this->clean($request->validate($this->rules($request)));
        $reminder = $request->user()->reminders()->create($data);

        return $this->ok($reminder->load('task:id,title,deadline', 'agenda:id,title,date,start_time'), 'Pengingat dibuat', 201);
    }

    public function show(Request $request, string $id)
    {
        return $this->ok(
            $request->user()->reminders()->with('task:id,title,deadline', 'agenda:id,title,date,start_time')->findOrFail($id)
        );
    }

    public function update(Request $request, string $id)
    {
        $reminder = $request->user()->reminders()->findOrFail($id);
        $reminder->update($this->clean($request->validate($this->rules($request, $reminder->id))));

        return $this->ok($reminder->fresh()->load('task:id,title,deadline', 'agenda:id,title,date,start_time'), 'Pengingat diperbarui');
    }

    public function destroy(Request $request, string $id)
    {
        $request->user()->reminders()->findOrFail($id)->delete();

        return $this->ok(null, 'Pengingat dihapus');
    }

    private function rules(Request $request, ?int $ignoreId = null): array
    {
        $uid = $request->user()->id;

        return [
            // Tepat satu terisi: wajib salah satu, dan tidak boleh keduanya
            'task_id' => [
                'nullable', 'integer', 'required_without:agenda_id', 'prohibits:agenda_id',
                Rule::exists('tasks', 'id')->where('user_id', $uid),
            ],
            'agenda_id' => [
                'nullable', 'integer', 'required_without:task_id', 'prohibits:task_id',
                Rule::exists('agendas', 'id')->where('user_id', $uid),
            ],
            'offset' => [
                'required', Rule::in(['7d', '3d', '1d', '3h', '1h']),
                Rule::unique('reminders', 'offset')
                    ->where(fn ($q) => $q->where('task_id', $request->input('task_id'))
                        ->where('agenda_id', $request->input('agenda_id')))
                    ->ignore($ignoreId),
            ],
            'enabled' => ['required', 'boolean'],
        ];
    }

    private function clean(array $data): array
    {
        $data['task_id'] = $data['task_id'] ?? null;
        $data['agenda_id'] = $data['agenda_id'] ?? null;

        return $data;
    }
}