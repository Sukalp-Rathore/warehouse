@extends('layout')
@section('content')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.3.0/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">

    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-0">All Patients</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">Search Patients</div>
                    <div class="card-options">
                        <a class="btn btn-primary btn-sm add-patient">Add Patient</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="file-exports" class="table table-bordered text-nowrap w-100">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Name</th>
                                    <th>Mobile No.</th>
                                    <th>Gender</th>
                                    <th>Age</th>
                                    <th>Address</th>
                                    <th>Created By</th>
                                    <th>Options</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($patients as $p)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $p->name }}</td>
                                        <td>{{ $p->phone }}</td>
                                        <td>{{ $p->gender }}</td>
                                        <td>{{ $p->age }}</td>
                                        <td>{{ $p->address }}</td>
                                        <td>{{ $p->created_by }}</td>
                                        <td>
                                            <button class="btn btn-primary btn-sm editBtn"
                                                data-id="{{ $p->_id }}">Edit</button>
                                            <button class="btn btn-danger btn-sm deleteBtn"
                                                data-id="{{ $p->_id }}">Delete</button>
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
    <div class="modal fade" id="addPatientModal" tabindex="-1" aria-labelledby="exampleModalLgLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="exampleModalLgLabel">Add Patient</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="response">
                    <form id="addPatientForm" method="POST" action="{{ route('patient.create') }}">
                        {{-- CSRF Token --}}
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Patient Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="Enter full patient name" required autocomplete="off">
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

            $(document).on('click', '.add-patient', function() {
                $("#addPatientModal").modal('show');
            });

            $('#addPatientForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    type: 'POST',
                    url: $(this).attr('action'),
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('#addPatientModal').modal('hide');
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
                let patientId = $(this).attr('data-id');
                $("#editPatientModal").modal('show');
                $.ajax({
                    url: "{{ route('patient.show.edit') }}",
                    type: 'POST',
                    data: {
                        patientId: patientId,
                    },
                    success: function(response) {
                        if (response.success == false) {
                            alert(response.message);
                        } else {
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
                let patientId = $(this).attr('data-id');

                $.confirm({
                    title: 'Confirm Deletion',
                    content: 'Are you sure you want to delete this patient?',
                    buttons: {
                        confirm: {
                            text: 'Yes, Delete',
                            btnClass: 'btn-red',
                            action: function() {
                                $.ajax({
                                    url: "{{ route('patient.delete') }}",
                                    type: 'POST',
                                    data: {
                                        patientId: patientId,
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
