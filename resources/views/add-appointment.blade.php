@extends('layout')
@section('content')
    <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
        <div>
            <h1 class="page-title fw-medium fs-18 mb-0">Create Appointment</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-12">
            <div class="card custom-card">
                <div class="card-header justify-content-between">
                    <div class="card-title">Create New Appointment</div>
                </div>
                <div class="card-body">
                    <form id="createAppForm" action="{{ route('create.appointment') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="patient_name">Patient Name</label>
                            <select name="name" id="patient_name" class="form-control">
                                <option value="">Select Patient</option>
                                @foreach ($patients as $patient)
                                    <option value="{{ $patient->name }}" data-phone="{{ $patient->phone }}">
                                        {{ $patient->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label for="phone">Patient Contact</label>
                            <input type="text" class="form-control" name="phone" id="phone" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="date">Date</label>
                            <input type="date" class="form-control" name="date" id="date" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="timeslot">Time Slot</label>
                            <input type="time" class="form-control" name="timeslot" id="timeslot" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        $(document).ready(function() {

            $.fn.modal.Constructor.prototype.enforceFocus = function() {}
            $('#patient_name').select2({
                dir: "ltr",
                tags: true,
            });

            $('#patient_name').on('change', function() {
                var phone = $(this).find(':selected').data('phone') || '';
                $('#phone').val(phone);
            });

            $('#createAppForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    type: 'POST',
                    url: $(this).attr('action'),
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        toastr.success(response.message);
                        window.location.href = "{{ route('visits') }}";
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
        });
    </script>
@endsection
