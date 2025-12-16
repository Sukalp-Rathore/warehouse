@extends('layout')

@section('title', 'Plant Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Plant Management</h4>
                        <a href="{{ route('tenant.plants.create') }}" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i> Add New Plant
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Plants Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Plant Code</th>
                                    <th>Plant Name</th>
                                    <th>Location</th>
                                    <th>Contact Person</th>
                                    <th>Contact Phone</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($plants as $plant)
                                    <tr>
                                        <td><strong>{{ $plant->plant_code }}</strong></td>
                                        <td>{{ $plant->plant_name }}</td>
                                        <td>{{ $plant->location }}</td>
                                        <td>{{ $plant->contact_person ?? 'N/A' }}</td>
                                        <td>{{ $plant->contact_phone ?? 'N/A' }}</td>
                                        <td>
                                            @if($plant->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('tenant.plants.show', $plant->_id) }}" 
                                                   class="btn btn-sm btn-outline-info" title="View">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                                <a href="{{ route('tenant.plants.edit', $plant->_id) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="ri-edit-line"></i>
                                                </a>
                                                <form method="POST" action="{{ route('tenant.plants.destroy', $plant->_id) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                            title="Delete" onclick="return confirm('Are you sure you want to delete this plant?')">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No plants found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $plants->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
