<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\JobOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class JobOfferController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::guard('api')->user();
        
        if ($user->role === 'recruiter') {
            $jobOffers = JobOffer::where('recruiter_id', $user->id)->get();
        } else if ($user->role === 'admin') {
            $jobOffers = JobOffer::all();
        } else {
            $jobOffers = JobOffer::where('status', 'published')->get();
        }
        
        return response()->json($jobOffers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        
        $this->authorize('create', JobOffer::class);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'contract_type' => 'required|in:full-time,part-time,freelance',
            'salary' => 'required|numeric|min:0',
            'status' => 'sometimes|required|in:draft,published,closed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $jobOffer = JobOffer::create([
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'contract_type' => $request->contract_type,
            'salary' => $request->salary,
            'recruiter_id' => $user->id,
            'posted_at' => now(),
            'status' => $request->status,
        ]);
            
        return response()->json($jobOffer, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $jobOffer = JobOffer::with('recruiter:id,name,email')->findOrFail($id);
        
        $this->authorize('view', $jobOffer);
        
        $jobOffer->applications_count = $jobOffer->applications()->count();
        
        return response()->json($jobOffer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $jobOffer = JobOffer::findOrFail($id);
        
        $this->authorize('update', $jobOffer);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'contract_type' => 'required|in:full-time,part-time,freelance',
            'salary' => 'required|numeric|min:0',
            'status' => 'sometimes|required|in:draft,published,closed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $jobOffer->update($request->only([
            'title', 'description', 'location', 'contract_type', 'salary', 'status'
        ]));

        $jobOffer->save();

        return response()->json($jobOffer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $jobOffer = JobOffer::findOrFail($id);
        
        $this->authorize('delete', $jobOffer);
        
        $jobOffer->delete();
        
        return response()->json(['message' => 'Job offer deleted successfully']);
    }
}
