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
    
    .info-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 1.5rem;
        border-left: 5px solid #667eea;
    }
</style>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card modern-card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-clipboard-pulse me-2"></i>
                        Treatment Details
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Patient Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-primary">Patient: {{ $visit->name }}</h6>
                            <p class="text-muted">Phone: {{ $visit->phone }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Doctor: {{ $visit->doctor_name }}</h6>
                            <p class="text-muted">Date: {{ \Carbon\Carbon::parse($visit->visit_date)->format('M d, Y') }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('visits.treatment.update', $visit->_id) }}">
                        @csrf
                        
                        <!-- Treatment Details -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Treatment Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Treatment Description <span class="text-danger">*</span></label>
                                        <input type="text" name="treatment_details" class="form-control" 
                                               value="{{ $visit->treatment_details ?? '' }}" 
                                               placeholder="e.g., Root Canal Treatment" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Duration <span class="text-danger">*</span></label>
                                        <select name="treatment_duration" class="form-select" required>
                                            <option value="">Select Duration</option>
                                            <option value="15 mins" {{ ($visit->treatment_duration ?? '') == '15 mins' ? 'selected' : '' }}>15 mins</option>
                                            <option value="30 mins" {{ ($visit->treatment_duration ?? '') == '30 mins' ? 'selected' : '' }}>30 mins</option>
                                            <option value="45 mins" {{ ($visit->treatment_duration ?? '') == '45 mins' ? 'selected' : '' }}>45 mins</option>
                                            <option value="1 hour" {{ ($visit->treatment_duration ?? '') == '1 hour' ? 'selected' : '' }}>1 hour</option>
                                            <option value="1.5 hours" {{ ($visit->treatment_duration ?? '') == '1.5 hours' ? 'selected' : '' }}>1.5 hours</option>
                                            <option value="2 hours" {{ ($visit->treatment_duration ?? '') == '2 hours' ? 'selected' : '' }}>2 hours</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Treatment Cost (₹) <span class="text-danger">*</span></label>
                                        <input type="number" name="treatment_cost" class="form-control" 
                                               value="{{ $visit->treatment_cost ?? '' }}" 
                                               placeholder="5000" step="0.01" min="0" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Next Visit Date</label>
                                        <input type="date" name="next_visit_date" class="form-control" 
                                               value="{{ $visit->next_visit_date ?? '' }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Notes</label>
                                        <input type="text" name="treatment_notes" class="form-control" 
                                               value="{{ $visit->treatment_notes ?? '' }}" 
                                               placeholder="Any additional notes...">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('visits') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Visits
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Save Treatment Details
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
