<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visit;
use App\Models\Patient;
use App\Models\Inventory;


class VisitController extends Controller
{
    //
    public function index()
    {
        $visits = Visit::all();
        $patients = Patient::all();
        return view('visits', compact('visits', 'patients'));
    }

    public function create($patient_id) {
        $patient = Patient::findOrFail($patient_id);

        // Check for follow-up
        $lastVisit = Visit::where('patient_id', $patient->_id)
            ->orderBy('visit_date', 'desc')
            ->first();

        $isFollowUp = false;
        $visitFee = 500; // default visit fee

        if ($lastVisit && now()->diffInDays($lastVisit->visit_date) <= 15) {
            $isFollowUp = true;
            $visitFee = 0;
        }

        return view('visits.create', compact('patient', 'isFollowUp', 'visitFee'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'patient_id' => 'required',
            'remarks' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'medicines' => 'nullable|array',
            'procedures' => 'nullable|array',
            'injection_given' => 'nullable|boolean',
            'injection_price' => 'nullable|numeric',
            'visit_fee' => 'required|numeric'
        ]);

        $totalCharge = $validated['visit_fee'] + ($validated['injection_price'] ?? 0);

        if (!empty($validated['procedures'])) {
            foreach ($validated['procedures'] as $proc) {
                $totalCharge += $proc['price'];
            }
        }

        $visit = Visit::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => auth()->user()->role === 'doctor' ? auth()->id() : null,
            'receptionist_id' => auth()->user()->role === 'receptionist' ? auth()->id() : null,
            'visit_date' => now(),
            'remarks' => $validated['remarks'] ?? '',
            'diagnosis' => $validated['diagnosis'] ?? '',
            'medicines' => $validated['medicines'] ?? [],
            'procedures' => $validated['procedures'] ?? [],
            'injection_given' => $validated['injection_given'] ?? false,
            'injection_price' => $validated['injection_price'] ?? 0,
            'visit_fee' => $validated['visit_fee'],
            'is_followup' => $validated['visit_fee'] == 0,
            'total_charge' => $totalCharge,
        ]);

        return redirect()->route('visits.show', $visit->_id)->with('success', 'Visit created successfully.');
    }

    public function edit(Request $request)
    {
        $visitId = $request->input('visitId');
        $visit = Visit::findOrFail($visitId);
        $inventory = Inventory::all(); 
        return view('edit-visit', compact('visit' , 'inventory'));
    }

    public function delete(Request $request)
    {
        $visitId = $request->input('visitId');
        $visit = Visit::findOrFail($visitId);
        $visit->delete();

        return response()->json(['success' => true, 'message' => 'Visit deleted successfully.']);
    }
    
}
