<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\JobOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ApplicationController extends Controller
{
    /**
     * Display a listing of the applications.
     */
    public function index(Request $request)
    {
        $user = Auth::guard('api')->user();
        
        $this->authorize('viewAny', Application::class);
        
        if ($request->has('job_offer_id')) {
            $jobOffer = JobOffer::findOrFail($request->job_offer_id);
            
            if ($user->role === 'admin' || ($user->role === 'recruiter' && $jobOffer->recruiter_id === $user->id)) {
                $applications = Application::where('job_offer_id', $request->job_offer_id)->with('user:id,name,email')->get();
            } else {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        } else {
            if ($user->role === 'admin') {
                $applications = Application::with(['user:id,name,email', 'jobOffer:id,title,recruiter_id'])->get();
            } else if ($user->role === 'recruiter') {
                $applications = Application::whereHas('jobOffer', function ($query) use ($user) {
                    $query->where('recruiter_id', $user->id);
                })->with(['user:id,name,email', 'jobOffer:id,title,recruiter_id'])->get();
            } else {
                $applications = Application::where('user_id', $user->id)->with(['jobOffer:id,title,recruiter_id'])->get();
            }
        }
        
        return response()->json($applications);
    }

    /**
     * Store a newly created application.
     */
    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        
        $this->authorize('create', Application::class);
        
        $validator = Validator::make($request->all(), [
            'job_offer_id' => 'required|exists:job_offers,id',
            'cover_letter' => 'required|string',
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $existingApplication = Application::where('user_id', $user->id)->where('job_offer_id', $request->job_offer_id)->first();
        
        if ($existingApplication) {
            return response()->json(['error' => 'You have already applied for this job'], 422);
        }
        
        $cvPath = null;
        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('cvs', 's3');
        }
        
        $application = Application::create([
            'user_id' => $user->id,
            'job_offer_id' => $request->job_offer_id,
            'cover_letter' => $request->cover_letter,
            'status' => Application::STATUS_PENDING,
            'cv_path' => $cvPath,
        ]);
        
        return response()->json($application, 201);
    }

    /**
     * Display the specified application.
     */
    public function show(string $id)
    {
        $application = Application::with(['user:id,name,email', 'jobOffer'])->findOrFail($id);
        
        $this->authorize('view', $application);
        
        return response()->json($application);
    }

    /**
     * Update the specified application.
     */
    public function update(Request $request, string $id)
    {
        $application = Application::findOrFail($id);
        
        $this->authorize('update', $application);
        
        $user = Auth::guard('api')->user();
        
        if ($user->role === 'candidate') {
            $validator = Validator::make($request->all(), [
                'cover_letter' => 'sometimes|required|string',
                'cv' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:pending,reviewed,interview,accepted,rejected',
            ]);
        }
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        if ($user->role === 'candidate' && $request->hasFile('cv')) {
            if ($application->cv_path) {
                Storage::disk('s3')->delete($application->cv_path);
            }
            
            $cvPath = $request->file('cv')->store('cvs', 's3');
            $application->cv_path = $cvPath;
        }
        
        if ($user->role === 'candidate') {
            if ($request->has('cover_letter')) {
                $application->cover_letter = $request->cover_letter;
            }
        } else {
            if ($request->has('status')) {
                $application->status = $request->status;
            }
        }
        
        $application->save();
        
        return response()->json($application);
    }

    /**
     * Remove the specified application.
     */
    public function destroy(string $id)
    {
        $application = Application::findOrFail($id);
        
        $this->authorize('delete', $application);
        
        if ($application->cv_path) {
            Storage::disk('s3')->delete($application->cv_path);
        }
        
        $application->delete();
        
        return response()->json(['message' => 'Application deleted successfully']);
    }
}