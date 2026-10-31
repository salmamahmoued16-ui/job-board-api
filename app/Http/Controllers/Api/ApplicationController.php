<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobListing;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    // Candidate: التقديم على وظيفة
    public function apply(Request $request, JobListing $job)
    {
        $data = $request->validate([
            'cover_letter' => 'nullable|string',
        ]);

        if (! $job->is_active) {
            return response()->json(['message' => 'This job is closed'], 422);
        }

        $alreadyApplied = JobApplication::where('job_listing_id', $job->id)
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($alreadyApplied) {
            return response()->json(['message' => 'You already applied to this job'], 409);
        }

        $application = JobApplication::create([
            'job_listing_id' => $job->id,
            'user_id' => $request->user()->id,
            'cover_letter' => $data['cover_letter'] ?? null,
        ]);

        return response()->json($application, 201);
    }

    // Candidate: تقديماتي
    public function myApplications(Request $request)
    {
        return JobApplication::with('job.company')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();
    }

    // Employer: المتقدمين على وظيفة تخصه
    public function jobApplications(Request $request, JobListing $job)
    {
        if ($job->company_id !== $request->user()->company->id) {
            abort(403, 'Forbidden');
        }

        return $job->applications()->with('user:id,name,email')->latest()->get();
    }

    // Employer: تغيير حالة الطلب
    public function updateStatus(Request $request, JobApplication $application)
    {
        $companyId = $application->job->company_id;

        if ($companyId !== $request->user()->company->id) {
            abort(403, 'Forbidden');
        }

        $data = $request->validate([
            'status' => 'required|in:pending,accepted,rejected',
        ]);

        $application->update($data);

        return response()->json($application);
    }
}
