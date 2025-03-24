<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class CV extends Model
{
    use HasFactory;

    protected $table = 'cvs';

    protected $fillable = [
        'user_id',
        'title',
        'file_path',
        'file_type',
    ];

    protected $hidden = [
        'file_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}