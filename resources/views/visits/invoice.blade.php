@extends('layout')

@section('content')
<style>
    .invoice-container {
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .invoice-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        text-align: center;
    }
    
    .invoice-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .invoice-subtitle {
        font-size: 1.2rem;
        opacity: 0.9;
    }
    
    .invoice-body {
        padding: 2rem;
    }
    
    .invoice-section {
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 15px;
        border-left: 5px solid #667eea;
    }
    
    .invoice-section h5 {
        color: #667eea;
        font-weight: 600;
        margin-bottom: 1rem;
    }
    
    .payment-summary {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-radius: 15px;
        padding: 1.5rem;
        margin: 1rem 0;
    }
    
    .payment-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #e9ecef;
    }
    
    .payment-item:last-child {
        border-bottom: none;
        font-weight: 600;
        font-size: 1.1rem;
    }
    
    .amount {
        font-weight: 600;
        color: #28a745;
    }
    
    .amount.pending {
        color: #ffc107;
    }
    
    .amount.total {
        color: #667eea;
        font-size: 1.2rem;
    }
    
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .status-completed {
        background: #d4edda;
        color: #155724;
    }
    
    .status-partial {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-pending {
        background: #f8d7da;
        color: #721c24;
    }
    
    .print-button {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
        padding: 1rem 2rem;
        border-radius: 25px;
        font-weight: 600;
        box-shadow: 0 8px 15px rgba(40, 167, 69, 0.3);
        transition: all 0.3s ease;
    }
    
    .print-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 20px rgba(40, 167, 69, 0.4);
    }
    
    @media print {
        .print-button {
            display: none;
        }
        
        .invoice-container {
            box-shadow: none;
            border-radius: 0;
        }
        
        .invoice-header {
            background: #667eea !important;
            -webkit-print-color-adjust: exact;
        }
    }
</style>

<button onclick="window.print()" class="print-button">
    <i class="bi bi-printer me-2"></i> Print Invoice
</button>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="invoice-container">
                <!-- Invoice Header -->
                <div class="invoice-header">
                    <h1 class="invoice-title">INVOICE</h1>
                    <p class="invoice-subtitle">Healthcare Services</p>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5>Invoice Details</h5>
                            <p class="mb-1"><strong>Invoice #:</strong> INV-{{ substr($visit->_id, -8) }}</p>
                            <p class="mb-1"><strong>Date:</strong> {{ \Carbon\Carbon::parse($visit->visit_date)->format('M d, Y') }}</p>
                            <p class="mb-0"><strong>Visit ID:</strong> {{ $visit->_id }}</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Clinic Information</h5>
                            <p class="mb-1"><strong>Healthcare Clinic</strong></p>
                            <p class="mb-1">123 Medical Street, Health City</p>
                            <p class="mb-1">Phone: +91 9876543210</p>
                            <p class="mb-0">Email: info@healthclinic.com</p>
                        </div>
                    </div>
                </div>

                <!-- Invoice Body -->
                <div class="invoice-body">
                    <!-- Patient Information -->
                    <div class="invoice-section">
                        <h5><i class="bi bi-person me-2"></i>Patient Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Name:</strong> {{ $visit->name }}</p>
                                <p class="mb-1"><strong>Phone:</strong> {{ $visit->phone }}</p>
                                <p class="mb-0"><strong>Age:</strong> {{ $visit->age ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Gender:</strong> {{ $visit->gender ?? 'N/A' }}</p>
                                <p class="mb-1"><strong>Address:</strong> {{ $visit->address ?? 'N/A' }}</p>
                                <p class="mb-0"><strong>Visit Date:</strong> {{ \Carbon\Carbon::parse($visit->visit_date)->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Doctor Information -->
                    <div class="invoice-section">
                        <h5><i class="bi bi-person-badge me-2"></i>Doctor Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Doctor:</strong> {{ $visit->doctor_name }}</p>
                                <p class="mb-0"><strong>Specialization:</strong> General Medicine</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Visit Type:</strong> {{ $visit->is_followup ? 'Follow-up' : 'New Visit' }}</p>
                                <p class="mb-0"><strong>Status:</strong> {{ ucfirst($visit->status ?? 'Completed') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Treatment Details -->
                    @if($visit->treatment_details)
                    <div class="invoice-section">
                        <h5><i class="bi bi-clipboard-pulse me-2"></i>Treatment Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Treatment:</strong> {{ $visit->treatment_details }}</p>
                                <p class="mb-1"><strong>Duration:</strong> {{ $visit->treatment_duration ?? 'N/A' }}</p>
                                <p class="mb-0"><strong>Cost:</strong> ₹{{ number_format($visit->treatment_cost ?? 0, 2) }}</p>
                            </div>
                            <div class="col-md-6">
                                @if($visit->next_visit_date)
                                <p class="mb-1"><strong>Next Visit:</strong> {{ \Carbon\Carbon::parse($visit->next_visit_date)->format('M d, Y') }}</p>
                                @endif
                                @if($visit->treatment_notes)
                                <p class="mb-1"><strong>Notes:</strong> {{ $visit->treatment_notes }}</p>
                                @endif
                                @if($visit->diagnosis)
                                <p class="mb-0"><strong>Diagnosis:</strong> {{ $visit->diagnosis }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Payment Summary -->
                    <div class="payment-summary">
                        <h5 class="text-center mb-4"><i class="bi bi-credit-card me-2"></i>Payment Summary</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="payment-item">
                                    <span>Total Treatment Cost:</span>
                                    <span class="amount total">₹{{ number_format($visit->treatment_cost ?? 0, 2) }}</span>
                                </div>
                                <div class="payment-item">
                                    <span>Amount Paid:</span>
                                    <span class="amount">₹{{ number_format($visit->total_paid ?? 0, 2) }}</span>
                                </div>
                                <div class="payment-item">
                                    <span>Remaining Balance:</span>
                                    <span class="amount {{ ($visit->remaining_balance ?? 0) > 0 ? 'pending' : 'amount' }}">₹{{ number_format($visit->remaining_balance ?? ($visit->treatment_cost ?? 0), 2) }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 text-center">
                                <div class="mb-3">
                                    <span class="status-badge status-{{ $visit->payment_status ?? 'pending' }}">
                                        {{ ucfirst($visit->payment_status ?? 'pending') }}
                                    </span>
                                </div>
                                @if(isset($visit->payments) && count($visit->payments) > 0)
                                <h6>Payment History</h6>
                                <div class="small">
                                    @foreach($visit->payments as $payment)
                                    <div class="d-flex justify-content-between">
                                        <span>{{ \Carbon\Carbon::parse($payment['payment_date'])->format('M d, Y') }}</span>
                                        <span>₹{{ number_format($payment['amount_paid'], 2) }} ({{ $payment['payment_mode'] }})</span>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="text-center mt-4 pt-4 border-top">
                        <p class="text-muted mb-0">
                            <small>Thank you for choosing our healthcare services. For any queries, please contact us.</small>
                        </p>
                        <p class="text-muted mb-0">
                            <small>Generated on {{ now()->format('M d, Y \a\t h:i A') }}</small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-print when print button is clicked
document.querySelector('.print-button').addEventListener('click', function() {
    window.print();
});
</script>
@endsection
