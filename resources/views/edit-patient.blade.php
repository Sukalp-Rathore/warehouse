<form id="editPatientForm" method="POST" action="{{ route('patient.update') }}">
    {{-- CSRF Token --}}
    @csrf
    <div class="mb-3">
        <label for="name" class="form-label">Patient Name</label>
        <input type="text" class="form-control" id="name" name="name" placeholder="Enter full patient name"
            required autocomplete="off" value="{{ $patient->name ?? '' }}">
    </div>
    <div class="mb-3">
        <label for="phone" class="form-label">Phone No.</label>
        <input type="tel" maxlength="10" class="form-control" id="phone"
            placeholder="Enter 10 digit Phone number" name="phone" autocomplete="off"
            value="{{ $patient->phone ?? '' }}">
    </div>
    <div class="mb-3">
        <label for="gender" class="form-label">Gender</label>
        <select class="form-select" id="gender" name="gender">
            <option value="Male" {{ isset($patient->gender) && $patient->gender == 'Male' ? 'selected' : '' }}>Male
            </option>
            <option value="Female" {{ isset($patient->gender) && $patient->gender == 'Female' ? 'selected' : '' }}>
                Female</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="age" class="form-label">Age</label>
        <input type="text" class="form-control" id="age" placeholder="Enter Age" name="age"
            autocomplete="off" value="{{ $patient->age ?? '' }}">
    </div>
    <div class="mb-3">
        <label for="address" class="form-label">Address</label>
        <input type="text" class="form-control" id="address" placeholder="Enter address" name="address"
            autocomplete="off" value="{{ $patient->address ?? '' }}">
    </div>
    <div class="mb-3">
        <button class="btn btn-secondary" type="submit">Update Patient</button>
    </div>
</form>

<script>
    $('#editPatientForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                $('#editVendorModal').modal('hide');
                toastr.success(response.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
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
</script>
