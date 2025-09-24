@extends('layout')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-credit-card me-2"></i>
                        Simple Payment Entry
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

                    <form method="POST" action="{{ route('payments.simple.store', $visit->_id) }}">
                        @csrf
                        <input type="hidden" name="visit_id" value="{{ $visit->_id }}">

                        <!-- Simple Payment Form -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Total Treatment Cost (₹)</label>
                                    <input type="number" name="total_cost" class="form-control" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Amount Paid (₹)</label>
                                    <input type="number" name="paid_amount" class="form-control" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Payment Method</label>
                                    <select name="payment_method" class="form-select">
                                        <option value="cash">Cash</option>
                                        <option value="card">Card</option>
                                        <option value="upi">UPI</option>
                                        <option value="cheque">Cheque</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Notes (Optional)</label>
                                    <input type="text" name="notes" class="form-control" placeholder="Any additional notes...">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('visits') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Visits
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-credit-card me-1"></i> Save Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
