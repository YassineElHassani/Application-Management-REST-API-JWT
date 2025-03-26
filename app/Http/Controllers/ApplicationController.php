<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\JobOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


/**
 * @OA\Schema(
 *     schema="Application",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="job_offer_id", type="integer"),
 *     @OA\Property(property="cover_letter", type="string"),
 *     @OA\Property(property="status", type="string", enum={"pending", "reviewed", "interview", "accepted", "rejected"}),
 *     @OA\Property(property="cv_path", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="user",
 *         type="object",
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="name", type="string"),
 *         @OA\Property(property="email", type="string")
 *     ),
 *     @OA\Property(
 *         property="job_offer",
 *         type="object",
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="title", type="string"),
 *         @OA\Property(property="recruiter_id", type="integer")
 *     )
 * )
 */
class ApplicationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/applications",
     *     summary="Get list of applications",
     *     tags={"Applications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="job_offer_id",
     *         in="query",
     *         description="Filter applications by job offer ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of applications",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Application")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access"
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/applications",
     *     summary="Submit a new application",
     *     tags={"Applications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"job_offer_id", "cover_letter"},
     *                 @OA\Property(
     *                     property="job_offer_id",
     *                     type="integer",
     *                     description="ID of the job offer"
     *                 ),
     *                 @OA\Property(
     *                     property="cover_letter",
     *                     type="string",
     *                     description="Cover letter text"
     *                 ),
     *                 @OA\Property(
     *                     property="cv",
     *                     type="string",
     *                     format="binary",
     *                     description="CV file (PDF, DOC, DOCX, max 2MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Application created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Application")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or already applied"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access"
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/applications/{id}",
     *     summary="Get a specific application",
     *     tags={"Applications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Application ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Application details",
     *         @OA\JsonContent(ref="#/components/schemas/Application")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Application not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        $application = Application::with(['user:id,name,email', 'jobOffer'])->findOrFail($id);
        
        $this->authorize('view', $application);
        
        return response()->json($application);
    }

    
    /**
     * @OA\Put(
     *     path="/api/applications/{id}",
     *     summary="Update an application",
     *     tags={"Applications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Application ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="cover_letter",
     *                     type="string",
     *                     description="Cover letter text (for candidates)"
     *                 ),
     *                 @OA\Property(
     *                     property="cv",
     *                     type="string",
     *                     format="binary",
     *                     description="CV file (PDF, DOC, DOCX, max 2MB) (for candidates)"
     *                 ),
     *                 @OA\Property(
     *                     property="status",
     *                     type="string",
     *                     enum={"pending", "reviewed", "interview", "accepted", "rejected"},
     *                     description="Application status (for recruiters/admins)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Application updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Application")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Application not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
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
     * @OA\Delete(
     *     path="/api/applications/{id}",
     *     summary="Delete an application",
     *     tags={"Applications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Application ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Application deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Application not found"
     *     )
     * )
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