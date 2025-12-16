@extends('layout')

@section('title', 'Inventory Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Inventory Details</h4>
                        <div>
                            @if($inventory->status == 'available')
                                <a href="{{ route('tenant.inventory.transfer', $inventory->_id) }}" class="btn btn-warning me-2">
                                    <i class="ri-arrow-left-right-line me-1"></i> Transfer
                                </a>
                                <button type="button" class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#qcHoldModal">
                                    <i class="ri-lock-line me-1"></i> QC Hold
                                </button>
                            @endif
                            @if($inventory->status == 'qc_hold')
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#qcReleaseModal">
                                    <i class="ri-unlock-line me-1"></i> QC Release
                                </button>
                            @endif
                            <a href="{{ route('tenant.inventory.adjust', $inventory->_id) }}" class="btn btn-info">
                                <i class="ri-edit-line me-1"></i> Adjust Stock
                            </a>
                            <a href="{{ route('tenant.inventory.index') }}" class="btn btn-secondary">
                                <i class="ri-arrow-left-line me-1"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Item Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Item Code</th>
                                    <td>{{ $inventory->item_code }}</td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{{ $inventory->item->description ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>SKU</th>
                                    <td>{{ $inventory->item->sku ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>UOM</th>
                                    <td>{{ $inventory->item->uom ?? 'PCS' }}</td>
                                </tr>
                                <tr>
                                    <th>Type</th>
                                    <td>{{ $inventory->item->type ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5>Location Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Full Location Path</th>
                                    <td>{{ $inventory->getLocationPath() }}</td>
                                </tr>
                                <tr>
                                    <th>Warehouse</th>
                                    <td>{{ $inventory->warehouse->warehouse_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Storage Type</th>
                                    <td>{{ $inventory->storageType->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Section</th>
                                    <td>{{ $inventory->section->section_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Bin</th>
                                    <td><strong>{{ $inventory->bin->bin_code ?? 'N/A' }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5>Stock Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Total Quantity</th>
                                    <td><strong>{{ number_format($inventory->quantity, 2) }} {{ $inventory->item->uom ?? 'PCS' }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Available Quantity</th>
                                    <td><span class="badge bg-success">{{ number_format($inventory->available_qty, 2) }}</span></td>
                                </tr>
                                <tr>
                                    <th>Blocked Quantity</th>
                                    <td><span class="badge bg-danger">{{ number_format($inventory->blocked_qty, 2) }}</span></td>
                                </tr>
                                <tr>
                                    <th>Reserved Quantity</th>
                                    <td><span class="badge bg-warning">{{ number_format($inventory->reserved_qty, 2) }}</span></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($inventory->status == 'available')
                                            <span class="badge bg-success">Available</span>
                                        @elseif($inventory->status == 'reserved')
                                            <span class="badge bg-warning">Reserved</span>
                                        @elseif($inventory->status == 'blocked')
                                            <span class="badge bg-danger">Blocked</span>
                                        @elseif($inventory->status == 'qc_hold')
                                            <span class="badge bg-secondary">QC Hold</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($inventory->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5>Batch & Identification Codes</h5>
                            <table class="table table-bordered">
                                @if($inventory->batch)
                                <tr>
                                    <th width="40%">Batch ID</th>
                                    <td><span class="badge bg-info">{{ $inventory->batch->batch_id }}</span></td>
                                </tr>
                                <tr>
                                    <th>Manufacturing Date</th>
                                    <td>{{ $inventory->batch->manufacturing_date ? $inventory->batch->manufacturing_date->format('Y-m-d') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Expiry Date</th>
                                    <td>
                                        @if($inventory->batch->expiry_date)
                                            {{ $inventory->batch->expiry_date->format('Y-m-d') }}
                                            @if($inventory->batch->isExpired())
                                                <span class="badge bg-danger ms-2">Expired</span>
                                            @else
                                                @php
                                                    $daysUntilExpiry = $inventory->batch->getDaysUntilExpiry();
                                                @endphp
                                                @if($daysUntilExpiry !== null && $daysUntilExpiry <= 30)
                                                    <span class="badge bg-warning ms-2">Expiring Soon</span>
                                                @endif
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Barcode</th>
                                    <td><code>{{ $inventory->barcode ?? 'N/A' }}</code></td>
                                </tr>
                                <tr>
                                    <th>QR Code</th>
                                    <td><code>{{ $inventory->qr_code ?? 'N/A' }}</code></td>
                                </tr>
                                <tr>
                                    <th>RFID Tag</th>
                                    <td><code>{{ $inventory->rfid_tag ?? 'N/A' }}</code></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Transaction History</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date/Time</th>
                                            <th>Type</th>
                                            <th>From Bin</th>
                                            <th>To Bin</th>
                                            <th>Quantity</th>
                                            <th>Reference</th>
                                            <th>User</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($transactions as $transaction)
                                            <tr>
                                                <td>{{ $transaction->timestamp->format('Y-m-d H:i') }}</td>
                                                <td>
                                                    <span class="badge bg-primary">{{ $transaction->transaction_type }}</span>
                                                </td>
                                                <td>{{ $transaction->fromBin->bin_code ?? '-' }}</td>
                                                <td>{{ $transaction->toBin->bin_code ?? '-' }}</td>
                                                <td>{{ number_format($transaction->quantity, 2) }} {{ $transaction->uom }}</td>
                                                <td>{{ $transaction->reference_doc ?? '-' }}</td>
                                                <td>{{ $transaction->user->name ?? 'System' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No transaction history</td>
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
    </div>
</div>

<!-- QC Hold Modal -->
@if($inventory->status == 'available')
<div class="modal fade" id="qcHoldModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tenant.inventory.qc-hold', $inventory->_id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Move to QC Hold</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="hold_bin_id" class="form-label">Hold Bin (Optional)</label>
                        <select name="hold_bin_id" id="hold_bin_id" class="form-select">
                            <option value="">Use Current Bin</option>
                            <!-- Load bins via AJAX -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="hold_notes" class="form-label">Notes *</label>
                        <textarea name="notes" id="hold_notes" class="form-control" rows="3" required placeholder="Reason for QC hold..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Move to QC Hold</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- QC Release Modal -->
@if($inventory->status == 'qc_hold')
<div class="modal fade" id="qcReleaseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tenant.inventory.qc-release', $inventory->_id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Release from QC Hold</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="release_bin_id" class="form-label">Destination Bin *</label>
                        <select name="destination_bin_id" id="release_bin_id" class="form-select" required>
                            <option value="">Select Bin</option>
                            <!-- Load bins via AJAX -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="release_notes" class="form-label">Notes *</label>
                        <textarea name="notes" id="release_notes" class="form-control" rows="3" required placeholder="QC release notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Release from QC</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const warehouseId = '{{ $inventory->warehouse_id }}';
    
    // Load bins for QC Hold modal
    const qcHoldModal = document.getElementById('qcHoldModal');
    if (qcHoldModal) {
        qcHoldModal.addEventListener('show.bs.modal', function() {
            const holdBinSelect = document.getElementById('hold_bin_id');
            if (holdBinSelect && holdBinSelect.options.length <= 1) {
                loadBins(warehouseId, holdBinSelect);
            }
        });
    }
    
    // Load bins for QC Release modal
    const qcReleaseModal = document.getElementById('qcReleaseModal');
    const currentBinId = '{{ $inventory->bin_id }}';
    if (qcReleaseModal) {
        qcReleaseModal.addEventListener('show.bs.modal', function() {
            const releaseBinSelect = document.getElementById('release_bin_id');
            if (releaseBinSelect && releaseBinSelect.options.length <= 1) {
                loadBinsForQCRelease(warehouseId, releaseBinSelect, currentBinId);
            }
        });
    }
    
    function loadBins(warehouseId, selectElement) {
        // Clear existing options except the first one
        while (selectElement.options.length > 1) {
            selectElement.remove(1);
        }
        
        // Show loading state
        const loadingOption = document.createElement('option');
        loadingOption.value = '';
        loadingOption.textContent = 'Loading bins...';
        loadingOption.disabled = true;
        selectElement.appendChild(loadingOption);
        
        fetch(`{{ route('tenant.bins.list') }}?warehouse_id=${warehouseId}`)
            .then(response => response.json())
            .then(data => {
                // Remove loading option
                selectElement.remove(selectElement.options.length - 1);
                
                if (data.success && data.bins.length > 0) {
                    // Add bins to dropdown
                    data.bins.forEach(bin => {
                        const option = document.createElement('option');
                        option.value = bin.id;
                        option.textContent = `${bin.code} - ${bin.name} (${bin.storage_type}${bin.section !== 'N/A' ? ' - ' + bin.section : ''}) [${bin.status}]`;
                        selectElement.appendChild(option);
                    });
                } else {
                    // Show message if no bins found
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'No bins available for this warehouse';
                    option.disabled = true;
                    selectElement.appendChild(option);
                }
            })
            .catch(error => {
                console.error('Error loading bins:', error);
                // Remove loading option
                selectElement.remove(selectElement.options.length - 1);
                
                // Show error message
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'Error loading bins';
                option.disabled = true;
                selectElement.appendChild(option);
            });
    }
    
    function loadBinsForQCRelease(warehouseId, selectElement, currentBinId) {
        // Clear existing options except the first one
        while (selectElement.options.length > 1) {
            selectElement.remove(1);
        }
        
        // Show loading state
        const loadingOption = document.createElement('option');
        loadingOption.value = '';
        loadingOption.textContent = 'Loading bins...';
        loadingOption.disabled = true;
        selectElement.appendChild(loadingOption);
        
        // Load bins including the current bin (even if not available)
        const url = `{{ route('tenant.bins.list') }}?warehouse_id=${warehouseId}&include_bin_id=${currentBinId}`;
        fetch(url)
            .then(response => response.json())
            .then(data => {
                // Remove loading option
                selectElement.remove(selectElement.options.length - 1);
                
                if (data.success && data.bins.length > 0) {
                    // Add bins to dropdown, pre-select current bin
                    data.bins.forEach(bin => {
                        const option = document.createElement('option');
                        option.value = bin.id;
                        const isCurrentBin = bin.id === currentBinId;
                        option.textContent = `${bin.code} - ${bin.name} (${bin.storage_type}${bin.section !== 'N/A' ? ' - ' + bin.section : ''}) [${bin.status}]${isCurrentBin ? ' (Current)' : ''}`;
                        if (isCurrentBin) {
                            option.selected = true;
                        }
                        selectElement.appendChild(option);
                    });
                } else {
                    // Show message if no bins found
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'No bins available for this warehouse';
                    option.disabled = true;
                    selectElement.appendChild(option);
                }
            })
            .catch(error => {
                console.error('Error loading bins:', error);
                // Remove loading option
                selectElement.remove(selectElement.options.length - 1);
                
                // Show error message
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'Error loading bins';
                option.disabled = true;
                selectElement.appendChild(option);
            });
    }
});
</script>
@endsection

