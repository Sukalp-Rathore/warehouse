@extends('layout')
@section('content')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">
    <!-- jQuery Confirm CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-confirm@3.3.4/css/jquery-confirm.min.css">
    
    <style>
        .modern-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .modern-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }
        
        .hover-row {
            transition: all 0.2s ease;
        }
        
        .hover-row:hover {
            background-color: #f8f9fa;
            transform: scale(1.01);
        }
        
        .avatar {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
        }
        
        .avatar-initial {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-group .btn {
            border-radius: 8px;
            margin: 0 2px;
            transition: all 0.2s ease;
        }
        
        .btn-group .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .table-dark th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 0.5em 0.75em;
            border-radius: 20px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
            border-radius: 15px 15px 0 0 !important;
        }
        
        .page-header-breadcrumb {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .page-title {
            color: white !important;
            font-weight: 600;
        }
        
        .add-visit {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .add-visit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(40, 167, 69, 0.3);
        }
    </style>
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-0">All Visits</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card modern-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">Search Visits</div>
                    <div class="card-options">
                        <a class="btn btn-primary btn-sm add-visit">Add Visit</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="file-exports" class="table table-bordered text-nowrap w-100">
                            <thead class="table-dark">
                                <tr>
                                    <th>S.No</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Visit Date</th>
                                    <th>Treatment</th>
                                    <th>Payment Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($visits as $p)
                                    <tr class="hover-row">
                                        <td class="fw-bold">{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-3">
                                                    <span class="avatar-initial rounded-circle bg-primary text-white">
                                                        {{ substr($p->name, 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold">{{ $p->name }}</h6>
                                                    <small class="text-muted">{{ $p->phone }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <h6 class="mb-0">{{ $p->doctor_name }}</h6>
                                                <small class="text-muted">{{ $p->gender }}, {{ $p->age }} years</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <span class="fw-semibold">{{ \Carbon\Carbon::parse($p->visit_date)->format('M d, Y') }}</span>
                                                <br>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($p->visit_date)->format('h:i A') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if($p->treatment_details)
                                                <div>
                                                    <h6 class="mb-1 text-primary">{{ $p->treatment_details }}</h6>
                                                    <small class="text-muted">₹{{ number_format($p->treatment_cost ?? 0, 2) }}</small>
                                                </div>
                                            @else
                                                <span class="text-muted">No treatment</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($p->treatment_cost && $p->treatment_cost > 0)
                                                <div class="text-center">
                                                    <div class="mb-1">
                                                        <span class="badge bg-{{ ($p->payment_status ?? 'pending') === 'completed' ? 'success' : (($p->payment_status ?? 'pending') === 'partial' ? 'warning' : 'secondary') }} fs-6">
                                                            {{ ucfirst($p->payment_status ?? 'pending') }}
                                                        </span>
                                                    </div>
                                                    <div class="small">
                                                        <div class="text-success">Paid: ₹{{ number_format($p->total_paid ?? 0, 2) }}</div>
                                                        <div class="text-warning">Remaining: ₹{{ number_format($p->remaining_balance ?? ($p->treatment_cost ?? 0), 2) }}</div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">No payment</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('visits.sessions', $p->_id) }}" 
                                                   class="btn btn-outline-primary btn-sm" title="Manage Visit">
                                                    <i class="bi bi-gear"></i> Manage
                                                </a>
                                                <button class="btn btn-outline-danger btn-sm deleteBtn"
                                                    data-id="{{ $p->_id }}" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Modals for add patient  --}}
    <div class="modal fade" id="addVisitModal" tabindex="-1" aria-labelledby="exampleModalLgLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="exampleModalLgLabel">Add Visit</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="response">
                    <form id="addVisitForm" method="POST" action="{{ route('visit.create') }}">
                        {{-- CSRF Token --}}
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Patient Name</label>
                            <select name="patient_name" id="patient_name">
                                <option value="">Select Patient</option>
                                @foreach ($patients as $patient)
                                    <option value="{{ $patient->name }}">{{ $patient->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone No.</label>
                            <input type="tel" maxlength="10" class="form-control" id="phone"
                                placeholder="Enter 10 digit Phone number" name="phone" autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="age" class="form-label">Age</label>
                            <input type="text" class="form-control" id="age" placeholder="Enter Age" name="age"
                                autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" placeholder="Enter address"
                                name="address" autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <button class="btn btn-secondary" type="submit">Add Patient</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    {{-- Modals for edit vendor  --}}
    <div class="modal fade" id="editPatientModal" tabindex="-1" aria-labelledby="exampleModalLgLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="exampleModalLgLabel">Update Patient</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="responsed">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    {{-- modal end --}}
@endsection

@section('js')
    <!-- jQuery Confirm JS -->
    <script src="https://cdn.jsdelivr.net/npm/jquery-confirm@3.3.4/js/jquery-confirm.min.js"></script>
    <!-- Datatables Cdn -->
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.3.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.6/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

    <!-- Internal Datatables JS -->
    <script src="assets/js/datatables.js"></script>

    <script>
        $(document).ready(function() {

            var table = $('#file-exports').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'collection',
                    text: '<i class="fa fa-download"></i> Export',
                    buttons: [{
                            extend: 'copy',
                            text: '<i class="fa fa-copy"></i> Copy'
                        },
                        {
                            extend: 'csv',
                            text: '<i class="fa fa-file-csv"></i> CSV'
                        },
                        {
                            extend: 'excel',
                            text: '<i class="fa fa-file-excel"></i> Excel'
                        },
                        {
                            extend: 'pdf',
                            text: '<i class="fa fa-file-pdf"></i> PDF'
                        }
                    ]
                }],
                language: {
                    searchPlaceholder: 'Search...',
                    sSearch: '',
                }
            });

            $.fn.modal.Constructor.prototype.enforceFocus = function() {}
            $("#patient_name").select2({
                placeholder: "Select Patient",
                allowClear: true,
                dropdownParent: $('#patient_name').closest('.modal-content')
            });

            $(document).on('click', '.add-visit', function() {
                $("#addVisitModal").modal('show');
            });

            $('#addVisitForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    type: 'POST',
                    url: $(this).attr('action'),
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('#addVisitModal').modal('hide');
                        window.location.reload();
                        toastr.success(response.message);
                    },
                    error: function(xhr, status, error) {
                        var response = xhr.responseJSON;
                        if (response && response.errors) {
                            $.each(response.errors, function(key, value) {
                                toastr.error(value[0]);
                            });
                        }
                    }
                });
            });

            $(document).on('click', '.editBtn', function() {
                let visitId = $(this).attr('data-id');
                $("#editVisitModal").modal('show');
                $.ajax({
                    url: "{{ route('visit.show.edit') }}",
                    type: 'POST',
                    data: {
                        visitId: visitId,
                    },
                    success: function(response) {
                        if (response.success == false) {
                            alert(response.message);
                        } else {
                            $("#editPatientModal").modal('show');
                            $("#responsed").html(response);
                        };
                    },
                    error: function(xhr, status, error) {
                        // Handle error response
                        console.error(xhr.responseText);
                    }
                });
            });


            $(document).on('click', '.deleteBtn', function() {
                let visitId = $(this).attr('data-id');

                $.confirm({
                    title: 'Confirm Deletion',
                    content: 'Are you sure you want to delete this patient?',
                    buttons: {
                        confirm: {
                            text: 'Yes, Delete',
                            btnClass: 'btn-red',
                            action: function() {
                                $.ajax({
                                    url: "{{ route('visit.delete') }}",
                                    type: 'POST',
                                    data: {
                                        visitId: visitId,
                                        _token: '{{ csrf_token() }}' // add CSRF token manually if needed
                                    },
                                    success: function(response) {
                                        if (response.success == false) {
                                            $.alert({
                                                title: 'Error',
                                                content: response.message,
                                                type: 'red'
                                            });
                                        } else {
                                            toastr.success(response.message);
                                            setTimeout(function() {
                                                window.location.reload();
                                            }, 1000);
                                        }
                                    },
                                    error: function(xhr, status, error) {
                                        console.error(xhr.responseText);
                                        $.alert({
                                            title: 'Ajax Error',
                                            content: 'Something went wrong while deleting.',
                                            type: 'red'
                                        });
                                    }
                                });
                            }
                        },
                        cancel: function() {
                            // Do nothing on cancel
                        }
                    }
                });
            });

        });
    </script>

    <!-- Custom JS -->
    <script src="assets/js/custom.js"></script>
@endsection
