<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class JobBoardTest extends TestCase
{
    use RefreshDatabase;

    private function makeEmployer(): User
    {
        $user = User::factory()->create(['role' => 'employer']);
        Company::create(['user_id' => $user->id, 'name' => 'Test Co']);

        return $user;
    }

    private function jobData(): array
    {
        return [
            'title' => 'Laravel Developer',
            'description' => 'Build REST APIs',
            'location' => 'Cairo',
            'type' => 'full_time',
            'salary' => 12000,
        ];
    }

    public function test_employer_can_create_job(): void
    {
        Sanctum::actingAs($this->makeEmployer());

        $this->postJson('/api/jobs', $this->jobData())
            ->assertStatus(201);
    }

    public function test_candidate_cannot_create_job(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'candidate']));

        $this->postJson('/api/jobs', $this->jobData())
            ->assertStatus(403);
    }

    public function test_candidate_can_apply_only_once(): void
    {
        $employer = $this->makeEmployer();
        $job = $employer->company->jobs()->create($this->jobData());

        Sanctum::actingAs(User::factory()->create(['role' => 'candidate']));

        $this->postJson("/api/jobs/{$job->id}/apply", ['cover_letter' => 'Hi'])
            ->assertStatus(201);

        $this->postJson("/api/jobs/{$job->id}/apply")
            ->assertStatus(409);
    }

    public function test_jobs_can_be_filtered_by_type(): void
    {
        $employer = $this->makeEmployer();
        $employer->company->jobs()->create($this->jobData());
        $employer->company->jobs()->create(
            array_merge($this->jobData(), ['type' => 'remote'])
        );

        $this->getJson('/api/jobs?type=remote')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}