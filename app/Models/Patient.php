<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    protected $fillable = [
        'medical_record_number',
        'first_name',
        'last_name',
        'birth_date',
        'gender',
        'phone',
        'email',
        'active',
    ];

    public function allergies(): HasMany
    {
        return $this->hasMany(PatientAllergy::class);
    }
}
