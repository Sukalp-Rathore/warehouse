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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 20px 20px 0 0 !important;
        border: none;
    }
    
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
    
    .cost-breakdown {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-radius: 15px;
        padding: 1.5rem;
    }
    
    .form-control, .form-select {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
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
    
    .add-session-btn {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
        border-radius: 25px;
        padding: 0.8rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .add-session-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(40, 167, 69, 0.3);
    }
</style>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card modern-card">
                <!-- Header -->
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-0">
                                <i class="bi bi-calendar-check me-2"></i>
                                Session Management - {{ $visit->name }}
                            </h4>
                            <p class="mb-0 opacity-75">Treatment: {{ $visit->treatment_details ?? 'No treatment details' }}</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('visits.payments', $visit->_id) }}" class="btn btn-warning me-2">
                                <i class="bi bi-credit-card"></i> Payments
                            </a>
                            <a href="{{ route('visits.invoice', $visit->_id) }}" class="btn btn-info" target="_blank">
                                <i class="bi bi-receipt"></i> Invoice
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Body -->
                <div class="card-body">
                    <!-- Treatment Overview -->
                    <div class="cost-breakdown mb-4">
                        <h5 class="text-primary mb-3">
                            <i class="bi bi-calculator me-2"></i>Cost Breakdown
                        </h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-muted">Base Treatment</h6>
                                    <h4 class="text-primary">₹{{ number_format($visit->treatment_cost ?? 0, 2) }}</h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-muted">Visit Fees Added</h6>
                                    <h4 class="text-warning">₹{{ number_format($visit->visit_fees_added ?? 0, 2) }}</h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-muted">Total Cost</h6>
                                    <h4 class="text-success">₹{{ number_format($visit->total_cost ?? $visit->treatment_cost ?? 0, 2) }}</h4>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h6 class="text-muted">Remaining</h6>
                                    <h4 class="text-danger">₹{{ number_format($visit->remaining_balance ?? ($visit->total_cost ?? $visit->treatment_cost ?? 0), 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sessions -->
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="mb-3">
                                <i class="bi bi-list-ol me-2"></i>Sessions ({{ count($visit->sessions ?? []) }})
                            </h5>
                            
                            @if(isset($visit->sessions) && count($visit->sessions) > 0)
                                @foreach($visit->sessions as $session)
                                <div class="session-card mb-3 {{ $session['visit_fee_added'] ? 'visit-fee' : '' }} {{ $session['session_status'] }}">
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
                                @endforeach
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mt-3">No sessions added yet</h5>
                                    <p class="text-muted">Add the first session to start tracking progress</p>
                                </div>
                            @endif
                        </div>

                        <!-- Add Session Form -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">
                                        <i class="bi bi-plus-circle me-2"></i>Add New Session
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('visits.session.add', $visit->_id) }}">
                                        @csrf
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
                                        
                                        <button type="submit" class="btn add-session-btn w-100">
                                            <i class="bi bi-plus-circle me-2"></i>Add Session
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
