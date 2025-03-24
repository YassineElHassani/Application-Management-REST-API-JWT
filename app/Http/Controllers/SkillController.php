<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SkillController extends Controller
{
    /**
     * Display a listing of skills.
     */
    public function index()
    {
        $skills = Skill::all();
        return response()->json($skills);
    }

    /**
     * Store a newly created skill.
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
     * Display the specified skill.
     */
    public function show(string $id)
    {
        $skill = Skill::findOrFail($id);
        return response()->json($skill);
    }

    /**
     * Update the specified skill.
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
     * Remove the specified skill.
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