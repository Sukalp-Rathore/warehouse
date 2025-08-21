<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Visit extends Model
{
    //
    protected $collection = 'visits';

    protected $fillable = [
        'patient_id',
        'name',
        'phone',
        'age',
        'gender',
        'address',
        'doctor_name',
        'doctor_id',
        'receptionist_id',
        'visit_date',
        'remarks',
        'diagnosis',
        'medicines',
        'procedures',
        'injection_given',
        'injection_price',
        'visit_fee',
        'total_charge',
        'is_followup'
    ];
}
