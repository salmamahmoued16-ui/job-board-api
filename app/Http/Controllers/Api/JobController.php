<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobResource;
use App\Models\JobListing;
use Illuminate\Http\Request;

class JobController extends Controller
{
    // عرض الوظايف مع البحث والفلترة (للكل)
    public function index(Request $request)
    {
        $jobs = JobListing::with('company')
            ->where('is_active', true)
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->location, fn ($q, $v) => $q->where('location', $v))
            ->when($request->type, fn ($q, $v) => $q->where('type', $v))
            ->latest()
            ->paginate(10);

        return JobResource::collection($jobs);
    }

    public function show(JobListing $job)
    {
        return new JobResource($job->load('company'));
    }

    // الـ Employer بس
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'type' => 'required|in:full_time,part_time,remote,internship',
            'salary' => 'nullable|integer|min:0',
        ]);

        $job = $request->user()->company->jobs()->create($data);

        return (new JobResource($job->load('company')))
            ->response()->setStatusCode(201);
    }

    public function update(Request $request, JobListing $job)
    {
        $this->authorizeOwner($request, $job);

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'location' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:full_time,part_time,remote,internship',
            'salary' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $job->update($data);

        return new JobResource($job->load('company'));
    }

    public function destroy(Request $request, JobListing $job)
    {
        $this->authorizeOwner($request, $job);
        $job->delete();

        return response()->json(['message' => 'Job deleted']);
    }

    private function authorizeOwner(Request $request, JobListing $job): void
    {
        if ($job->company_id !== $request->user()->company->id) {
            abort(403, 'Forbidden');
        }
    }
}
