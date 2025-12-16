@extends('layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h4 class="mb-3">Tenant Billing Details</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tenant</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Client ID:</strong> {{ $tenant->client_id }}</p>
                    <p class="mb-1"><strong>Name:</strong> {{ $tenant->name }}</p>
                    <p class="mb-1"><strong>Status:</strong> {{ $tenant->status }}</p>
                    <p class="mb-1"><strong>Billing Status:</strong>
                        <span class="badge bg-{{ $tenant->billing_status === 'active' ? 'success' : 'secondary' }}">
                            {{ $tenant->billing_status ?? 'not set' }}
                        </span>
                    </p>
                    <p class="mb-1"><strong>Stripe Customer ID:</strong> {{ $tenant->stripe_id ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Plan</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Internal Plan Key:</strong> {{ $tenant->billing_plan ?? $tenant->subscription_plan ?? '-' }}</p>
                    <p class="mb-1"><strong>Plan Name:</strong> {{ $plan['name'] ?? '-' }}</p>
                    @if(isset($plan['features']))
                        <hr>
                        <p class="mb-2"><strong>Features</strong></p>
                        <ul class="mb-0">
                            <li>Max Warehouses: {{ $plan['features']['max_warehouses'] ?? 'N/A' }}</li>
                            <li>Max Users: {{ $plan['features']['max_users'] ?? 'N/A' }}</li>
                            <li>GRN SAP Integration: {{ !empty($plan['features']['grn_sap_integration']) ? 'Enabled' : 'Disabled' }}</li>
                            <li>Advanced Reporting: {{ !empty($plan['features']['advanced_reporting']) ? 'Enabled' : 'Disabled' }}</li>
                        </ul>
                    @else
                        <p class="text-muted mb-0">No plan definition found in config.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


