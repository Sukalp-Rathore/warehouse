@extends('layout')

@section('content')
<style>
    .modern-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .modern-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }
    
    .card-header {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        color: white;
        border-radius: 20px 20px 0 0 !important;
        border: none;
    }
    
    .form-control, .form-select {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #ffc107;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }
    
    .btn {
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
    }
    
    .info-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 1.5rem;
        border-left: 5px solid #ffc107;
    }
    
    .payment-summary {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-radius: 15px;
        padding: 1.5rem;
    }
    
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
        transition: all 0.2s ease;
    }
</style>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card modern-card">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="bi bi-credit-card me-2"></i>
                        Payment Management
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Patient & Treatment Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-primary">Patient: {{ $visit->name }}</h6>
                            <p class="text-muted">Phone: {{ $visit->phone }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Treatment: {{ $visit->treatment_details ?? 'Not specified' }}</h6>
                            <p class="text-muted">Cost: ₹{{ number_format($visit->treatment_cost ?? 0, 2) }}</p>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Payment Summary</h6>
                        </div>
                        <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h6 class="text-muted">Base Treatment</h6>
                            <h4 class="text-primary">₹{{ number_format($visit->treatment_cost ?? 0, 2) }}</h4>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted">Visit Fees</h6>
                            <h4 class="text-warning">₹{{ number_format($visit->visit_fees_added ?? 0, 2) }}</h4>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted">Total Paid</h6>
                            <h4 class="text-success">₹{{ number_format($visit->total_paid ?? 0, 2) }}</h4>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted">Remaining</h6>
                            <h4 class="text-danger">₹{{ number_format($visit->remaining_balance ?? ($visit->total_cost ?? $visit->treatment_cost ?? 0), 2) }}</h4>
                        </div>
                    </div>
                            <div class="row mt-3">
                                <div class="col-12 text-center">
                                    <span class="badge bg-{{ ($visit->payment_status ?? 'pending') === 'completed' ? 'success' : 'warning' }} fs-6">
                                        Status: {{ ucfirst($visit->payment_status ?? 'pending') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add Payment Form -->
                    @if(($visit->treatment_cost ?? 0) > 0 && (($visit->remaining_balance ?? ($visit->total_cost ?? $visit->treatment_cost)) > 0))
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Add Payment</h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('visits.payment.add', $visit->_id) }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="amount_paid" class="form-control" 
                               step="0.01" min="0.01" max="{{ $visit->remaining_balance ?? ($visit->total_cost ?? $visit->treatment_cost ?? 0) }}" 
                               placeholder="Enter amount" required>
                        <small class="text-muted">Max: ₹{{ number_format($visit->remaining_balance ?? ($visit->total_cost ?? $visit->treatment_cost ?? 0), 2) }}</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                                        <select name="payment_mode" class="form-select" required>
                                            <option value="">Select Mode</option>
                                            <option value="Cash">Cash</option>
                                            <option value="Card">Card</option>
                                            <option value="UPI">UPI</option>
                                            <option value="Cheque">Cheque</option>
                                            <option value="Online">Online Transfer</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Notes</label>
                                        <input type="text" name="notes" class="form-control" 
                                               placeholder="Optional notes...">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-credit-card me-1"></i> Add Payment
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endif

                    <!-- Payment History -->
                    @if(isset($visit->payments) && count($visit->payments) > 0)
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Payment History</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Mode</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(array_reverse($visit->payments) as $payment)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($payment['payment_date'])->format('M d, Y h:i A') }}</td>
                                            <td class="text-success">₹{{ number_format($payment['amount_paid'], 2) }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $payment['payment_mode'] }}</span>
                                            </td>
                                            <td>{{ $payment['notes'] ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="card">
                        <div class="card-body text-center text-muted">
                            <i class="bi bi-credit-card display-4 d-block mb-2"></i>
                            <p>No payments recorded yet</p>
                        </div>
                    </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('visits') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Back to Visits
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
