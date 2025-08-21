<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Visit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class AppointmentController extends Controller
{
    //
    // public function show(Request $request)
    // {
    //     $appointments = Appointment::get();
    //     return view('appointments', compact('appointments'));
    // }

    public function index(Request $request)
    {
        $patients = Patient::get();
        return view('add-appointment' , compact('patients'));
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'date' => 'date|required',
            'timeslot' => 'required'
        ]);
        $doctorName = Session::get('user_name');
        $validated['doctor_name'] = $doctorName;
        $patient = Patient::where('name' , $request->name)->first();
        if($patient){
            $validated['age'] = $patient->age;
            $validated['gender'] = $patient->gender;
            $validated['address'] = $patient->address;

            $lastVisit = Visit::where('patient_id', $patient->name)
                ->orderBy('visit_date', 'desc')
                ->first();

            $isFollowUp = false;
            $visitFee = 500; // default visit fee

            if ($lastVisit && now()->diffInDays($lastVisit->visit_date) <= 15) {
                $isFollowUp = true;
                $visitFee = 0;
            }
            $validated['is_followup'] = $isFollowUp;
            $validated['visit_fee'] = $visitFee;
        }
        else{
            $patientCreate = Patient::create([
                'name' => $request->name ,
                'phone' => $request->phone
            ]);
        }
        
        $appointment = Visit::create($validated);

        // $toAndComponents = [];
        // $phone = preg_replace('/\D/', '', $request->patient_contact); // Remove non-numeric characters
        // $formattedPhone = strlen($phone) === 10 ? '91' . $phone : $phone; // Add '91' if length is 10
        // $toAndComponents[] = [
        //     "to" => [$formattedPhone],
        //     "components" => [
        //         "body_1" => [
        //             "type" => "text",
        //             "value" => $request->patient_name // Dynamically send patient_name
        //         ],
        //         "body_2" => [
        //             "type" => "text",
        //             "value" => $request->date // Dynamically send date
        //         ],
        //         "body_3" => [
        //             "type" => "text",
        //             "value" => $request->timeslot // Dynamically send timeslot
        //         ]
        //     ]
        // ];
        // // dd("add new number");
        // $payload = [
        //     "integrated_number" => "919999999999",
        //     "content_type" => "template",
        //     "payload" => [
        //         "messaging_product" => "whatsapp",
        //         "type" => "template",
        //         "template" => [
        //             "name" => "appointment_confirmation",
        //             "language" => [
        //                 "code" => "en",
        //                 "policy" => "deterministic"
        //             ],
        //             "namespace" => "a6f0d3b7_77f1_463a_94dd_a8c9f5054401",
        //             "to_and_components" => $toAndComponents
        //         ]
        //     ]
        // ];

        // $response = Http::withHeaders([
        //     'Content-Type' => 'application/json',
        //     'authkey' => '451815AXQYneFUH686786fbP1' // Replace with your actual authkey
        // ])->post('https://api.msg91.com/api/v5/whatsapp/whatsapp-outbound-message/bulk/', $payload);
        
        return response()->json([
            'success' => true,
            'message' => 'Appointment created successfully.'
        ]);
    }
}
