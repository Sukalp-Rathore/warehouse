@extends('layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h4 class="mb-3">Billing Overview</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Current Plan</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Tenant:</strong> {{ $tenant->name }} ({{ $tenant->client_id }})</p>
                    <p class="mb-1"><strong>Billing Status:</strong>
                        <span class="badge bg-{{ $tenant->billing_status === 'active' ? 'success' : 'warning' }}">
                            {{ $tenant->billing_status ?? 'not set' }}
                        </span>
                    </p>
                    <p class="mb-1"><strong>Plan:</strong> {{ $plan['name'] ?? ($tenant->billing_plan ?? 'Not Assigned') }}</p>
                    @if(isset($plan['features']))
                        <hr>
                        <p class="mb-2"><strong>Key Limits</strong></p>
                        <ul class="mb-0">
                            <li>Max Warehouses: {{ $plan['features']['max_warehouses'] ?? 'N/A' }}</li>
                            <li>Max Users: {{ $plan['features']['max_users'] ?? 'N/A' }}</li>
                            <li>GRN SAP Integration: {{ !empty($plan['features']['grn_sap_integration']) ? 'Enabled' : 'Disabled' }}</li>
                            <li>Advanced Reporting: {{ !empty($plan['features']['advanced_reporting']) ? 'Enabled' : 'Disabled' }}</li>
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Current Month Usage</h5>
                </div>
                <div class="card-body">
                    @if($usage->isEmpty())
                        <p class="text-muted mb-0">No usage recorded for the current month yet.</p>
                    @else
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Metric</th>
                                    <th class="text-end">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($usage as $metric => $quantity)
                                    <tr>
                                        <td>{{ $metric }}</td>
                                        <td class="text-end">{{ number_format($quantity, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


