@extends('layout')

@section('title', 'Import Items')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Import Items from CSV</h4>
                        <div>
                            <a href="{{ route('tenant.items.import.template') }}" class="btn btn-info me-2">
                                <i class="ri-download-line me-1"></i> Download Template
                            </a>
                            <a href="{{ route('tenant.items.index') }}" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-line me-1"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('warning') && session('import_details'))
                        <div class="alert alert-warning">
                            <h6>Import Results:</h6>
                            <p>Success: {{ session('import_details')['successful']->count() }}</p>
                            <p>Errors: {{ session('import_details')['failed']->count() }}</p>
                            @if(session('import_details')['failed']->count() > 0)
                                <details>
                                    <summary>View Errors</summary>
                                    <ul>
                                        @foreach(session('import_details')['failed'] as $error)
                                            <li>Line {{ $error['line'] }} ({{ $error['item_code'] }}): {{ implode(', ', $error['errors']) }}</li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        </div>
                    @endif

                    <form method="POST" action="{{ route('tenant.items.import.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="file" class="form-label">Select CSV File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror" 
                                   id="file" name="file" accept=".csv,.txt" required>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Only CSV files are allowed. Maximum file size: 2MB</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="skip_first_row" name="skip_first_row" value="1" checked>
                                    <label class="form-check-label" for="skip_first_row">
                                        Skip first row (header row)
                                    </label>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="update_existing" name="update_existing" value="1">
                                    <label class="form-check-label" for="update_existing">
                                        Update existing items (by item code)
                                    </label>
                                    <small class="form-text text-muted d-block">If unchecked, duplicate item codes will be skipped</small>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <strong>Import Instructions:</strong>
                            <ul class="mb-0">
                                <li>Download the template file to see the required format</li>
                                <li>Required fields: Item Code, Description, UOM</li>
                                <li>Optional fields: All other columns</li>
                                <li>Categories will be created automatically if they don't exist</li>
                                <li>Vendor codes must match existing vendors</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('tenant.items.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Import Items</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

