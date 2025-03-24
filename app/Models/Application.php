<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Application extends Model
{
    use HasFactory;

    protected $table = 'applications';

    protected $fillable = [
        'user_id',
        'job_offer_id',
        'cover_letter',
        'status',
        'cv_path',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_REVIEWED = 'reviewed';
    const STATUS_INTERVIEW = 'interview';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobOffer()
    {
        return $this->belongsTo(JobOffer::class);
    }

    public function cv()
    {
        return $this->belongsTo(CV::class);
    }
}