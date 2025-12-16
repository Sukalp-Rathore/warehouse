@extends('layout')

@section('title', 'Plant Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Plant Details: {{ $plant->plant_name }}</h4>
                        <div>
                            <a href="{{ route('tenant.plants.edit', $plant->_id) }}" class="btn btn-primary me-2">
                                <i class="ri-edit-line me-1"></i> Edit Plant
                            </a>
                            <a href="{{ route('tenant.plants.index') }}" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-line me-1"></i> Back to Plants
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Basic Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Plant Code:</strong></td>
                                    <td><strong>{{ $plant->plant_code }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Plant Name:</strong></td>
                                    <td>{{ $plant->plant_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Location:</strong></td>
                                    <td>{{ $plant->location }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Address:</strong></td>
                                    <td>{{ $plant->address ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($plant->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $plant->created_at ? $plant->created_at->format('M d, Y H:i') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $plant->updated_at ? $plant->updated_at->format('M d, Y H:i') : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Contact Information -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Contact Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Contact Person:</strong></td>
                                    <td>{{ $plant->contact_person ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Contact Phone:</strong></td>
                                    <td>{{ $plant->contact_phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Contact Email:</strong></td>
                                    <td>{{ $plant->contact_email ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Users with access to this plant -->
                    @if($users && $users->count() > 0)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5>Users with Access to this Plant ({{ $users->count() }})</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($users as $user)
                                                <tr>
                                                    <td>{{ $user->name }}</td>
                                                    <td>{{ $user->email }}</td>
                                                    <td>
                                                        @if($user->role)
                                                            <span class="badge bg-info">{{ $user->role->name }}</span>
                                                        @else
                                                            <span class="text-muted">No Role</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($user->status === 'active')
                                                            <span class="badge bg-success">Active</span>
                                                        @else
                                                            <span class="badge bg-danger">Inactive</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="ri-information-line me-2"></i>
                                    No users currently have access to this plant.
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <form method="POST" action="{{ route('tenant.plants.destroy', $plant->_id) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" 
                                            onclick="return confirm('Are you sure you want to delete this plant?')">
                                        <i class="ri-delete-bin-line me-1"></i> Delete Plant
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
@endsection
