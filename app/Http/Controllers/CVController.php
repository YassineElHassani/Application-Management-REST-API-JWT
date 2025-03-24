<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CV;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CVController extends Controller
{
    /**
     * Store a newly created resource in storage.
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
     * Display the specified resource.
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
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
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
        
        $hasApplied = $cv->user->applications()
            ->whereHas('jobOffer', function($query) use ($user) {
                $query->where('recruiter_id', $user->id);
            })
            ->exists();
        
        return $hasApplied;
    }
}
