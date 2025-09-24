@extends('layout')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card modern-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-receipt me-2"></i>
                            Invoice
                        </h4>
                        <div>
                            <button onclick="window.print()" class="btn btn-outline-primary btn-sm me-2">
                                <i class="bi bi-printer me-1"></i> Print
                            </button>
                            <a href="{{ route('visits.show', $visit->_id) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Invoice Header -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="invoice-header">
                                <h5 class="text-primary mb-2">Healthcare Clinic</h5>
                                <p class="mb-1">123 Medical Street</p>
                                <p class="mb-1">Health City, HC 12345</p>
                                <p class="mb-0">Phone: +91 9876543210</p>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="invoice-details">
                                <h6 class="text-muted mb-2">Invoice Details</h6>
                                <p class="mb-1"><strong>Invoice #:</strong> INV-{{ substr($visit->_id, -8) }}</p>
                                <p class="mb-1"><strong>Date:</strong> {{ \Carbon\Carbon::parse($visit->visit_date)->format('M d, Y') }}</p>
                                <p class="mb-0"><strong>Visit ID:</strong> {{ $visit->_id }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Patient & Doctor Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-section">
                                <h6 class="text-primary mb-2">Patient Information</h6>
                                <p class="mb-1"><strong>Name:</strong> {{ $patient->name }}</p>
                                <p class="mb-1"><strong>Phone:</strong> {{ $patient->phone }}</p>
                                <p class="mb-0"><strong>Age:</strong> {{ $visit->age ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-section">
                                <h6 class="text-primary mb-2">Doctor Information</h6>
                                <p class="mb-1"><strong>Doctor:</strong> {{ $visit->doctor_name }}</p>
                                <p class="mb-1"><strong>Specialization:</strong> General Medicine</p>
                                <p class="mb-0"><strong>Visit Date:</strong> {{ \Carbon\Carbon::parse($visit->visit_date)->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Treatment Details -->
                    @if($treatments->count() > 0)
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="table-primary">
                                <tr>
                                    <th>Treatment/Procedure</th>
                                    <th>Description</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Unit Cost</th>
                                    <th class="text-end">Total Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($treatments as $treatment)
                                <tr>
                                    <td>{{ $treatment->treatment_name }}</td>
                                    <td>{{ $treatment->description ?? '-' }}</td>
                                    <td class="text-center">{{ $treatment->quantity }}</td>
                                    <td class="text-end">₹{{ number_format($treatment->cost, 2) }}</td>
                                    <td class="text-end">₹{{ number_format($treatment->total_cost, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    <!-- Payment Summary -->
                    <div class="row">
                        <div class="col-md-6">
                            @if($visit->diagnosis)
                            <div class="info-section mb-3">
                                <h6 class="text-primary mb-2">Diagnosis</h6>
                                <p class="mb-0">{{ $visit->diagnosis }}</p>
                            </div>
                            @endif
                            
                            @if($visit->remarks)
                            <div class="info-section">
                                <h6 class="text-primary mb-2">Remarks</h6>
                                <p class="mb-0">{{ $visit->remarks }}</p>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="payment-summary">
                                <h6 class="text-primary mb-3">Payment Summary</h6>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Total Treatment Cost:</span>
                                    <span>₹{{ number_format($visit->total_treatment_cost ?? 0, 2) }}</span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Amount Paid:</span>
                                    <span class="text-success">₹{{ number_format($visit->paid_amount ?? 0, 2) }}</span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Pending Amount:</span>
                                    <span class="text-warning">₹{{ number_format($visit->pending_amount ?? 0, 2) }}</span>
                                </div>
                                
                                <hr>
                                
                                <div class="d-flex justify-content-between">
                                    <strong>Payment Status:</strong>
                                    <span class="badge bg-{{ ($visit->payment_status ?? 'pending') === 'completed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($visit->payment_status ?? 'pending') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment History -->
                    @if($payments->count() > 0)
                    <div class="mt-4">
                        <h6 class="text-primary mb-3">Payment History</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount Paid</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($payment->created_at)->format('M d, Y') }}</td>
                                        <td>₹{{ number_format($payment->paid_amount, 2) }}</td>
                                        <td>{{ $payment->payment_method ? ucfirst($payment->payment_method) : '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : 'warning' }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $payment->notes ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Footer -->
                    <div class="mt-5 pt-4 border-top">
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
    .card-header .btn,
    .card-header a {
        display: none !important;
    }
    
    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
    
    .modern-card {
        box-shadow: none !important;
    }
}

.invoice-header {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

.invoice-details {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    border-left: 4px solid #28a745;
}

.info-section {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.payment-summary {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    border-left: 4px solid #ffc107;
}

.modern-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.table th {
    background-color: #e9ecef;
    border-color: #dee2e6;
    font-weight: 600;
}

.table-bordered th,
.table-bordered td {
    border-color: #dee2e6;
}
</style>
@endsection
