<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CommunityPost;
use App\Models\Client;
use App\Models\Coach;

class Comment extends Model
{
    protected $fillable = ['post_id', 'client_id', 'coach_id', 'body'];

    public function post()
    {
        return $this->belongsTo(CommunityPost::class, 'post_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    public function coach()
    {
        return $this->belongsTo(Coach::class, 'coach_id', 'coach_id');
    }
}
