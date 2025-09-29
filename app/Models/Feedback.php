<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedbacks';
    protected $primaryKey = 'feedback_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'coach_id',
        'user_id',  // references clients.client_id
        'rating',
        'comment',
    ];

    public function coach()
    {
        return $this->belongsTo(Coach::class, 'coach_id', 'coach_id');
    }

    // Use ONE relation name consistently in Blade
    public function user()
    {
        // 'user_id' (feedbacks) -> 'client_id' (clients)
        return $this->belongsTo(Client::class, 'user_id', 'client_id');
    }
}
