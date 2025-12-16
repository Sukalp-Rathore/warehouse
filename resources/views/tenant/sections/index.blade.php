@extends('layout')

@section('title', 'Sections')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Sections (High Rack Storage Types Only)</h4>
                    <button type="button" class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#createForm">
                        <i class="ri-add-line me-1"></i> Create Section
                    </button>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="GET" class="mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Warehouse</label>
                                <select name="warehouse_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    @foreach($warehouses as $wh)
                                        <option value="{{ $wh->_id }}" {{ request('warehouse_id') == $wh->_id ? 'selected' : '' }}>
                                            {{ $wh->warehouse_name }} ({{ $wh->warehouse_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Storage Type</label>
                                <select name="storage_type_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    @foreach($storageTypes as $st)
                                        <option value="{{ $st->_id }}" {{ request('storage_type_id') == $st->_id ? 'selected' : '' }}>
                                            {{ $st->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Code or name">
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-outline-primary">Filter</button>
                            </div>
                        </div>
                    </form>

                    <div id="createForm" class="collapse mb-4">
                        <div class="card card-body bg-light">
                            <h6 class="mb-3">Create New Section (High Rack Only) - 2D Grid Format</h6>
                            <form method="POST" action="{{ route('tenant.sections.store') }}" id="sectionCreateForm">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Section Code <span class="text-danger">*</span></label>
                                        <input type="text" name="section_code" class="form-control @error('section_code') is-invalid @enderror" 
                                               value="{{ old('section_code') }}" placeholder="e.g., SEC1" required>
                                        @error('section_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <small class="form-text text-muted">Unique code for this section</small>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Section Name <span class="text-danger">*</span></label>
                                        <input type="text" name="section_name" class="form-control @error('section_name') is-invalid @enderror" 
                                               value="{{ old('section_name') }}" placeholder="e.g., Section 1" required>
                                        @error('section_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Warehouse <span class="text-danger">*</span></label>
                                        <select name="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" required>
                                            <option value="">Select Warehouse</option>
                                            @foreach($warehouses as $wh)
                                                <option value="{{ $wh->_id }}" {{ old('warehouse_id', request('warehouse_id')) == $wh->_id ? 'selected' : '' }}>
                                                    {{ $wh->warehouse_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Storage Type (High Rack) <span class="text-danger">*</span></label>
                                        <select name="storage_type_id" class="form-select @error('storage_type_id') is-invalid @enderror" required>
                                            <option value="">Select Storage Type</option>
                                            @foreach($storageTypes as $st)
                                                <option value="{{ $st->_id }}" {{ old('storage_type_id', request('storage_type_id')) == $st->_id ? 'selected' : '' }}>
                                                    {{ $st->name }} ({{ $st->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('storage_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        @if($storageTypes->count() === 0)
                                            <small class="text-danger">No High Rack storage types found. Create one first.</small>
                                        @endif
                                    </div>
                                    
                                    <div class="col-12">
                                        <hr>
                                        <h6 class="mb-3">Grid Configuration</h6>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label">Number of Rows <span class="text-danger">*</span></label>
                                        <input type="number" name="rows" id="rows" class="form-control @error('rows') is-invalid @enderror" 
                                               value="{{ old('rows', 1) }}" min="1" max="100" required oninput="calculateTotalBins()">
                                        @error('rows')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <small class="form-text text-muted">Rows will be numbered: r1, r2, r3...</small>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Number of Columns <span class="text-danger">*</span></label>
                                        <input type="number" name="columns" id="columns" class="form-control @error('columns') is-invalid @enderror" 
                                               value="{{ old('columns', 1) }}" min="1" max="100" required oninput="calculateTotalBins()">
                                        @error('columns')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <small class="form-text text-muted">Columns will be numbered: c1, c2, c3...</small>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Bin Volume (per bin) <span class="text-danger">*</span></label>
                                        <input type="number" name="bin_volume" class="form-control @error('bin_volume') is-invalid @enderror" 
                                               value="{{ old('bin_volume', 1.0) }}" step="0.01" min="0.1" required>
                                        @error('bin_volume')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <small class="form-text text-muted">Volume capacity for each bin</small>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Bin Weight (per bin) <span class="text-danger">*</span></label>
                                        <input type="number" name="bin_weight" class="form-control @error('bin_weight') is-invalid @enderror" 
                                               value="{{ old('bin_weight', 100.0) }}" step="0.01" min="0.1" required>
                                        @error('bin_weight')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <small class="form-text text-muted">Weight capacity for each bin</small>
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <div class="alert alert-info mb-0">
                                            <strong>Total Bins:</strong> <span id="totalBins">1</span> bins will be created automatically
                                            <br>
                                            <small>Bin codes will follow the pattern: {SectionCode}-r{row}c{column} (e.g., SEC1-r1c1, SEC1-r1c2)</small>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <label class="form-label">Zone Code</label>
                                        <input type="text" name="zone_code" class="form-control @error('zone_code') is-invalid @enderror" 
                                               value="{{ old('zone_code') }}" placeholder="e.g., A, B, C" maxlength="10">
                                        @error('zone_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-9">
                                        <label class="form-label">Description</label>
                                        <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" 
                                               value="{{ old('description') }}" placeholder="Optional description">
                                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="text-end mt-3">
                                    <button type="button" class="btn btn-secondary me-2" data-bs-toggle="collapse" data-bs-target="#createForm">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Create Section & Generate Bins</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <script>
                        function calculateTotalBins() {
                            const rows = parseInt(document.getElementById('rows').value) || 0;
                            const columns = parseInt(document.getElementById('columns').value) || 0;
                            const total = rows * columns;
                            document.getElementById('totalBins').textContent = total;
                        }
                        
                        // Calculate on page load
                        document.addEventListener('DOMContentLoaded', function() {
                            calculateTotalBins();
                        });
                    </script>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Section Code</th>
                                    <th>Name</th>
                                    <th>Warehouse</th>
                                    <th>Storage Type</th>
                                    <th>Grid Size</th>
                                    <th>Total Bins</th>
                                    <th>Bin Volume</th>
                                    <th>Bin Weight</th>
                                    <th>Zone</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sections as $sec)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $sec->section_code }}</span></td>
                                        <td>{{ $sec->section_name }}</td>
                                        <td>{{ optional(\App\Models\Client\Warehouse::find($sec->warehouse_id))->warehouse_name }}</td>
                                        <td>{{ optional(\App\Models\Client\StorageType::find($sec->storage_type_id))->name }}</td>
                                        <td>
                                            @if($sec->rows && $sec->columns)
                                                <span class="badge bg-info">{{ $sec->rows }} × {{ $sec->columns }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($sec->rows && $sec->columns)
                                                <strong>{{ $sec->rows * $sec->columns }}</strong>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $sec->bin_volume ?? '-' }}</td>
                                        <td>{{ $sec->bin_weight ?? '-' }}</td>
                                        <td>{{ $sec->zone_code ?? '-' }}</td>
                                        <td>
                                            <form class="d-inline" method="POST" action="{{ route('tenant.sections.destroy', $sec->_id) }}" 
                                                  onsubmit="return confirm('Delete this section and all its bins? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="10" class="text-center">No sections found. Create one using the "Create Section" button above.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $sections->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
