<!-- Patient Info -->
<div class="mb-3">
    <strong>Patient:</strong> <span id="patient_name">{{ $visit->name }}</span><br>
    <strong>Phone:</strong> <span id="patient_phone">{{ $visit->phone }}</span><br>
    <strong>Doctor:</strong> <span id="doctor_name">{{ $visit->doctor_name }}</span><br>
    <strong>Age:</strong> <span id="patient_age">{{ $visit->age }}</span><br>
    <strong>Gender:</strong> <span id="patient_gender">{{ $visit->gender }}</span><br>
    <strong>Address:</strong> <span id="patient_address">{{ $visit->address }}</span><br>
    <strong>Visit Fee:</strong> ₹<span id="visit_fee">{{ $visit->visit_fee }}</span><br>
</div>

<!-- Medicines Table -->
<h6>Medicines</h6>

<!-- Select2 Dropdown -->
<select id="medicineSelect" class="form-select" style="width:100%">
    <option value="">-- Select Medicine --</option>
    @foreach ($inventory as $item)
        <option value="{{ $item->_id }}">{{ $item->product_name }}</option>
    @endforeach
</select>

<!-- Medicines Table -->
<table class="table table-bordered mt-3" id="medicines_table">
    <thead>
        <tr>
            <th>Medicine</th>
            <th>Duration</th>
            <th>When to Take</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<!-- Injections Table -->
<h6 class="mt-3">Injections</h6>
<table class="table table-bordered" id="injections_table">
    <thead>
        <tr>
            <th>Injection</th>
            <th>Price</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>
<button class="btn btn-sm btn-primary" type="button" id="addInjectionRow">+ Add Injection</button>

<!-- Checkup Report -->
<div class="mt-3">
    <label for="checkup_report" class="form-label">Checkup Report</label>
    <textarea class="form-control" id="checkup_report" rows="3"></textarea>
</div>

<button class="btn btn-success" id="finishCheckup">Finish Checkup</button>

<script>
    $(document).ready(function() {
        // Initialize Select2
        $.fn.modal.Constructor.prototype.enforceFocus = function() {}

        $('#medicineSelect').select2({
            placeholder: "Select a medicine",
            allowClear: true,
            dropdownParent: $('#editVisitModal').closest('.modal-content'),
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
        });
    });
</script>
