<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name' => 'Aizhia Azwan',
            'email' => 'aizhia@mahasiswa.ac.id',
            'password' => Hash::make('password123'),
            'university' => 'Universitas Pamulang',
            'program' => 'Teknik Informatika',
            'semester' => 4,
            'entry_year' => 2024,
        ]);

        // ----- Mata kuliah -----
        $rows = [
            ['Web Programming', 'IF3201', 3, 'Dr. Irwan Santoso, M.T.', 1, '08:00', '10:30', 'Lab Kom 3', 'code', 'primary', '#5B3DE0'],
            ['Machine Learning', 'IF4102', 3, 'Prof. Siti Rahma, Ph.D.', 2, '13:00', '15:30', 'Gd. Teori 4.2', 'cpu', 'accent', '#C99A00'],
            ['Metode Penelitian', 'IF3105', 2, 'Dr. Hendra Wijaya', 3, '10:00', '11:40', 'Ruang 201', 'book-open', 'primary', '#7B5FD6'],
            ['Jaringan Komputer', 'IF2204', 3, 'Ahmad Fauzi, M.Kom.', 4, '08:00', '10:30', 'Lab Jarkom', 'share-2', 'primary', '#C7B8FF'],
            ['Analisis & Perancangan Sistem', 'IF3208', 3, 'Dewi Lestari, S.T., M.Sc.', 5, '09:00', '11:30', 'Ruang 304', 'layers', 'primary', '#8A8AA0'],
        ];

        $courses = [];
        foreach ($rows as $r) {
            $courses[] = $user->courses()->create([
                'name' => $r[0], 'code' => $r[1], 'sks' => $r[2], 'lecturer' => $r[3],
                'day' => $r[4], 'start_time' => $r[5], 'end_time' => $r[6], 'room' => $r[7],
                'icon' => $r[8], 'tone' => $r[9], 'stripe' => $r[10],
                'semester' => 4,
            ]);
        }

        // ----- Tugas -----
        $tasks = [];
        $taskRows = [
            [0, 'Membuat CRUD Laravel & Sanctum', '2026-09-30 23:59:00', 'high', 'pending', 2, 4, null],
            [1, 'Analisis Klasifikasi Random Forest', '2026-10-02 17:00:00', 'medium', 'in_progress', 0, 0, null],
            [2, 'Draft Bab 1-3 Proposal Skripsi', '2026-10-05 20:00:00', 'low', 'in_progress', 0, 0, null],
            [3, 'Simulasi Topologi Jaringan Cisco', '2026-10-10 23:59:00', 'medium', 'pending', 0, 0, null],
            [4, 'Perancangan Diagram UML & Use Case', '2026-09-27 23:59:00', 'low', 'completed', 0, 0, '2026-09-25 10:00:00'],
        ];
        foreach ($taskRows as $t) {
            $tasks[] = $user->tasks()->create([
                'course_id' => $courses[$t[0]]->id,
                'title' => $t[1], 'deadline' => $t[2], 'priority' => $t[3], 'status' => $t[4],
                'subtasks_done' => $t[5], 'subtasks_total' => $t[6], 'completed_at' => $t[7],
            ]);
        }

        // ----- Agenda -----
        $agendaRows = [
            ['Kuliah Metode Penelitian', '2026-09-30', '10:00', '11:40', 'kuliah', true, 'Ruang 201'],
            ['Belajar Cisco Packet Tracer', '2026-09-30', '15:00', '16:30', 'belajar', false, null],
            ['Rapat Organisasi HIMA', '2026-09-30', '19:30', '21:00', 'organisasi', true, 'Bahas rencana kegiatan bulan Oktober'],
            ['Kuliah Jaringan Komputer', '2026-10-01', '08:00', '10:30', 'kuliah', false, 'Lab Jarkom'],
            ['Futsal bareng teman', '2026-10-01', '17:00', '18:30', 'olahraga', false, null],
        ];
        $agendas = [];
        foreach ($agendaRows as $a) {
            $agendas[] = $user->agendas()->create([
                'title' => $a[0], 'date' => $a[1], 'start_time' => $a[2], 'end_time' => $a[3],
                'category' => $a[4], 'reminder' => $a[5], 'description' => $a[6],
            ]);
        }

        // ----- To-Do -----
        $user->todos()->createMany([
            ['title' => 'Push project ke GitHub', 'deadline' => '2026-09-30', 'priority' => 'high', 'status' => 'pending'],
            ['title' => 'Belajar Cisco', 'description' => 'Latihan konfigurasi VLAN', 'deadline' => '2026-10-01', 'priority' => 'medium', 'status' => 'in_progress'],
            ['title' => 'Membaca jurnal', 'priority' => 'low', 'status' => 'pending'],
            ['title' => 'Olahraga pagi', 'deadline' => '2026-09-30', 'priority' => 'medium', 'status' => 'completed'],
        ]);

        // ----- Catatan -----
        $user->notes()->createMany([
            ['title' => 'Catatan Machine Learning', 'content' => 'Random Forest menggabungkan banyak decision tree.', 'date' => '2026-09-29', 'tag' => 'kuliah'],
            ['title' => 'Materi Cisco', 'content' => 'VLAN memisahkan jaringan secara logis.', 'date' => '2026-09-28', 'tag' => 'kuliah'],
            ['title' => 'Ide project', 'content' => 'Aplikasi pengingat kas organisasi.', 'date' => '2026-09-26', 'tag' => 'ide'],
        ]);

        // ----- Pengingat -----
        $user->reminders()->createMany([
            ['task_id' => $tasks[0]->id, 'offset' => '1d', 'enabled' => true],
            ['task_id' => $tasks[1]->id, 'offset' => '3h', 'enabled' => true],
            ['agenda_id' => $agendas[2]->id, 'offset' => '1h', 'enabled' => false],
            ['agenda_id' => $agendas[3]->id, 'offset' => '1h', 'enabled' => true],
        ]);
    }
}