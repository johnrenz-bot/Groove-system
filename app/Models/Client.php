<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Client extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'client_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'client_id','role',
        'firstname','middlename','lastname','birthdate',
        'region_code','province_code','city_code','barangay_code',
        'region_name','province_name','city_name','barangay_name',
        'street','postal_code',
        'address','barangay',
        'contact','talent','email','username','password',
        'status','photo','bio',
        'terms_accepted',
        'email_verification_code','email_verified',
        'account_verified','approved_at','approved_by',
        'remember_token',
        'valid_id_path',
    ];

    protected $casts = [
        'birthdate'         => 'date',
        'terms_accepted'    => 'boolean',
        'email_verified'    => 'boolean',
        'account_verified'  => 'boolean',
        'approved_at'       => 'datetime',
            'birthdate'   => 'date',

    ];

    protected $hidden = ['password', 'remember_token'];

    public function getFullNameAttribute(): string
    {
        $parts = array_filter([$this->firstname, $this->middlename, $this->lastname]);
        return implode(' ', $parts);
    }

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->street,
            $this->barangay_name,
            $this->city_name,
            $this->province_name,
            $this->region_name,
            $this->postal_code ? 'PH '.$this->postal_code : null,
        ])->filter()->implode(', ');
    }

    public function getValidIdUrlAttribute(): ?string
    {
        return $this->valid_id_path ? asset('storage/'.$this->valid_id_path) : null;
    }

    public function approver()
    {
        // optional relation to Admin model
        return $this->belongsTo(\App\Models\Admin::class, 'approved_by');
    }
}
