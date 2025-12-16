@extends('layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h4 class="mb-3">Tenant Billing Overview</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tenants</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Client ID</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Plan</th>
                                    <th>Billing Status</th>
                                    <th>Created At</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tenants as $tenant)
                                    <tr>
                                        <td>{{ $tenant->client_id }}</td>
                                        <td>{{ $tenant->name }}</td>
                                        <td>{{ $tenant->status }}</td>
                                        <td>{{ $tenant->billing_plan ?? $tenant->subscription_plan ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $tenant->billing_status === 'active' ? 'success' : 'secondary' }}">
                                                {{ $tenant->billing_status ?? 'not set' }}
                                            </span>
                                        </td>
                                        <td>{{ $tenant->created_at }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('billing.tenants.show', $tenant->client_id) }}" class="btn btn-sm btn-primary">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No tenants found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


