<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $table = 'appointments';
    protected $primaryKey = 'appointment_id';
    public $incrementing = false;   // custom 5-digit key
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'appointment_id',
        'client_id',
        'coach_id',
        'name',
        'email',
        'contact',
        'address',
        'session_type',
        'talent',
        'date',
        'start_time',
        'end_time',
        'experience',
        'purpose',
        'message',
        'status',
        'feedback',
        'rating',
    ];

    protected $casts = [
        'date'   => 'date:Y-m-d',
        'rating' => 'integer',
    ];

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class, 'client_id', 'client_id');
    }

    public function coach()
    {
        return $this->belongsTo(\App\Models\Coach::class, 'coach_id', 'coach_id');
    }

    /** Normalize time on set (accepts "08:00 AM" etc.) */
    protected function startTime(): Attribute
    {
        return Attribute::make(
            set: fn($v) => $this->normalizeTime($v)
        );
    }
    protected function endTime(): Attribute
    {
        return Attribute::make(
            set: fn($v) => $this->normalizeTime($v)
        );
    }

    public function getStartAtAttribute(): ?Carbon
    {
        if (!$this->date || !$this->start_time) return null;
        return Carbon::parse($this->date)->setTimeFromTimeString($this->start_time);
    }

    public function getEndAtAttribute(): ?Carbon
    {
        if (!$this->date || !$this->end_time) return null;
        return Carbon::parse($this->date)->setTimeFromTimeString($this->end_time);
    }

    private function normalizeTime($value): string
    {
        try { return Carbon::parse($value)->format('H:i:s'); }
        catch (\Throwable) { return (string)$value; }
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (blank($model->appointment_id)) {
                do { $id = random_int(10000, 99999); }
                while (self::whereKey($id)->exists());
                $model->appointment_id = $id;
            }
            $model->status ??= 'pending';

            // Compose name if caller sent name parts
            $first  = data_get($model->attributes, 'firstname');
            $middle = data_get($model->attributes, 'middlename');
            $last   = data_get($model->attributes, 'lastname');
            if (blank($model->name) && ($first || $middle || $last)) {
                $model->name = trim(collect([$first, $middle, $last])->filter()->implode(' '));
                unset($model->attributes['firstname'], $model->attributes['middlename'], $model->attributes['lastname']);
            }
        });
    }
}
