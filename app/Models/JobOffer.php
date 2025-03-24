<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class JobOffer extends Model
{
    use HasFactory;

    protected $table = 'job_offers';

    protected $fillable = [
        'title',
        'description',
        'location',
        'contract_type',
        'salary',
        'posted_at',
        'recruiter_id',
        'status',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
        'salary' => 'decimal:2',
    ];

    public function recruiter()
    {
        return $this->belongsTo(User::class, 'recruiter_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}