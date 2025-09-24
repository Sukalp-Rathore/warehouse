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
        'is_followup',
        // Treatment fields
        'treatment_details',
        'treatment_duration',
        'treatment_cost',
        'next_visit_date',
        'treatment_notes',
        // Session and Visit Fee fields
        'sessions',
        'visit_fees_added',
        'total_cost',
        // Treatment completion fields
        'treatment_status',
        'treatment_completion_date',
        'treatment_completion_notes',
        // Payment fields (embedded)
        'total_paid',
        'remaining_balance',
        'payment_status',
        'payments'
    ];
}
