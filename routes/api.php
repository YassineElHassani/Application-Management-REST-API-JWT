<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\CVController;
use App\Http\Controllers\JobOfferController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SkillController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    // Auth actions
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);
    
    // Skills
    Route::get('/skills', [SkillController::class, 'index']);
    Route::post('/skills', [SkillController::class, 'store']);
    Route::get('/skills/{skill}', [SkillController::class, 'show']);
    Route::post('/skills/{skill}', [SkillController::class, 'update']);
    Route::delete('/skills/{skill}', [SkillController::class, 'destroy']);
    
    // Job Offers
    Route::get('/job-offers', [JobOfferController::class, 'index']);
    Route::post('/job-offers', [JobOfferController::class, 'store']);
    Route::get('/job-offers/{jobOffer}', [JobOfferController::class, 'show']);
    Route::post('/job-offers/{jobOffer}', [JobOfferController::class, 'update']);
    Route::delete('/job-offers/{jobOffer}', [JobOfferController::class, 'destroy']);
    
    // Applications
    Route::get('/applications', [ApplicationController::class, 'index']);
    Route::post('/applications', [ApplicationController::class, 'store']);
    Route::get('/applications/{application}', [ApplicationController::class, 'show']);
    Route::post('/applications/{application}', [ApplicationController::class, 'update']);
    Route::delete('/applications/{application}', [ApplicationController::class, 'destroy']);
    
    // CVs
    Route::get('/cvs/{cv}', [CVController::class, 'show']);
    Route::get('/cvs/{cv}/download', [CVController::class, 'download']);
    Route::post('/cvs', [CVController::class, 'store']);
    Route::post('/cvs/{cv}', [CVController::class, 'update']);
    Route::delete('/cvs/{cv}', [CVController::class, 'destroy']);
});