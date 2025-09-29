<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostReact extends Model
{
    protected $fillable = ['post_id', 'reactor_type', 'reactor_id'];
}
