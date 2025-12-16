@extends('layout')

@section('title', 'Storage Type Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Storage Types</h4>
                    <a href="{{ route('tenant.warehouses.index') }}" class="btn btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Warehouses
                    </a>
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

                    <div class="mb-4 d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#createForm">
                            <i class="ri-add-line me-1"></i> Add Storage Type
                        </button>
                    </div>

                    <div id="createForm" class="collapse mb-4">
                        <div class="card card-body">
                            <form method="POST" action="{{ route('tenant.storage-types.store') }}">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Code<span class="text-danger">*</span></label>
                                        <select name="code" class="form-select @error('code') is-invalid @enderror" required>
                                            <option value="">Select</option>
                                            <option value="HIGH_RACK" {{ old('code')=='HIGH_RACK' ? 'selected' : '' }}>HIGH_RACK</option>
                                            <option value="COLD_ROOM" {{ old('code')=='COLD_ROOM' ? 'selected' : '' }}>COLD_ROOM</option>
                                        </select>
                                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Name<span class="text-danger">*</span></label>
                                        <select name="name" class="form-select @error('name') is-invalid @enderror" required>
                                            <option value="">Select</option>
                                            <option value="High Rack" {{ old('name')=='High Rack' ? 'selected' : '' }}>High Rack</option>
                                            <option value="Cold Room" {{ old('name')=='Cold Room' ? 'selected' : '' }}>Cold Room</option>
                                        </select>
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Description</label>
                                        <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" value="{{ old('description') }}">
                                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="text-end mt-3">
                                    <button class="btn btn-primary" type="submit">Create</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($storageTypes as $st)
                                <tr>
                                    <td><span class="badge bg-secondary">{{ $st->code }}</span></td>
                                    <td>{{ $st->name }}</td>
                                    <td>{{ $st->description ?? '-' }}</td>
                                    <td>
                                        <form class="d-inline" method="POST" action="{{ route('tenant.storage-types.update', $st->_id) }}">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="code" value="{{ $st->code }}">
                                            <input type="hidden" name="name" value="{{ $st->name }}">
                                            <input type="hidden" name="description" value="{{ $st->description }}">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                                        </form>
                                        <form class="d-inline" method="POST" action="{{ route('tenant.storage-types.destroy', $st->_id) }}" onsubmit="return confirm('Delete this storage type?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center">No storage types found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $storageTypes->appends(request()->query())->links() }}

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
