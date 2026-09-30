<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeCourse(User $user)
    {
        return $user->courses()->create([
            'name' => 'Web Programming', 'code' => 'IF3201', 'day' => 1,
            'start_time' => '08:00', 'end_time' => '10:00',
        ]);
    }

    private function makeTask(User $user, ?int $courseId = null)
    {
        return $user->tasks()->create([
            'course_id' => $courseId, 'title' => 'Tugas A', 'deadline' => '2026-10-05 20:00:00',
            'priority' => 'high', 'status' => 'pending',
        ]);
    }

    public function test_register_login_and_wrong_password(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Tester', 'email' => 'a@test.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ])->assertCreated()->assertJsonPath('success', true)->assertJsonStructure(['data' => ['token', 'user']]);

        // Password tersimpan sebagai hash, bukan teks asli
        $this->assertNotSame('password123', User::first()->password);

        $this->postJson('/api/login', ['email' => 'a@test.com', 'password' => 'password123'])
            ->assertOk()->assertJsonStructure(['data' => ['token']]);

        $this->postJson('/api/login', ['email' => 'a@test.com', 'password' => 'salah'])
            ->assertStatus(401)->assertJsonPath('success', false);
    }

    public function test_requests_without_token_are_rejected(): void
    {
        $this->getJson('/api/tasks')->assertStatus(401)->assertJsonPath('success', false);
    }

    public function test_validation_error_format(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/tasks', [])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation error')
            ->assertJsonStructure(['errors' => ['title', 'deadline', 'priority', 'status']]);
    }

    public function test_create_and_list_task(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $course = $this->makeCourse($user);

        $this->postJson('/api/tasks', [
            'title' => 'CRUD Laravel', 'course_id' => $course->id,
            'deadline' => '2026-09-30T23:59:00+07:00', 'priority' => 'high', 'status' => 'pending',
        ])->assertCreated()->assertJsonPath('data.deadline', '2026-09-30T23:59:00+07:00');

        $this->getJson('/api/tasks')->assertOk()->assertJsonPath('meta.total', 1);
    }

    public function test_user_cannot_touch_other_users_task(): void
    {
        $owner = User::factory()->create();
        $task = $this->makeTask($owner);

        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/tasks/{$task->id}")->assertNotFound();
        $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Diretas', 'deadline' => '2026-10-05', 'priority' => 'low', 'status' => 'pending',
        ])->assertNotFound();
        $this->deleteJson("/api/tasks/{$task->id}")->assertNotFound();

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Tugas A']);
        $this->getJson('/api/tasks')->assertJsonPath('meta.total', 0);
    }

    public function test_cannot_use_other_users_course(): void
    {
        $owner = User::factory()->create();
        $course = $this->makeCourse($owner);

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/tasks', [
            'title' => 'X', 'course_id' => $course->id,
            'deadline' => '2026-10-05', 'priority' => 'low', 'status' => 'pending',
        ])->assertStatus(422)->assertJsonValidationErrors('course_id');
    }

    public function test_deleting_course_removes_tasks_and_task_removes_reminders(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $course = $this->makeCourse($user);
        $task = $this->makeTask($user, $course->id);
        $user->reminders()->create(['task_id' => $task->id, 'offset' => '1d', 'enabled' => true]);

        $this->deleteJson("/api/courses/{$course->id}")->assertOk();

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
        $this->assertDatabaseCount('reminders', 0);
    }

    public function test_reminder_needs_exactly_one_target_and_no_duplicates(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $task = $this->makeTask($user);

        $this->postJson('/api/reminders', ['offset' => '1d', 'enabled' => true])->assertStatus(422);

        $this->postJson('/api/reminders', ['task_id' => $task->id, 'offset' => '1d', 'enabled' => true])->assertCreated();
        $this->postJson('/api/reminders', ['task_id' => $task->id, 'offset' => '1d', 'enabled' => true])->assertStatus(422);
    }
}