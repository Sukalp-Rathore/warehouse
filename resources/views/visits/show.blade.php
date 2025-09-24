@extends('layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card modern-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="bi bi-person-lines-fill me-2"></i>
                            Visit Details - {{ $visit->name }}
                        </h4>
                        <div>
                            <a href="{{ route('visits.payments', $visit->_id) }}" class="btn btn-warning btn-sm me-2">
                                <i class="bi bi-credit-card me-1"></i> Payments
                            </a>
                            <a href="{{ route('visits.invoice', $visit->_id) }}" class="btn btn-info btn-sm me-2" target="_blank">
                                <i class="bi bi-receipt me-1"></i> Invoice
                            </a>
                            <a href="{{ route('visits') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Patient & Visit Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-card">
                                <h6 class="info-title">Patient Information</h6>
                                <p class="mb-1"><strong>Name:</strong> {{ $visit->name }}</p>
                                <p class="mb-1"><strong>Phone:</strong> {{ $visit->phone }}</p>
                                <p class="mb-1"><strong>Age:</strong> {{ $visit->age ?? 'N/A' }}</p>
                                <p class="mb-0"><strong>Gender:</strong> {{ $visit->gender ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <h6 class="info-title">Visit Information</h6>
                                <p class="mb-1"><strong>Doctor:</strong> {{ $visit->doctor_name }}</p>
                                <p class="mb-1"><strong>Date:</strong> {{ \Carbon\Carbon::parse($visit->visit_date)->format('M d, Y') }}</p>
                                <p class="mb-1"><strong>Status:</strong> 
                                    <span class="badge bg-{{ $visit->status === 'completed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($visit->status ?? 'pending') }}
                                    </span>
                                </p>
                                <p class="mb-0"><strong>Visit ID:</strong> {{ $visit->_id }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Treatment & Payment Summary -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card payment-summary-card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="bi bi-clipboard-pulse me-2"></i>
                                        Treatment & Payment Summary
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="payment-item">
                                                <h6 class="text-muted">Base Treatment</h6>
                                                <h4 class="text-primary">₹{{ number_format($visit->treatment_cost ?? 0, 2) }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="payment-item">
                                                <h6 class="text-muted">Visit Fees</h6>
                                                <h4 class="text-warning">₹{{ number_format($visit->visit_fees_added ?? 0, 2) }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="payment-item">
                                                <h6 class="text-muted">Total Paid</h6>
                                                <h4 class="text-success">₹{{ number_format($visit->total_paid ?? 0, 2) }}</h4>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="payment-item">
                                                <h6 class="text-muted">Remaining</h6>
                                                <h4 class="text-danger">₹{{ number_format($visit->remaining_balance ?? ($visit->total_cost ?? $visit->treatment_cost ?? 0), 2) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Medical Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            @if($visit->diagnosis)
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Diagnosis</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $visit->diagnosis }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($visit->remarks)
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Remarks</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $visit->remarks }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Sessions Management -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="bi bi-calendar-check me-2"></i>
                                        Treatment Sessions ({{ count($visit->sessions ?? []) }})
                                    </h5>
                                    @if($visit->treatment_status !== 'completed')
                                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addSessionModal">
                                            <i class="bi bi-plus-circle me-1"></i> Add Session
                                        </button>
                                    @else
                                        <span class="badge bg-success fs-6">
                                            <i class="bi bi-check-circle me-1"></i> Treatment Completed
                                        </span>
                                    @endif
                                </div>
                                <div class="card-body">
                                    @if(isset($visit->sessions) && count($visit->sessions) > 0)
                                        <div class="row">
                                            @foreach($visit->sessions as $session)
                                            <div class="col-md-6 mb-3">
                                                <div class="session-card {{ $session['visit_fee_added'] ? 'visit-fee' : '' }} {{ $session['session_status'] }}">
                                                    <div class="p-3">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <div>
                                                                <h6 class="mb-1">
                                                                    Session #{{ $session['session_number'] }}
                                                                    @if($session['visit_fee_added'])
                                                                    <span class="visit-fee-badge ms-2">💰 Visit Fee: ₹{{ $session['visit_fee_amount'] }}</span>
                                                                    @endif
                                                                </h6>
                                                                <span class="status-badge status-{{ $session['session_status'] }}">
                                                                    {{ ucfirst($session['session_status']) }}
                                                                </span>
                                                            </div>
                                                            <small class="text-muted">
                                                                {{ \Carbon\Carbon::parse($session['created_at'])->format('M d, Y') }}
                                                            </small>
                                                        </div>
                                                        
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <p class="mb-1"><strong>Date:</strong> {{ \Carbon\Carbon::parse($session['session_date'])->format('M d, Y') }}</p>
                                                                <p class="mb-1"><strong>Time:</strong> {{ $session['session_time'] }}</p>
                                                                @if($session['next_session_date'])
                                                                <p class="mb-0"><strong>Next:</strong> {{ \Carbon\Carbon::parse($session['next_session_date'])->format('M d, Y') }}</p>
                                                                @endif
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p class="mb-1"><strong>Notes:</strong></p>
                                                                <p class="mb-0 text-muted">{{ $session['session_notes'] }}</p>
                                                                @if($session['visit_fee_added'])
                                                                <small class="text-warning">
                                                                    <i class="bi bi-info-circle"></i> {{ $session['visit_fee_reason'] }}
                                                                </small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-4">
                                            <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                                            <h5 class="text-muted mt-3">No sessions added yet</h5>
                                            <p class="text-muted">Add the first session to start tracking progress</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Treatment Management -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="bi bi-clipboard-pulse me-2"></i>
                                        Treatment Management
                                    </h5>
                                    <div>
                                        @if($visit->treatment_status === 'completed')
                                            <span class="badge bg-success fs-6">
                                                <i class="bi bi-check-circle me-1"></i> Completed
                                            </span>
                                        @elseif($visit->treatment_status === 'in_progress')
                                            <span class="badge bg-warning fs-6">
                                                <i class="bi bi-clock me-1"></i> In Progress
                                            </span>
                                        @else
                                            <span class="badge bg-secondary fs-6">
                                                <i class="bi bi-pause me-1"></i> Pending
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Treatment Form -->
                                    <form method="POST" action="{{ route('visits.treatment.update', $visit->_id) }}">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Treatment Details <span class="text-danger">*</span></label>
                                                    <textarea name="treatment_details" class="form-control" rows="3" 
                                                              placeholder="Describe the treatment..." required>{{ $visit->treatment_details ?? '' }}</textarea>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Treatment Duration</label>
                                                    <input type="text" name="treatment_duration" class="form-control" 
                                                           value="{{ $visit->treatment_duration ?? '' }}" 
                                                           placeholder="e.g., 30 mins, 1 hour, Multiple sessions">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Treatment Cost (₹) <span class="text-danger">*</span></label>
                                                    <input type="number" name="treatment_cost" class="form-control" 
                                                           value="{{ $visit->treatment_cost ?? '' }}" 
                                                           step="0.01" min="0" placeholder="Enter treatment cost" required>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Next Visit Date</label>
                                                    <input type="date" name="next_visit_date" class="form-control" 
                                                           value="{{ $visit->next_visit_date ?? '' }}">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Treatment Notes</label>
                                                    <textarea name="treatment_notes" class="form-control" rows="3" 
                                                              placeholder="Additional notes about the treatment...">{{ $visit->treatment_notes ?? '' }}</textarea>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="bi bi-check-circle me-1"></i> Save Treatment
                                                    </button>
                                                    @if($visit->treatment_status !== 'completed' && $visit->treatment_details)
                                                    <button type="button" class="btn btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#completeTreatmentModal">
                                                        <i class="bi bi-flag-fill me-1"></i> Mark as Completed
                                                    </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    
                                    <!-- Treatment Summary -->
                                    @if($visit->treatment_details)
                                    <div class="mt-4 pt-4 border-top">
                                        @if($visit->treatment_status === 'completed')
                                            <h6 class="text-success mb-3">
                                                <i class="bi bi-check-circle me-2"></i>Treatment Completed
                                            </h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Treatment:</strong> {{ $visit->treatment_details }}</p>
                                                    <p><strong>Duration:</strong> {{ $visit->treatment_duration ?? 'N/A' }}</p>
                                                    <p><strong>Cost:</strong> ₹{{ number_format($visit->treatment_cost ?? 0, 2) }}</p>
                                                    <p><strong>Completed On:</strong> {{ \Carbon\Carbon::parse($visit->treatment_completion_date)->format('M d, Y') }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    @if($visit->treatment_completion_notes)
                                                    <p><strong>Completion Notes:</strong> {{ $visit->treatment_completion_notes }}</p>
                                                    @endif
                                                    @if($visit->treatment_notes)
                                                    <p><strong>Treatment Notes:</strong> {{ $visit->treatment_notes }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <h6 class="text-primary mb-3">Current Treatment Summary:</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Treatment:</strong> {{ $visit->treatment_details }}</p>
                                                    <p><strong>Duration:</strong> {{ $visit->treatment_duration ?? 'N/A' }}</p>
                                                    <p><strong>Cost:</strong> ₹{{ number_format($visit->treatment_cost ?? 0, 2) }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    @if($visit->next_visit_date)
                                                    <p><strong>Next Visit:</strong> {{ \Carbon\Carbon::parse($visit->next_visit_date)->format('M d, Y') }}</p>
                                                    @endif
                                                    @if($visit->treatment_notes)
                                                    <p><strong>Notes:</strong> {{ $visit->treatment_notes }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment History -->
                    @if(isset($visit->payments) && count($visit->payments) > 0)
                    <div class="row">
                        <div class="col-12">
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
                                                    <th>Amount Paid</th>
                                                    <th>Method</th>
                                                    <th>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($visit->payments as $payment)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($payment['payment_date'])->format('M d, Y h:i A') }}</td>
                                                    <td class="text-success">₹{{ number_format($payment['amount_paid'], 2) }}</td>
                                                    <td>
                                                        <span class="badge bg-secondary">{{ ucfirst($payment['payment_mode']) }}</span>
                                                    </td>
                                                    <td>{{ $payment['notes'] ?? '-' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Medicines & Procedures -->
                    @if($visit->medicines || $visit->procedures || $visit->injections)
                    <div class="row mt-4">
                        <div class="col-md-4">
                            @if($visit->medicines)
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Medicines</h6>
                                </div>
                                <div class="card-body">
                                    @if(is_array($visit->medicines))
                                        @foreach($visit->medicines as $medicine)
                                        <p class="mb-1">{{ $medicine }}</p>
                                        @endforeach
                                    @else
                                        <p class="mb-0">{{ $visit->medicines }}</p>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            @if($visit->procedures)
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Procedures</h6>
                                </div>
                                <div class="card-body">
                                    @if(is_array($visit->procedures))
                                        @foreach($visit->procedures as $procedure)
                                        <p class="mb-1">{{ $procedure }}</p>
                                        @endforeach
                                    @else
                                        <p class="mb-0">{{ $visit->procedures }}</p>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            @if($visit->injections)
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Injections</h6>
                                </div>
                                <div class="card-body">
                                    @if(is_array($visit->injections))
                                        @foreach($visit->injections as $injection)
                                        <p class="mb-1">{{ $injection }}</p>
                                        @endforeach
                                    @else
                                        <p class="mb-0">{{ $visit->injections }}</p>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Complete Treatment Modal -->
<div class="modal fade" id="completeTreatmentModal" tabindex="-1" aria-labelledby="completeTreatmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="completeTreatmentModalLabel">
                    <i class="bi bi-flag-fill me-2"></i>Complete Treatment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('visits.complete.treatment', $visit->_id) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Note:</strong> Once marked as completed, no more sessions can be added to this treatment.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Completion Date <span class="text-danger">*</span></label>
                        <input type="date" name="treatment_completion_date" class="form-control" 
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Completion Notes</label>
                        <textarea name="treatment_completion_notes" class="form-control" rows="3" 
                                  placeholder="Add any final notes about the treatment completion..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>Mark as Completed
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Session Modal -->
<div class="modal fade" id="addSessionModal" tabindex="-1" aria-labelledby="addSessionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addSessionModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Add New Session
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('visits.session.add', $visit->_id) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Session Date <span class="text-danger">*</span></label>
                        <input type="date" name="session_date" class="form-control" 
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Session Time <span class="text-danger">*</span></label>
                        <input type="time" name="session_time" class="form-control" 
                               value="{{ date('H:i') }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Next Session Date</label>
                        <input type="date" name="next_session_date" class="form-control">
                        <small class="text-muted">Leave empty if this is the final session</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Session Notes <span class="text-danger">*</span></label>
                        <textarea name="session_notes" class="form-control" rows="3" 
                                  placeholder="Describe what was done in this session..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i>Add Session
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
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

.payment-summary-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
}

.payment-item {
    text-align: center;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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

.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-bottom: 1rem;
}

.card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    border-radius: 10px 10px 0 0 !important;
}

/* Session Card Styles */
.session-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    border-left: 5px solid #667eea;
}

.session-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
}

.session-card.visit-fee {
    border-left-color: #ffc107;
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
}

.session-card.completed {
    border-left-color: #28a745;
}

.session-card.scheduled {
    border-left-color: #17a2b8;
}

.visit-fee-badge {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    color: white;
    border-radius: 20px;
    padding: 0.3rem 0.8rem;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-badge {
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-completed {
    background: #d4edda;
    color: #155724;
}

.status-scheduled {
    background: #d1ecf1;
    color: #0c5460;
}
</style>
@endsection
