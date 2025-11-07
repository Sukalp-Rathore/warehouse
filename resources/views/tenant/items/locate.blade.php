@extends('layout')

@section('title', 'Locate Item')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Locate Item</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="ri-information-line me-1"></i>
                        Search by Item Code, SKU, Barcode, QR Code, or RFID Tag
                    </div>

                    <div class="mb-4">
                        <div class="input-group input-group-lg">
                            <input type="text" id="search-code" class="form-control" placeholder="Enter item code, barcode, QR code, or RFID tag...">
                            <button class="btn btn-primary" type="button" id="search-btn">
                                <i class="ri-search-line me-1"></i> Search
                            </button>
                        </div>
                    </div>

                    <div id="search-results" style="display: none;">
                        <h5>Search Results</h5>
                        <div id="results-container"></div>
                    </div>

                    <div id="loading" style="display: none;" class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-code');
    const searchBtn = document.getElementById('search-btn');
    const resultsDiv = document.getElementById('search-results');
    const resultsContainer = document.getElementById('results-container');
    const loadingDiv = document.getElementById('loading');

    function performSearch() {
        const code = searchInput.value.trim();
        if (!code) {
            alert('Please enter a search code');
            return;
        }

        loadingDiv.style.display = 'block';
        resultsDiv.style.display = 'none';

        fetch(`{{ route('tenant.items.locate.search') }}?code=${encodeURIComponent(code)}`)
            .then(response => response.json())
            .then(data => {
                loadingDiv.style.display = 'none';
                resultsDiv.style.display = 'block';

                if (!data.success || !data.results || data.results.length === 0) {
                    resultsContainer.innerHTML = '<div class="alert alert-warning">No items found with the provided code.</div>';
                    return;
                }

                let html = '';
                data.results.forEach(result => {
                    html += `<div class="card mb-3">
                        <div class="card-header">
                            <h5 class="mb-0">${result.item.code} - ${result.item.description || 'N/A'}</h5>
                            <small>SKU: ${result.item.sku || 'N/A'}</small>
                        </div>
                        <div class="card-body">
                            <h6>Locations (${result.locations.length})</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Location Path</th>
                                            <th>Available Qty</th>
                                            <th>Total Qty</th>
                                            <th>Status</th>
                                            <th>Batch</th>
                                            <th>Codes</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;
                    
                    result.locations.forEach(loc => {
                        html += `<tr>
                            <td>${loc.location_path}</td>
                            <td>${loc.quantity}</td>
                            <td>${loc.total_quantity}</td>
                            <td><span class="badge bg-${loc.status === 'available' ? 'success' : 'secondary'}">${loc.status}</span></td>
                            <td>${loc.batch ? loc.batch.batch_id + (loc.batch.expiry_date ? ' (Exp: ' + new Date(loc.batch.expiry_date).toLocaleDateString() + ')' : '') : '-'}</td>
                            <td>
                                ${loc.codes.barcode ? '<small class="d-block">BC: ' + loc.codes.barcode + '</small>' : ''}
                                ${loc.codes.qr_code ? '<small class="d-block">QR: ' + loc.codes.qr_code.substring(0, 15) + '...</small>' : ''}
                                ${loc.codes.rfid_tag ? '<small class="d-block">RFID: ' + loc.codes.rfid_tag.substring(0, 15) + '...</small>' : ''}
                                ${!loc.codes.barcode && !loc.codes.qr_code && !loc.codes.rfid_tag ? '-' : ''}
                            </td>
                            <td>
                                <a href="/wms/tenant/inventory/${loc.inventory_id}" class="btn btn-sm btn-info">
                                    <i class="ri-eye-line"></i>
                                </a>
                            </td>
                        </tr>`;
                    });

                    html += `</tbody></table></div></div></div>`;
                });

                resultsContainer.innerHTML = html;
            })
            .catch(error => {
                loadingDiv.style.display = 'none';
                resultsContainer.innerHTML = '<div class="alert alert-danger">Error performing search. Please try again.</div>';
                console.error('Search error:', error);
            });
    }

    searchBtn.addEventListener('click', performSearch);
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });
});
</script>
@endsection

