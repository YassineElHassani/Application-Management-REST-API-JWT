<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\JobOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


/**
 * @OA\Schema(
 *     schema="JobOffer",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="recruiter_id", type="integer"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="location", type="string"),
 *     @OA\Property(property="contract_type", type="string", enum={"full-time", "part-time", "freelance"}),
 *     @OA\Property(property="salary", type="number", format="float"),
 *     @OA\Property(property="posted_at", type="string", format="date-time"),
 *     @OA\Property(property="status", type="string", enum={"draft", "published", "closed"}),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="recruiter",
 *         type="object",
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="name", type="string"),
 *         @OA\Property(property="email", type="string")
 *     ),
 *     @OA\Property(property="applications_count", type="integer")
 * )
 */
class JobOfferController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/job-offers",
     *     summary="Get list of job offers",
     *     tags={"Job Offers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of job offers",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/JobOffer")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/job-offers",
     *     summary="Create a new job offer",
     *     tags={"Job Offers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","description","location","contract_type","salary"},
     *             @OA\Property(property="title", type="string", example="Senior Developer"),
     *             @OA\Property(property="description", type="string", example="We are looking for a senior developer..."),
     *             @OA\Property(property="location", type="string", example="New York"),
     *             @OA\Property(property="contract_type", type="string", enum={"full-time", "part-time", "freelance"}, example="full-time"),
     *             @OA\Property(property="salary", type="number", format="float", example=80000),
     *             @OA\Property(property="status", type="string", enum={"draft", "published", "closed"}, example="published")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Job offer created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/JobOffer")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/job-offers/{id}",
     *     summary="Get a specific job offer",
     *     tags={"Job Offers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Job offer ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Job offer details",
     *         @OA\JsonContent(ref="#/components/schemas/JobOffer")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Job offer not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        $jobOffer = JobOffer::with('recruiter:id,name,email')->findOrFail($id);
        
        $this->authorize('view', $jobOffer);
        
        $jobOffer->applications_count = $jobOffer->applications()->count();
        
        return response()->json($jobOffer);
    }

    
    /**
     * @OA\Put(
     *     path="/api/job-offers/{id}",
     *     summary="Update a job offer",
     *     tags={"Job Offers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Job offer ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","description","location","contract_type","salary"},
     *             @OA\Property(property="title", type="string", example="Senior Developer"),
     *             @OA\Property(property="description", type="string", example="We are looking for a senior developer..."),
     *             @OA\Property(property="location", type="string", example="New York"),
     *             @OA\Property(property="contract_type", type="string", enum={"full-time", "part-time", "freelance"}, example="full-time"),
     *             @OA\Property(property="salary", type="number", format="float", example=80000),
     *             @OA\Property(property="status", type="string", enum={"draft", "published", "closed"}, example="published")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Job offer updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/JobOffer")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Job offer not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
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
     * @OA\Delete(
     *     path="/api/job-offers/{id}",
     *     summary="Delete a job offer",
     *     tags={"Job Offers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Job offer ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Job offer deleted successfully",
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
     *         description="Job offer not found"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $jobOffer = JobOffer::findOrFail($id);
        
        $this->authorize('delete', $jobOffer);
        
        $jobOffer->delete();
        
        return response()->json(['message' => 'Job offer deleted successfully']);
    }
}
