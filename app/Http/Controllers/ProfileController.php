<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


/**
 * @OA\Schema(
 *     schema="Profile",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="phone_number", type="string", nullable=true),
 *     @OA\Property(property="image", type="string", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ProfileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/profile",
     *     summary="Get authenticated user's profile",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="profile",
     *                 ref="#/components/schemas/Profile"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function show(Request $request)
    {
        $profile = $request->user()->profile;
        return response()->json(['profile' => $profile]);
    }
    
    
    /**
     * @OA\Put(
     *     path="/api/profile",
     *     summary="Update user profile",
     *     tags={"Profile"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="phone_number", type="string", example="555-123-4567"),
     *             @OA\Property(property="image", type="string", example="base64_encoded_image_or_url")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Profile")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request)
    {
        // $user = Auth::guard('api')->user();

        $validator = Validator::make($request->all(), [
            'phone_number' => 'nullable|string|max:20',
            'image' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $profile = $request->user()->profile;
        
        if (!$profile) {
            $profile = Profile::create([
                'user_id' => $request->user()->id,
                'phone_number' => $request->phone_number,
                'image' => $request->image,
            ]);
        } else {
            $profile->update($request->only(['phone_number', 'image']));
        }

        return response()->json($profile);        
    }

}
