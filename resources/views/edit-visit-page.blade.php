@extends('layout')
@section('content')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-0">Edit Visit</h1>
        </div>
        <div>
            <a href="{{ route('visits') }}" class="btn btn-secondary btn-sm">Back to Visits</a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">Edit Visit Details</div>
                </div>
                <div class="card-body">
                    <!-- Patient Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="mb-3">Patient Information</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">Patient Name:</th>
                                        <td>{{ $visit->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Phone:</th>
                                        <td>{{ $visit->phone }}</td>
                                    </tr>
                                    <tr>
                                        <th>Doctor:</th>
                                        <td>{{ $visit->doctor_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Age:</th>
                                        <td>{{ $visit->age }}</td>
                                    </tr>
                                    <tr>
                                        <th>Gender:</th>
                                        <td>{{ $visit->gender }}</td>
                                    </tr>
                                    <tr>
                                        <th>Address:</th>
                                        <td>{{ $visit->address }}</td>
                                    </tr>
                                    <tr>
                                        <th>Visit Fee:</th>
                                        <td>₹{{ $visit->visit_fee }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Visit Details</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">Visit Date:</th>
                                        <td>{{ $visit->created_at ? $visit->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Created By:</th>
                                        <td>{{ $visit->created_by }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            @if(isset($visit->status) && $visit->status == 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @else
                                                <span class="badge bg-warning">In Progress</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if(isset($visit->completed_at))
                                    <tr>
                                        <th>Completed At:</th>
                                        <td>{{ $visit->completed_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Medicines Section -->
                    <div class="mb-4">
                        <h6 class="mb-3">Medicines</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <select id="medicineSelect" class="form-select" style="width:100%">
                                    <option value="">-- Select Medicine --</option>
                                    @foreach ($inventory as $item)
                                        <option value="{{ $item->_id }}">{{ $item->product_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered" id="medicines_table">
                                <thead>
                                    <tr>
                                        <th>Medicine</th>
                                        <th>Duration</th>
                                        <th>When to Take</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($visit->medicines) && is_array($visit->medicines))
                                        @foreach($visit->medicines as $medicine)
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="medicines[{{ $medicine['id'] ?? '' }}][id]" value="{{ $medicine['id'] ?? '' }}">
                                                    {{ $medicine['name'] ?? 'N/A' }}
                                                </td>
                                                <td><input type="text" name="medicines[{{ $medicine['id'] ?? '' }}][duration]" class="form-control" value="{{ $medicine['duration'] ?? '' }}" placeholder="7 days"></td>
                                                <td><input type="text" name="medicines[{{ $medicine['id'] ?? '' }}][time]" class="form-control" value="{{ $medicine['time'] ?? '' }}" placeholder="Morning/Evening"></td>
                                                <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Injections Section -->
                    <div class="mb-4">
                        <h6 class="mb-3">Injections</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="injections_table">
                                <thead>
                                    <tr>
                                        <th>Injection</th>
                                        <th>Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($visit->injections) && is_array($visit->injections))
                                        @foreach($visit->injections as $injection)
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="injections[{{ $loop->index }}][name]" value="{{ $injection['name'] ?? '' }}">
                                                    {{ $injection['name'] ?? 'N/A' }}
                                                </td>
                                                <td><input type="number" name="injections[{{ $loop->index }}][price]" class="form-control" value="{{ $injection['price'] ?? '' }}" placeholder="Price"></td>
                                                <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <button class="btn btn-sm btn-primary" type="button" id="addInjectionRow">+ Add Injection</button>
                    </div>

                    <!-- Procedures Section -->
                    <div class="mb-4">
                        <h6 class="mb-3">Procedures</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="procedures_table">
                                <thead>
                                    <tr>
                                        <th>Procedure</th>
                                        <th>Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($visit->procedures) && is_array($visit->procedures))
                                        @foreach($visit->procedures as $procedure)
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="procedures[{{ $loop->index }}][name]" value="{{ $procedure['name'] ?? '' }}">
                                                    {{ $procedure['name'] ?? 'N/A' }}
                                                </td>
                                                <td><input type="number" name="procedures[{{ $loop->index }}][price]" class="form-control" value="{{ $procedure['price'] ?? '' }}" placeholder="Price"></td>
                                                <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <button class="btn btn-sm btn-primary" type="button" id="addProcedureRow">+ Add Procedure</button>
                    </div>

                    <!-- Checkup Report -->
                    <div class="mb-4">
                        <label for="checkup_report" class="form-label">Checkup Report</label>
                        <textarea class="form-control" id="checkup_report" name="checkup_report" rows="3" placeholder="Enter checkup report...">{{ $visit->checkup_report ?? '' }}</textarea>
                    </div>

                    <!-- Diagnosis -->
                    <div class="mb-4">
                        <label for="diagnosis" class="form-label">Diagnosis</label>
                        <textarea class="form-control" id="diagnosis" name="diagnosis" rows="3" placeholder="Enter diagnosis...">{{ $visit->diagnosis ?? '' }}</textarea>
                    </div>

                    <!-- Remarks -->
                    <div class="mb-4">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Enter remarks...">{{ $visit->remarks ?? '' }}</textarea>
                    </div>

                    <!-- Total Calculation -->
                    <div class="mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Total Calculation</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Visit Fee:</strong> ₹<span id="visitFee">{{ $visit->visit_fee ?? 0 }}</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Injections:</strong> ₹<span id="injectionsTotal">0</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Procedures:</strong> ₹<span id="proceduresTotal">0</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Total:</strong> ₹<span id="grandTotal">{{ $visit->visit_fee ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2">
                        <button class="btn btn-success" id="updateVisit">
                            <i class="fa fa-save"></i> Update Visit
                        </button>
                        <button class="btn btn-primary" id="finishCheckup">
                            <i class="fa fa-check-circle"></i> Finish Checkup
                        </button>
                        <a href="{{ route('visits') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Back to Visits
                        </a>
                    </div>

                    <!-- Loading Indicator -->
                    <div id="loadingIndicator" class="text-center mt-3" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Processing...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('#medicineSelect').select2({
                placeholder: "Select a medicine",
                allowClear: true,
                dir: "ltr",
            });

            // When a medicine is selected
            $('#medicineSelect').on('select2:select', function(e) {
                let medicineId = e.params.data.id;
                let medicineName = e.params.data.text;

                // Add row in table
                let newRow = `
                <tr>
                    <td>
                        <input type="hidden" name="medicines[${medicineId}][id]" value="${medicineId}">
                        ${medicineName}
                    </td>
                    <td><input type="text" name="medicines[${medicineId}][duration]" class="form-control" placeholder="7 days"></td>
                    <td><input type="text" name="medicines[${medicineId}][time]" class="form-control" placeholder="Morning/Evening"></td>
                    <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
                </tr>
            `;

                $('#medicines_table tbody').append(newRow);

                // Reset Select2
                $('#medicineSelect').val(null).trigger('change');
            });

            // Remove row handler
            $(document).on('click', '.removeRow', function() {
                $(this).closest('tr').remove();
                calculateTotal(); // Recalculate total after removing row
            });

            // Add injection row
            $('#addInjectionRow').on('click', function() {
                let newRow = `
                <tr>
                    <td><input type="text" name="injections[new][name]" class="form-control" placeholder="Injection name"></td>
                    <td><input type="number" name="injections[new][price]" class="form-control" placeholder="Price"></td>
                    <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
                </tr>
            `;
                $('#injections_table tbody').append(newRow);
            });

            // Add procedure row
            $('#addProcedureRow').on('click', function() {
                let newRow = `
                <tr>
                    <td><input type="text" name="procedures[new][name]" class="form-control" placeholder="Procedure name"></td>
                    <td><input type="number" name="procedures[new][price]" class="form-control" placeholder="Price"></td>
                    <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
                </tr>
            `;
                $('#procedures_table tbody').append(newRow);
            });

            // Calculate total function - moved to global scope
            window.calculateTotal = function() {
                let visitFee = parseFloat($('#visitFee').text()) || 0;
                let injectionsTotal = 0;
                let proceduresTotal = 0;

                // Calculate injections total
                $('#injections_table tbody tr').each(function() {
                    let price = parseFloat($(this).find('input[name*="[price]"]').val()) || 0;
                    injectionsTotal += price;
                });

                // Calculate procedures total
                $('#procedures_table tbody tr').each(function() {
                    let price = parseFloat($(this).find('input[name*="[price]"]').val()) || 0;
                    proceduresTotal += price;
                });

                let grandTotal = visitFee + injectionsTotal + proceduresTotal;

                $('#injectionsTotal').text(injectionsTotal.toFixed(2));
                $('#proceduresTotal').text(proceduresTotal.toFixed(2));
                $('#grandTotal').text(grandTotal.toFixed(2));
            };

            // Calculate total on page load
            calculateTotal();

            // Add event handlers for existing price inputs
            $(document).on('change', 'input[name*="[price]"]', calculateTotal);

            // Update visit
            $('#updateVisit').on('click', function() {
                // Show loading indicator
                $('#loadingIndicator').show();
                $('#updateVisit, #finishCheckup').prop('disabled', true);
                
                // Collect form data
                let formData = {
                    visit_id: '{{ $visit->_id }}',
                    medicines: [],
                    injections: [],
                    procedures: [],
                    checkup_report: $('#checkup_report').val(),
                    diagnosis: $('#diagnosis').val(),
                    remarks: $('#remarks').val(),
                    _token: '{{ csrf_token() }}'
                };

                // Collect medicines data
                $('#medicines_table tbody tr').each(function() {
                    let medicineId = $(this).find('input[name*="[id]"]').val();
                    let duration = $(this).find('input[name*="[duration]"]').val();
                    let time = $(this).find('input[name*="[time]"]').val();
                    
                    if (medicineId && duration && time) {
                        formData.medicines.push({
                            id: medicineId,
                            duration: duration,
                            time: time
                        });
                    }
                });

                // Collect injections data
                $('#injections_table tbody tr').each(function() {
                    let name = $(this).find('input[name*="[name]"]').val();
                    let price = $(this).find('input[name*="[price]"]').val();
                    
                    if (name && price) {
                        formData.injections.push({
                            name: name,
                            price: parseFloat(price)
                        });
                    }
                });

                // Collect procedures data
                $('#procedures_table tbody tr').each(function() {
                    let name = $(this).find('input[name*="[name]"]').val();
                    let price = $(this).find('input[name*="[price]"]').val();
                    
                    if (name && price) {
                        formData.procedures.push({
                            name: name,
                            price: parseFloat(price)
                        });
                    }
                });

                // Send AJAX request
                $.ajax({
                    url: '{{ route("visit.update") }}',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        // Hide loading indicator
                        $('#loadingIndicator').hide();
                        $('#updateVisit, #finishCheckup').prop('disabled', false);
                        
                        if (response.success) {
                            toastr.success(response.message);
                            setTimeout(function() {
                                window.location.href = '{{ route("visits") }}';
                            }, 1500);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        // Hide loading indicator
                        $('#loadingIndicator').hide();
                        $('#updateVisit, #finishCheckup').prop('disabled', false);
                        
                        let response = xhr.responseJSON;
                        if (response && response.errors) {
                            $.each(response.errors, function(key, value) {
                                toastr.error(value[0]);
                            });
                        } else {
                            toastr.error('Something went wrong!');
                        }
                    }
                });
            });

            // Finish checkup
            $('#finishCheckup').on('click', function() {
                // Show confirmation dialog
                if (!confirm('Are you sure you want to finish this checkup? This action cannot be undone and will generate an invoice.')) {
                    return;
                }
                
                // Collect form data similar to update visit
                let formData = {
                    visit_id: '{{ $visit->_id }}',
                    medicines: [],
                    injections: [],
                    procedures: [],
                    checkup_report: $('#checkup_report').val(),
                    diagnosis: $('#diagnosis').val(),
                    remarks: $('#remarks').val(),
                    _token: '{{ csrf_token() }}'
                };

                // Collect medicines data
                $('#medicines_table tbody tr').each(function() {
                    let medicineId = $(this).find('input[name*="[id]"]').val();
                    let duration = $(this).find('input[name*="[duration]"]').val();
                    let time = $(this).find('input[name*="[time]"]').val();
                    
                    if (medicineId && duration && time) {
                        formData.medicines.push({
                            id: medicineId,
                            duration: duration,
                            time: time
                        });
                    }
                });

                // Collect injections data
                $('#injections_table tbody tr').each(function() {
                    let name = $(this).find('input[name*="[name]"]').val();
                    let price = $(this).find('input[name*="[price]"]').val();
                    
                    if (name && price) {
                        formData.injections.push({
                            name: name,
                            price: parseFloat(price)
                        });
                    }
                });

                // Collect procedures data
                $('#procedures_table tbody tr').each(function() {
                    let name = $(this).find('input[name*="[name]"]').val();
                    let price = $(this).find('input[name*="[price]"]').val();
                    
                    if (name && price) {
                        formData.procedures.push({
                            name: name,
                            price: parseFloat(price)
                        });
                    }
                });

                // Show loading indicator
                $('#loadingIndicator').show();
                $('#updateVisit, #finishCheckup').prop('disabled', true);
                
                // Send AJAX request to finish checkup
                $.ajax({
                    url: '{{ route("visit.finish.checkup") }}',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        // Hide loading indicator
                        $('#loadingIndicator').hide();
                        $('#updateVisit, #finishCheckup').prop('disabled', false);
                        
                        if (response.success) {
                            toastr.success(response.message);
                            
                            // Show invoice modal
                            showInvoiceModal(response.invoice_data);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        // Hide loading indicator
                        $('#loadingIndicator').hide();
                        $('#updateVisit, #finishCheckup').prop('disabled', false);
                        
                        let response = xhr.responseJSON;
                        if (response && response.errors) {
                            $.each(response.errors, function(key, value) {
                                toastr.error(value[0]);
                            });
                        } else {
                            toastr.error('Something went wrong!');
                        }
                    }
                });
            });

            // Function to show invoice modal
            function showInvoiceModal(invoiceData) {
                let modalHtml = `
                    <div class="modal fade" id="invoiceModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Invoice Generated Successfully!</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="text-center mb-3">
                                        <h6>Invoice #${invoiceData.invoice_number}</h6>
                                        <p>Patient: ${invoiceData.patient_name}</p>
                                        <p>Total Amount: ₹${invoiceData.total_charge}</p>
                                    </div>
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-success" onclick="downloadInvoice('${invoiceData.invoice_number}')">
                                            <i class="fa fa-download"></i> Download Invoice
                                        </button>
                                        <button class="btn btn-primary" onclick="sendWhatsApp('${invoiceData.patient_phone}', '${invoiceData.invoice_number}')">
                                            <i class="fab fa-whatsapp"></i> Send to WhatsApp
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Remove existing modal if any
                $('#invoiceModal').remove();
                
                // Append and show modal
                $('body').append(modalHtml);
                $('#invoiceModal').modal('show');
            }

            // Download invoice function
            window.downloadInvoice = function(invoiceNumber) {
                // Redirect to download route
                window.open(`{{ route('visit.download.invoice', ':invoice') }}`.replace(':invoice', invoiceNumber), '_blank');
            };

            // Send WhatsApp function
            window.sendWhatsApp = function(phone, invoiceNumber) {
                let message = `Your medical invoice #${invoiceNumber} is ready. Please check your email or contact us for details.`;
                let whatsappUrl = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
                window.open(whatsappUrl, '_blank');
            };
        });
    </script>
@endsection
