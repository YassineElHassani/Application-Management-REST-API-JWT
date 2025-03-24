<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;


class ProfileController extends Controller
{
    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $profile = $request->user()->profile;
        return response()->json(['profile' => $profile]);
    }
    
    /**
     * Update the specified resource in storage.
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
