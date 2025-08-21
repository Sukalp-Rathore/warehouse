<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    //
    use HasFactory;
    protected $collection = 'appointments';
    protected $fillable = [
        'patient_name',
        'patient_contact',
        'date',
        'timeslot',
        'status'
    ];
}
