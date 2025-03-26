<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


/**
 * @OA\Schema(
 *     schema="Skill",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class SkillController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/skills",
     *     summary="Get list of skills",
     *     tags={"Skills"},
     *     @OA\Response(
     *         response=200,
     *         description="List of all skills",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Skill")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $skills = Skill::all();
        return response()->json($skills);
    }

    
    /**
     * @OA\Post(
     *     path="/api/skills",
     *     summary="Create a new skill",
     *     tags={"Skills"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="JavaScript"),
     *             @OA\Property(property="description", type="string", example="Programming language for web development")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Skill created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Skill")
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
        // $user = Auth::guard('api')->user();
        
        // if ($user->role !== 'admin') {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:skills',
            'description' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $skill = Skill::create($request->all());
        
        return response()->json($skill, 201);
    }

    
    /**
     * @OA\Get(
     *     path="/api/skills/{id}",
     *     summary="Get a specific skill",
     *     tags={"Skills"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Skill ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Skill details",
     *         @OA\JsonContent(ref="#/components/schemas/Skill")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Skill not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        $skill = Skill::findOrFail($id);
        return response()->json($skill);
    }

    
    /**
     * @OA\Put(
     *     path="/api/skills/{id}",
     *     summary="Update a skill",
     *     tags={"Skills"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Skill ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="JavaScript"),
     *             @OA\Property(property="description", type="string", example="Updated description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Skill updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Skill")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Skill not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        // $user = Auth::guard('api')->user();
        
        // if ($user->role !== 'admin') {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }
        
        $skill = Skill::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:skills,name,' . $id,
            'description' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $skill->update($request->all());
        
        return response()->json($skill);
    }

    
    /**
     * @OA\Delete(
     *     path="/api/skills/{id}",
     *     summary="Delete a skill",
     *     tags={"Skills"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Skill ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Skill deleted successfully",
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
     *         description="Skill not found"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        // $user = Auth::guard('api')->user();
        
        // if ($user->role !== 'admin') {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }
        
        $skill = Skill::findOrFail($id);
        $skill->delete();
        
        return response()->json(['message' => 'Skill deleted successfully']);
    }
}