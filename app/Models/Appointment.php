<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    //
    protected $collection = 'appointments';
    protected $fillable = [
        'patient_name',
        'appointment_date',
        'insurer',
        'policy_number',
        'visit_cost',
        'status'
    ];
}
