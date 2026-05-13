<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'username' => 'lietotajs',
            'email' => 'lietotajs@example.com',
            'password' => 'parole123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('user.role', 'user')
            ->assertJsonStructure(['token']);

        $this->assertDatabaseHas('users', [
            'email' => 'lietotajs@example.com',
            'role' => 'user',
        ]);
    }

    public function test_authenticated_user_can_manage_event_and_read_stats(): void
    {
        $user = User::create([
            'username' => 'anna',
            'email' => 'anna@example.com',
            'password' => Hash::make('parole123'),
            'role' => 'user',
        ]);
        $group = Group::create([
            'name' => 'Skola',
            'description' => 'Mācību darbi',
        ]);

        Sanctum::actingAs($user);

        $created = $this->postJson('/api/events', [
            'title' => 'Noslēguma darba nodošana',
            'date' => '2026-05-20',
            'time' => '10:00',
            'location' => 'Rīga',
            'priority' => 'high',
            'group_id' => $group->id,
        ])->assertCreated();

        $eventId = $created->json('id');

        $this->getJson('/api/events?search=nodošana&priority=high&sort_by=title&sort_dir=asc')
            ->assertOk()
            ->assertJsonPath('0.id', $eventId)
            ->assertJsonPath('0.group.name', 'Skola');

        $this->putJson("/api/events/{$eventId}", [
            'title' => 'Noslēguma darba prezentācija',
            'date' => '2026-05-21',
            'time' => '12:00',
            'location' => 'Rīga',
            'priority' => 'medium',
            'group_id' => $group->id,
            'done' => true,
        ])->assertOk()
            ->assertJsonPath('done', true);

        $this->getJson('/api/events/stats')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('done', 1)
            ->assertJsonPath('by_group.0.name', 'Skola');

        $this->deleteJson("/api/events/{$eventId}")
            ->assertOk();

        $this->assertDatabaseMissing('events', ['id' => $eventId]);
    }
}
