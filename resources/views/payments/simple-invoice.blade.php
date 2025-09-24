@extends('layout')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Invoice</h4>
                        <button onclick="window.print()" class="btn btn-light btn-sm">
                            <i class="bi bi-printer me-1"></i> Print
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Header -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="text-primary">Healthcare Clinic</h5>
                            <p class="mb-1">123 Medical Street</p>
                            <p class="mb-1">Health City, HC 12345</p>
                            <p class="mb-0">Phone: +91 9876543210</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h6 class="text-muted">Invoice Details</h6>
                            <p class="mb-1"><strong>Invoice #:</strong> INV-{{ substr($visit->_id, -8) }}</p>
                            <p class="mb-1"><strong>Date:</strong> {{ \Carbon\Carbon::parse($visit->visit_date)->format('M d, Y') }}</p>
                            <p class="mb-0"><strong>Visit ID:</strong> {{ $visit->_id }}</p>
                        </div>
                    </div>

                    <!-- Patient Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-primary">Patient Information</h6>
                            <p class="mb-1"><strong>Name:</strong> {{ $visit->name }}</p>
                            <p class="mb-1"><strong>Phone:</strong> {{ $visit->phone }}</p>
                            <p class="mb-0"><strong>Age:</strong> {{ $visit->age ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Doctor Information</h6>
                            <p class="mb-1"><strong>Doctor:</strong> {{ $visit->doctor_name }}</p>
                            <p class="mb-0"><strong>Visit Date:</strong> {{ \Carbon\Carbon::parse($visit->visit_date)->format('M d, Y') }}</p>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Payment Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Total Treatment Cost:</span>
                                        <span class="fw-bold">₹{{ number_format($visit->total_treatment_cost ?? 0, 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Amount Paid:</span>
                                        <span class="text-success fw-bold">₹{{ number_format($visit->paid_amount ?? 0, 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Pending Amount:</span>
                                        <span class="text-warning fw-bold">₹{{ number_format($visit->pending_amount ?? 0, 2) }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>Payment Status:</strong>
                                        <span class="badge bg-{{ ($visit->payment_status ?? 'pending') === 'completed' ? 'success' : 'warning' }}">
                                            {{ ucfirst($visit->payment_status ?? 'pending') }}
                                        </span>
                                    </div>
                                    @if($visit->diagnosis)
                                    <div class="mt-3">
                                        <h6 class="text-primary">Diagnosis</h6>
                                        <p class="mb-0">{{ $visit->diagnosis }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-muted mb-0">
                                    <small>Thank you for choosing our healthcare services.</small>
                                </p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <p class="text-muted mb-0">
                                    <small>Generated on {{ now()->format('M d, Y \a\t h:i A') }}</small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .card-header .btn {
        display: none !important;
    }
}
</style>
@endsection
