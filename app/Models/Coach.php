<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Coach extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'coaches';
    protected $primaryKey = 'coach_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'coach_id','firstname','middlename','lastname','suffix','birthdate',
        'region_code','province_code','city_code','barangay_code',
        'region_name','province_name','city_name','barangay_name',
        'street','postal_code','contact','email','username','password',
        'bio','talents','genres','photo','status','role',
        'service_fee','duration','payment',
        'payment_provider','payment_handle',    
        'notice_hours','notice_days','method',
        'portfolio_path','valid_id_path','id_selfie_path',
        'terms_accepted','email_verification_code','email_verified',
        'account_verified','approved_at','approved_by'
    ];

    protected $casts = [
        'birthdate'        => 'date',
        'terms_accepted'   => 'boolean',
        'email_verified'   => 'boolean',
        'account_verified' => 'boolean',
        'service_fee'      => 'integer',
        'notice_hours'     => 'integer',
        'notice_days'      => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Coach $coach) {
            // Generate 4-digit ID (e.g. "0042")
            if (empty($coach->coach_id)) {
                for ($i = 0; $i < 5 && empty($coach->coach_id); $i++) {
                    $id = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
                    if (!self::whereKey($id)->exists()) {
                        $coach->coach_id = $id;
                    }
                }
                while (empty($coach->coach_id)) {
                    $id = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
                    if (!self::whereKey($id)->exists()) {
                        $coach->coach_id = $id;
                    }
                }
            }

            // Hash password if plain
            if (!empty($coach->password) && !Str::startsWith($coach->password, '$2y$')) {
                $coach->password = Hash::make($coach->password);
            }

            // Email verification token
            if (empty($coach->email_verification_code)) {
                $coach->email_verification_code = Str::random(64);
            }
        });

        static::updating(function (Coach $coach) {
            if ($coach->isDirty('password') && !Str::startsWith($coach->password, '$2y$')) {
                $coach->password = Hash::make($coach->password);
            }
        });
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->firstname.' '.($this->middlename ? $this->middlename.' ' : '').$this->lastname);
    }

    /**
     * Ratings & comments left for this coach.
     */
    public function feedbacks()
    {
        // feedbacks.coach_id -> coaches.coach_id
        return $this->hasMany(Feedback::class, 'coach_id', 'coach_id')->with('user');
    }
}
