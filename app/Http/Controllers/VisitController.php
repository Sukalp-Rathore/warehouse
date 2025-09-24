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

    public function showTreatment($id)
    {
        $visit = Visit::findOrFail($id);
        return view('visits.treatment', compact('visit'));
    }

    public function updateTreatment(Request $request, $id)
    {
        $request->validate([
            'treatment_details' => 'required|string',
            'treatment_duration' => 'required|string',
            'treatment_cost' => 'required|numeric|min:0',
            'next_visit_date' => 'nullable|date',
            'treatment_notes' => 'nullable|string'
        ]);

        $visit = Visit::findOrFail($id);
        $visit->update([
            'treatment_details' => $request->treatment_details,
            'treatment_duration' => $request->treatment_duration,
            'treatment_cost' => $request->treatment_cost,
            'next_visit_date' => $request->next_visit_date,
            'treatment_notes' => $request->treatment_notes
        ]);

        return redirect()->route('visits')->with('success', 'Treatment details saved successfully!');
    }

    public function showPayments($id)
    {
        $visit = Visit::findOrFail($id);
        return view('visits.payments', compact('visit'));
    }

    public function addPayment(Request $request, $id)
    {
        $request->validate([
            'amount_paid' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        try {
            $visit = Visit::findOrFail($id);
            $treatmentCost = $visit->treatment_cost ?? 0;
            $currentTotalPaid = $visit->total_paid ?? 0;
            $newTotalPaid = $currentTotalPaid + $request->amount_paid;
            $remainingBalance = $treatmentCost - $newTotalPaid;
            
            // If this is the first payment, initialize remaining_balance
            if (!isset($visit->remaining_balance)) {
                $remainingBalance = $treatmentCost - $request->amount_paid;
            }

            // Validate payment doesn't exceed treatment cost
            if ($newTotalPaid > $treatmentCost) {
                return redirect()->back()->with('error', 'Payment amount exceeds treatment cost. Maximum allowed: ₹' . ($treatmentCost - $currentTotalPaid));
            }

            // Get existing payments array
            $payments = $visit->payments ?? [];
            
            // Add new payment
            $payments[] = [
                'amount_paid' => $request->amount_paid,
                'payment_mode' => $request->payment_mode,
                'payment_date' => now()->toISOString(),
                'notes' => $request->notes ?? ''
            ];

            // Update visit with payment totals
        $visit->update([
                'total_paid' => $newTotalPaid,
                'remaining_balance' => $remainingBalance,
                'payment_status' => $remainingBalance <= 0 ? 'completed' : 'partial',
                'payments' => $payments
            ]);

            return redirect()->back()->with('success', 'Payment recorded successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function invoice($id)
    {
        $visit = Visit::findOrFail($id);
        return view('visits.invoice', compact('visit'));
    }

    public function show($id)
    {
        $visit = Visit::findOrFail($id);
        return view('visits.show', compact('visit'));
    }

    public function addSession(Request $request, $id)
    {
        $request->validate([
            'session_date' => 'required|date',
            'session_time' => 'required',
            'session_notes' => 'required|string',
            'next_session_date' => 'nullable|date'
        ]);

        try {
            $visit = Visit::findOrFail($id);
            
            // Get existing sessions
            $sessions = $visit->sessions ?? [];
            $lastSession = end($sessions);
            
            // Calculate days between sessions
            $daysBetween = 0;
            $visitFeeAdded = false;
            $visitFeeAmount = 0;
            $visitFeeReason = '';
            
            if ($lastSession && isset($lastSession['session_date'])) {
                $lastDate = \Carbon\Carbon::parse($lastSession['session_date']);
                $newDate = \Carbon\Carbon::parse($request->session_date);
                $daysBetween = $lastDate->diffInDays($newDate);
                
                // If gap is more than 15 days, add visit fee
                if ($daysBetween > 15) {
                    $visitFeeAdded = true;
                    $visitFeeAmount = 200; // Fixed visit fee amount
                    $visitFeeReason = "Gap of {$daysBetween} days between sessions";
                }
            }
            
            // Create new session
            $newSession = [
                'session_id' => uniqid(),
                'session_number' => count($sessions) + 1,
                'session_date' => $request->session_date,
                'session_time' => $request->session_time,
                'session_notes' => $request->session_notes,
                'session_status' => 'completed',
                'next_session_date' => $request->next_session_date,
                'visit_fee_added' => $visitFeeAdded,
                'visit_fee_amount' => $visitFeeAmount,
                'visit_fee_reason' => $visitFeeReason,
                'created_at' => now()->toISOString()
            ];
            
            // Add session to array
            $sessions[] = $newSession;
            
            // Calculate total visit fees
            $totalVisitFees = array_sum(array_column($sessions, 'visit_fee_amount'));
            
            // Update visit
            $visit->update([
                'sessions' => $sessions,
                'visit_fees_added' => $totalVisitFees,
                'total_cost' => ($visit->treatment_cost ?? 0) + $totalVisitFees,
                'remaining_balance' => (($visit->treatment_cost ?? 0) + $totalVisitFees) - ($visit->total_paid ?? 0)
            ]);
            
            // Show notification if visit fee was added
            if ($visitFeeAdded) {
                return redirect()->back()->with('success', 
                    "Session added successfully! Visit fee of ₹{$visitFeeAmount} added due to {$daysBetween} day gap between sessions.");
            }
            
            return redirect()->back()->with('success', 'Session added successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function completeTreatment(Request $request, $id)
    {
        $request->validate([
            'treatment_completion_date' => 'required|date',
            'treatment_completion_notes' => 'nullable|string'
        ]);

        try {
            $visit = Visit::findOrFail($id);
            
            $visit->update([
                'treatment_status' => 'completed',
                'treatment_completion_date' => $request->treatment_completion_date,
                'treatment_completion_notes' => $request->treatment_completion_notes
            ]);

            return redirect()->back()->with('success', 'Treatment marked as completed successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function delete(Request $request)
    {
        $visitId = $request->input('visitId');
        $visit = Visit::findOrFail($visitId);
        $visit->delete();

        return response()->json(['success' => true, 'message' => 'Visit deleted successfully.']);
    }
    
}
