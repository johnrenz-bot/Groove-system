<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'client_id','coach_id',
        'name','email','subject','message',
        'status','priority',
        'attachment_path','attachment_name','attachment_mime','attachment_size',
        'attachment_count',
    ];
}
