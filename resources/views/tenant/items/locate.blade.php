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
                        <div class="position-relative">
                            <div class="input-group input-group-lg">
                                <input type="text" id="search-code" class="form-control" 
                                       placeholder="Start typing item code, name, SKU, or barcode..." 
                                       autocomplete="off">
                                <button class="btn btn-primary" type="button" id="search-btn">
                                    <i class="ri-search-line me-1"></i> Search
                                </button>
                            </div>
                            <!-- Autocomplete dropdown -->
                            <div id="autocomplete-dropdown" class="position-absolute w-100 bg-white border rounded shadow-lg" 
                                 style="display: none; z-index: 1000; max-height: 300px; overflow-y: auto; top: 100%; margin-top: 2px;">
                                <div id="autocomplete-list"></div>
                            </div>
                        </div>
                        <small class="text-muted">Type at least 1 character to see suggestions</small>
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

<style>
    .autocomplete-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #e9ecef;
        transition: background-color 0.2s;
    }
    .autocomplete-item:hover {
        background-color: #f8f9fa;
    }
    .autocomplete-item:last-child {
        border-bottom: none;
    }
    .autocomplete-item.active {
        background-color: #e7f3ff;
    }
    .autocomplete-item-code {
        font-weight: bold;
        color: #0d6efd;
    }
    .autocomplete-item-desc {
        color: #6c757d;
        font-size: 0.9em;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-code');
    const searchBtn = document.getElementById('search-btn');
    const resultsDiv = document.getElementById('search-results');
    const resultsContainer = document.getElementById('results-container');
    const loadingDiv = document.getElementById('loading');
    const autocompleteDropdown = document.getElementById('autocomplete-dropdown');
    const autocompleteList = document.getElementById('autocomplete-list');
    
    let liveSearchTimeout;
    let selectedIndex = -1;
    let currentSuggestions = [];

    // Live search with debouncing
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        // Hide autocomplete if input is empty
        if (!query) {
            autocompleteDropdown.style.display = 'none';
            return;
        }

        // Clear previous timeout
        clearTimeout(liveSearchTimeout);
        
        // Debounce: wait 300ms after user stops typing
        liveSearchTimeout = setTimeout(function() {
            performLiveSearch(query);
        }, 300);
    });

    // Handle keyboard navigation in autocomplete
    searchInput.addEventListener('keydown', function(e) {
        if (autocompleteDropdown.style.display === 'none' || currentSuggestions.length === 0) {
            if (e.key === 'Enter') {
                performSearch();
            }
            return;
        }

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, currentSuggestions.length - 1);
            updateAutocompleteSelection();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, -1);
            updateAutocompleteSelection();
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (selectedIndex >= 0 && currentSuggestions[selectedIndex]) {
                selectSuggestion(currentSuggestions[selectedIndex]);
            } else {
                performSearch();
            }
        } else if (e.key === 'Escape') {
            autocompleteDropdown.style.display = 'none';
            selectedIndex = -1;
        }
    });

    // Hide autocomplete when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !autocompleteDropdown.contains(e.target)) {
            autocompleteDropdown.style.display = 'none';
        }
    });

    function performLiveSearch(query) {
        fetch(`{{ route('tenant.items.locate.live-search') }}?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.suggestions && data.suggestions.length > 0) {
                    currentSuggestions = data.suggestions;
                    displaySuggestions(data.suggestions);
                } else {
                    autocompleteDropdown.style.display = 'none';
                    currentSuggestions = [];
                }
            })
            .catch(error => {
                console.error('Live search error:', error);
                autocompleteDropdown.style.display = 'none';
            });
    }

    function displaySuggestions(suggestions) {
        let html = '';
        suggestions.forEach((suggestion, index) => {
            html += `<div class="autocomplete-item" data-index="${index}" data-item-id="${suggestion.id}" data-item-code="${suggestion.item_code}">
                <div class="autocomplete-item-code">${suggestion.item_code}</div>
                <div class="autocomplete-item-desc">${suggestion.description || suggestion.short_description || 'N/A'}</div>
                ${suggestion.sku ? '<small class="text-muted">SKU: ' + suggestion.sku + '</small>' : ''}
            </div>`;
        });
        
        autocompleteList.innerHTML = html;
        autocompleteDropdown.style.display = 'block';
        selectedIndex = -1;

        // Add click handlers
        autocompleteList.querySelectorAll('.autocomplete-item').forEach(item => {
            item.addEventListener('click', function() {
                const suggestion = suggestions[parseInt(this.dataset.index)];
                selectSuggestion(suggestion);
            });
        });
    }

    function updateAutocompleteSelection() {
        const items = autocompleteList.querySelectorAll('.autocomplete-item');
        items.forEach((item, index) => {
            if (index === selectedIndex) {
                item.classList.add('active');
                item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            } else {
                item.classList.remove('active');
            }
        });
    }

    function selectSuggestion(suggestion) {
        searchInput.value = suggestion.item_code;
        autocompleteDropdown.style.display = 'none';
        selectedIndex = -1;
        // Automatically perform the full search
        performSearch(suggestion.item_code);
    }

    function performSearch(code = null) {
        const searchCode = code || searchInput.value.trim();
        if (!searchCode) {
            alert('Please enter a search code');
            return;
        }

        loadingDiv.style.display = 'block';
        resultsDiv.style.display = 'none';
        autocompleteDropdown.style.display = 'none';

        fetch(`{{ route('tenant.items.locate.search') }}?code=${encodeURIComponent(searchCode)}`)
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

    searchBtn.addEventListener('click', function() {
        performSearch();
    });
});
</script>
@endsection

