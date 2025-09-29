<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoachProfilePost extends Model
{
    use HasFactory;

    protected $table = 'coach_profile_posts';

    protected $fillable = [
        'coach_name',
        'coach_id',
        'media_path',
        'caption',
    ];

    // Relationship to the Coach model
    public function coach()
    {
        return $this->belongsTo(Coach::class, 'coach_id', 'coach_id');
    }
}
