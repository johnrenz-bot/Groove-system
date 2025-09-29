<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'coach_id',
        'agreement_date',
        'appointment_price',
        'session_duration',
        'payment_method',
        'notice_hours',
        'notice_days',
        'cancellation_method',
        'client_signature',
        'coach_signature',
        'agreement_pdf',
    ];

    /**
     * Get the client for this agreement.
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    /**
     * Get the coach for this agreement.
     */
    public function coach()
    {
        return $this->belongsTo(Coach::class, 'coach_id', 'coach_id');
    }
}
