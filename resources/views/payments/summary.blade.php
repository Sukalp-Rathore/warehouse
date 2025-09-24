@extends('layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="bi bi-graph-up me-2"></i>
                        Payment Summary - {{ $patient->name }}
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="summary-card bg-primary">
                                <div class="summary-icon">
                                    <i class="bi bi-currency-rupee"></i>
                                </div>
                                <div class="summary-content">
                                    <h3>₹{{ number_format($totalAmount, 2) }}</h3>
                                    <p>Total Amount</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card bg-success">
                                <div class="summary-icon">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <div class="summary-content">
                                    <h3>₹{{ number_format($totalPaid, 2) }}</h3>
                                    <p>Total Paid</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card bg-warning">
                                <div class="summary-icon">
                                    <i class="bi bi-clock"></i>
                                </div>
                                <div class="summary-content">
                                    <h3>₹{{ number_format($totalPending, 2) }}</h3>
                                    <p>Pending Amount</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card bg-info">
                                <div class="summary-icon">
                                    <i class="bi bi-percent"></i>
                                </div>
                                <div class="summary-content">
                                    <h3>{{ $totalAmount > 0 ? number_format(($totalPaid / $totalAmount) * 100, 1) : 0 }}%</h3>
                                    <p>Payment Progress</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Patient Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-card">
                                <h6 class="info-title">Patient Details</h6>
                                <p class="mb-1"><strong>Name:</strong> {{ $patient->name }}</p>
                                <p class="mb-1"><strong>Phone:</strong> {{ $patient->phone }}</p>
                                <p class="mb-0"><strong>Total Visits:</strong> {{ $payments->count() }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <h6 class="info-title">Last Payment</h6>
                                @if($lastPayment)
                                    <p class="mb-1"><strong>Date:</strong> {{ \Carbon\Carbon::parse($lastPayment->created_at)->format('M d, Y') }}</p>
                                    <p class="mb-1"><strong>Amount:</strong> ₹{{ number_format($lastPayment->paid_amount, 2) }}</p>
                                    <p class="mb-0"><strong>Status:</strong> 
                                        <span class="badge bg-{{ $lastPayment->status === 'completed' ? 'success' : 'warning' }}">
                                            {{ ucfirst($lastPayment->status) }}
                                        </span>
                                    </p>
                                @else
                                    <p class="mb-0">No payments recorded</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Payment History Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Payment History</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Visit ID</th>
                                            <th>Doctor</th>
                                            <th>Total Amount</th>
                                            <th>Paid Amount</th>
                                            <th>Pending</th>
                                            <th>Method</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($payments as $payment)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($payment->created_at)->format('M d, Y') }}</td>
                                            <td>
                                                <a href="{{ route('visits.show', $payment->visit_id) }}" class="text-decoration-none">
                                                    {{ substr($payment->visit_id, -8) }}
                                                </a>
                                            </td>
                                            <td>{{ $payment->doctor->name ?? 'N/A' }}</td>
                                            <td>₹{{ number_format($payment->total_amount, 2) }}</td>
                                            <td class="text-success">₹{{ number_format($payment->paid_amount, 2) }}</td>
                                            <td class="text-warning">₹{{ number_format($payment->pending_amount, 2) }}</td>
                                            <td>
                                                @if($payment->payment_method)
                                                    <span class="badge bg-secondary">{{ ucfirst($payment->payment_method) }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($payment->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('payments.invoice', $payment->visit_id) }}" 
                                                       class="btn btn-outline-primary btn-sm" title="View Invoice">
                                                        <i class="bi bi-receipt"></i>
                                                    </a>
                                                    @if($payment->status === 'partial')
                                                    <button class="btn btn-outline-success btn-sm add-payment-btn" 
                                                            data-visit-id="{{ $payment->visit_id }}" 
                                                            data-pending="{{ $payment->pending_amount }}" title="Add Payment">
                                                        <i class="bi bi-plus-circle"></i>
                                                    </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                                No payment records found
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Additional Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPaymentForm">
                <div class="modal-body">
                    <input type="hidden" id="visit-id" name="visit_id">
                    <div class="mb-3">
                        <label class="form-label">Pending Amount</label>
                        <input type="number" id="pending-amount" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Additional Amount <span class="text-danger">*</span></label>
                        <input type="number" id="additional-amount" name="additional_amount" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="">Select Method</option>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="upi">UPI</option>
                            <option value="cheque">Cheque</option>
                            <option value="online">Online Transfer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.summary-card {
    background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-primary-dark) 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 15px;
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease;
}

.summary-card:hover {
    transform: translateY(-2px);
}

.summary-card.bg-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.summary-card.bg-warning {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
}

.summary-card.bg-info {
    background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
}

.summary-icon {
    font-size: 2rem;
    margin-right: 1rem;
    opacity: 0.8;
}

.summary-content h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
}

.summary-content p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.9rem;
}

.info-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1rem;
}

.info-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #fff;
}

.modern-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease;
}

.modern-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add payment button click
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-payment-btn') || e.target.closest('.add-payment-btn')) {
            const btn = e.target.closest('.add-payment-btn');
            const visitId = btn.dataset.visitId;
            const pendingAmount = btn.dataset.pending;
            
            document.getElementById('visit-id').value = visitId;
            document.getElementById('pending-amount').value = pendingAmount;
            document.getElementById('additional-amount').max = pendingAmount;
            
            new bootstrap.Modal(document.getElementById('addPaymentModal')).show();
        }
    });
    
    // Add payment form submission
    document.getElementById('addPaymentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const visitId = formData.get('visit_id');
        const additionalAmount = parseFloat(formData.get('additional_amount'));
        const pendingAmount = parseFloat(document.getElementById('pending-amount').value);
        
        if (additionalAmount > pendingAmount) {
            alert('Additional amount cannot exceed pending amount');
            return;
        }
        
        // Submit the form
        fetch(`/visits/${visitId}/add-payment`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing the payment');
        });
    });
});
</script>
@endsection
