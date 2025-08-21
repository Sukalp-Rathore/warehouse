<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;

class PatientController extends Controller
{
    //
    public function index()
    {
        $patients = Patient::all();
        return view('patients', compact('patients'));
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'gender' => 'required',
            'age' => 'required',
            'address' => 'nullable|string',
            'history' => 'nullable|string'
        ]);

        // Check if patient exists
        $existing = Patient::where('phone', $validated['phone'])->first();
        if ($existing) {
            return response()->json([
                'error' => 'Patient with this phone number already exists.'
            ], 400);
        }

        $validated['created_by'] = auth()->id();

        $patient = Patient::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Patient created successfully.',
            'patient' => $patient
        ]);
    }

    public function edit(Request $request)
    {
        $patient = Patient::find($request->patientId);
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found.'
            ], 404);
        }
        return view('edit-patient', compact('patient'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:patients,_id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'gender' => 'required',
            'age' => 'required',
            'address' => 'nullable|string',
            'history' => 'nullable|string'
        ]);

        $patient = Patient::find($validated['id']);
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found.'
            ], 404);
        }

        $patient->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Patient updated successfully.',
            'patient' => $patient
        ]);
    }
    public function delete(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:patients,_id'
        ]);

        $patient = Patient::find($validated['id']);
        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found.'
            ], 404);
        }

        $patient->delete();

        return response()->json([
            'success' => true,
            'message' => 'Patient deleted successfully.'
        ]);
    }
}