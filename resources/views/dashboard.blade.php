@extends('layout')

@section('content')
<style>
    .dashboard-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }
    
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 20px;
        padding: 2rem;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card.success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }
    
    .stat-card.warning {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    }
    
    .stat-card.info {
        background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
    }
    
    .stat-card.danger {
        background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
    }
    
    .stat-icon {
        font-size: 3rem;
        opacity: 0.3;
        position: absolute;
        top: 1rem;
        right: 1rem;
    }
    
    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        font-size: 1rem;
        opacity: 0.9;
        margin-bottom: 0;
    }
    
    .table-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .table-card .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: none;
        padding: 1.5rem;
    }
    
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 20px;
        margin-bottom: 2rem;
    }
</style>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-2">
                    <i class="bi bi-speedometer2 me-3"></i>
                    Healthcare Dashboard
                </h1>
                <p class="mb-0 opacity-75">Welcome to your healthcare management system</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="text-white">
                    <h5 class="mb-1">{{ date('l, F j, Y') }}</h5>
                    <p class="mb-0 opacity-75">{{ date('h:i A') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card success">
                <div class="stat-icon">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-value">{{ number_format($totalPatients) }}</div>
                <div class="stat-label">Total Patients</div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card info">
                <div class="stat-icon">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="stat-value">{{ number_format($totalVisits) }}</div>
                <div class="stat-label">Total Visits</div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card warning">
                <div class="stat-icon">
                    <i class="bi bi-currency-rupee"></i>
                </div>
                <div class="stat-value">₹{{ number_format($monthlyRevenue) }}</div>
                <div class="stat-label">Monthly Revenue</div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card danger">
                <div class="stat-icon">
                    <i class="bi bi-person-badge"></i>
                </div>
                <div class="stat-value">{{ number_format($totalUsers) }}</div>
                <div class="stat-label">Staff Members</div>
            </div>
        </div>
    </div>

    <!-- Today's Overview -->
    <div class="row mb-4">
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="dashboard-card">
                <div class="card-body text-center p-4">
                    <h3 class="text-primary mb-1">{{ $todayVisits }}</h3>
                    <p class="text-muted mb-0">Today's Visits</p>
                </div>
            </div>
        </div>
        
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="dashboard-card">
                <div class="card-body text-center p-4">
                    <h3 class="text-success mb-1">₹{{ number_format($todayRevenue) }}</h3>
                    <p class="text-muted mb-0">Today's Revenue</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Visits -->
    <div class="row">
        <div class="col-12">
            <div class="table-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        Recent Visits
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentVisits as $visit)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <span class="avatar-initial rounded-circle bg-primary text-white">
                                                    {{ substr($visit->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $visit->name }}</h6>
                                                <small class="text-muted">{{ $visit->phone }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $visit->doctor_name }}</td>
                                    <td>{{ \Carbon\Carbon::parse($visit->visit_date)->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $visit->treatment_status === 'completed' ? 'success' : ($visit->treatment_status === 'in_progress' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($visit->treatment_status ?? 'pending') }}
                                        </span>
                                    </td>
                                    <td class="text-success">₹{{ number_format($visit->total_cost ?? 0) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                        <p class="mt-2 mb-0">No visits found</p>
                                    </td>
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