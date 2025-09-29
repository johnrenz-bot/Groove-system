<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientProfilePost extends Model
{
    use HasFactory;

    protected $table = 'client_profile_posts';

    protected $fillable = [
        'client_name',
        'client_id',
        'media_path',
        'caption',
    ];

  
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }
    
}
