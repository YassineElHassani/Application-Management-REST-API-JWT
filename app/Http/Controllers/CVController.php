<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CV;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


/**
 * @OA\Schema(
 *     schema="CV",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="file_path", type="string"),
 *     @OA\Property(property="file_type", type="string"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class CVController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/cvs",
     *     summary="Upload a new CV",
     *     tags={"CVs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file", "title"},
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="CV file (PDF, DOC, DOCX, max 2MB)"
     *                 ),
     *                 @OA\Property(
     *                     property="title",
     *                     type="string",
     *                     description="CV title"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="CV uploaded successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CV")
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
        
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,doc,docx|max:2048',
            'title' => 'required|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $path = $request->file('file')->store('cvs/' . $user->id, 's3');
        
        $cv = CV::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'file_path' => $path,
            'file_type' => $request->file('file')->getClientOriginalExtension(),
        ]);
        
        return response()->json($cv, 201);
    }

    
    /**
     * @OA\Get(
     *     path="/api/cvs/{id}",
     *     summary="Get a specific CV",
     *     tags={"CVs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="CV ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="CV details with download URL",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/CV"),
     *                 @OA\Schema(
     *                     @OA\Property(property="download_url", type="string", format="url")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="CV not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        $cv = CV::findOrFail($id);
        $user = Auth::guard('api')->user();
        
        if ($user->id !== $cv->user_id && $user->role !== 'admin' && !$this->isRecruiterForCV($user, $cv)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        if ($cv->file_path) {
            $s3 = Storage::disk('s3')->getDriver()->getAdapter()->getClient();
            $command = $s3->getCommand('GetObject', [
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => $cv->file_path
            ]);
            $request = $s3->createPresignedRequest($command, '+5 minutes');
            $url = (string) $request->getUri();
            
            $cv->download_url = $url;
        }
        
        return response()->json($cv);
    }
    
    
    /**
     * @OA\Put(
     *     path="/api/cvs/{id}",
     *     summary="Update a CV",
     *     tags={"CVs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="CV ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="CV file (PDF, DOC, DOCX, max 2MB)"
     *                 ),
     *                 @OA\Property(
     *                     property="title",
     *                     type="string",
     *                     description="CV title"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="CV updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/CV")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="CV not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $cv = CV::findOrFail($id);
        $user = Auth::guard('api')->user();
        
        if ($user->id !== $cv->user_id && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'title' => 'sometimes|required|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        if ($request->hasFile('file')) {
            if ($cv->file_path) {
                Storage::disk('s3')->delete($cv->file_path);
            }
            
            $path = $request->file('file')->store('cvs/' . $user->id, 's3');
            $cv->file_path = $path;
            $cv->file_type = $request->file('file')->getClientOriginalExtension();
        }
        
        if ($request->has('title')) {
            $cv->title = $request->title;
        }
        
        $cv->save();
        
        return response()->json($cv);
    }

    
    /**
     * @OA\Delete(
     *     path="/api/cvs/{id}",
     *     summary="Delete a CV",
     *     tags={"CVs"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="CV ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="CV deleted successfully",
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
     *         description="CV not found"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $cv = CV::findOrFail($id);

        $user = Auth::guard('api')->user();

        if ($user->id !== $cv->user_id && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        if ($cv->file_path) {
            Storage::disk('s3')->delete($cv->file_path);
        }
        
        $cv->delete();
        
        return response()->json(['message' => 'CV deleted successfully']);
    }

    private function isRecruiterForCV($user, $cv)
    {
        if ($user->role !== 'recruiter') {
            return false;
        }
        
        $hasApplied = $cv->user->applications()->whereHas('jobOffer', function($query) use ($user) {
                $query->where('recruiter_id', $user->id);
            })->exists();
        
        return $hasApplied;
    }
}
