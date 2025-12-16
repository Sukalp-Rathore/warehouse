@extends('layout')

@section('title', 'Edit Transfer Order')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Edit Transfer Order: {{ $transferOrder->to_number }}</h4>
                        <a href="{{ route('tenant.transfer-orders.show', $transferOrder->_id) }}" class="btn btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('tenant.transfer-orders.update', $transferOrder->_id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">TO Number</label>
                                <input type="text" class="form-control" value="{{ $transferOrder->to_number }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <input type="text" class="form-control" value="{{ ucfirst($transferOrder->status) }}" disabled>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Item</label>
                                <input type="text" class="form-control" value="{{ $transferOrder->item_code }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Quantity</label>
                                <input type="text" class="form-control" value="{{ number_format($transferOrder->quantity, 2) }}" disabled>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">From Bin</label>
                                <input type="text" class="form-control" value="{{ $transferOrder->fromBin->bin_code ?? 'N/A' }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">To Bin</label>
                                <input type="text" class="form-control" value="{{ $transferOrder->toBin->bin_code ?? 'N/A' }}" disabled>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select name="priority" id="priority" class="form-select">
                                        <option value="normal" {{ $transferOrder->priority == 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="low" {{ $transferOrder->priority == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="high" {{ $transferOrder->priority == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ $transferOrder->priority == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="assigned_to" class="form-label">Assign To</label>
                                    <select name="assigned_to" id="assigned_to" class="form-select">
                                        <option value="">Unassigned</option>
                                        @foreach($operators as $operator)
                                            <option value="{{ $operator->_id }}" {{ $transferOrder->assigned_to == $operator->_id ? 'selected' : '' }}>
                                                {{ $operator->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3">{{ $transferOrder->notes }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('tenant.transfer-orders.show', $transferOrder->_id) }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Transfer Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

