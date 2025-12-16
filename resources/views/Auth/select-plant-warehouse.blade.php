<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-vertical-style="overlay" data-theme-mode="light"
    data-header-styles="light" data-menu-styles="light" data-toggled="close">

<head>
    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Select Plant & Warehouse - WMS</title>
    <meta name="Description" content="WMS">
    <meta name="Author" content="WMS">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" href="assets/images/brand-logos/1.png" type="image/x-icon">

    <!-- Main Theme Js -->
    <script src="assets/js/authentication-main.js"></script>

    <!-- Bootstrap Css -->
    <link id="style" href="assets/libs/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Style Css -->
    <link href="assets/css/styles.css" rel="stylesheet">

    <!-- Icons Css -->
    <link href="assets/css/icons.css" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Toastify CSS -->
    <link rel="stylesheet" href="assets/libs/toastify-js/src/toastify.css">

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>

<body class="bg-white">
    <div class="row authentication authentication-cover-main mx-0">
        <div class="col-xxl-6 col-xl-7">
            <div class="row justify-content-center align-items-center h-100">
                <div class="col-xxl-8 col-xl-9 col-lg-6 col-md-6 col-sm-8 col-12">
                    <span style="font-size: 20px; font-weight: bold;color:black">WMS</span>
                    <div class="card custom-card my-auto border">
                        <div class="card-body p-5">
                            <h4 class="mb-3 text-center">Select Plant & Warehouse</h4>
                            <p class="mb-4 text-muted op-7 fw-normal text-center">
                                Please select the plant and warehouse you want to access
                            </p>

                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <form id="selectionForm" action="{{ route('plant-warehouse.select') }}" method="POST">
                                @csrf
                                <div class="row gy-3">
                                    <div class="col-xl-12 form-group">
                                        <label for="plant_id" class="form-label text-default">Select Plant <span class="text-danger">*</span></label>
                                        <select class="form-select @error('plant_id') is-invalid @enderror" 
                                                id="plant_id" name="plant_id" required>
                                            <option value="">-- Select Plant --</option>
                                            @foreach($plants as $plant)
                                                @php
                                                    $plantId = (string)($plant->string_id ?? $plant->_id);
                                                @endphp
                                                <option value="{{ $plantId }}" 
                                                        {{ old('plant_id') == $plantId ? 'selected' : '' }}>
                                                    {{ $plant->plant_name }} ({{ $plant->plant_code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('plant_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-xl-12 form-group">
                                        <label for="warehouse_id" class="form-label text-default">Select Warehouse <span class="text-danger">*</span></label>
                                        <select class="form-select @error('warehouse_id') is-invalid @enderror" 
                                                id="warehouse_id" name="warehouse_id" required disabled>
                                            <option value="">-- Select Plant First --</option>
                                        </select>
                                        @error('warehouse_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text" id="warehouse-help-text">
                                            Please select a plant first to see available warehouses.
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-primary" id="submit-btn" disabled>Continue</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-6 col-xl-5 col-lg-12 d-xl-block d-none px-0">
            <div class="authentication-cover">
                <div class="aunthentication-cover-content text-center">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="text-white">
                                    <h1 class="mb-3">Welcome to WMS</h1>
                                    <p class="fs-15 fw-semibold">Warehouse Management System</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const plantSelect = document.getElementById('plant_id');
            const warehouseSelect = document.getElementById('warehouse_id');
            const submitBtn = document.getElementById('submit-btn');
            const warehouseHelpText = document.getElementById('warehouse-help-text');
            
            // Warehouse data grouped by plant
            const warehousesByPlant = @json($warehousesByPlant);
            
            // Handle plant selection change
            plantSelect.addEventListener('change', function() {
                const selectedPlantId = this.value;
                
                // Clear warehouse options
                warehouseSelect.innerHTML = '<option value="">-- Select Warehouse --</option>';
                warehouseSelect.disabled = true;
                submitBtn.disabled = true;
                
                if (!selectedPlantId) {
                    warehouseHelpText.textContent = 'Please select a plant first to see available warehouses.';
                    return;
                }
                
                // Get warehouses for selected plant
                const warehouses = warehousesByPlant[selectedPlantId] || [];
                
                if (warehouses.length === 0) {
                    warehouseSelect.innerHTML = '<option value="">No warehouses available for this plant</option>';
                    warehouseHelpText.textContent = 'No warehouses are available for the selected plant.';
                    return;
                }
                
                // Populate warehouse options
                warehouses.forEach(function(warehouse) {
                    const option = document.createElement('option');
                    option.value = warehouse._id;
                    option.textContent = warehouse.warehouse_name + ' (' + warehouse.warehouse_code + ')';
                    warehouseSelect.appendChild(option);
                });
                
                // Enable warehouse select
                warehouseSelect.disabled = false;
                warehouseHelpText.textContent = 'Please select a warehouse.';
                
                // Check if there's an old value to restore
                const oldWarehouseId = '{{ old("warehouse_id") }}';
                if (oldWarehouseId) {
                    warehouseSelect.value = oldWarehouseId;
                    updateSubmitButton();
                }
            });
            
            // Handle warehouse selection change
            warehouseSelect.addEventListener('change', function() {
                updateSubmitButton();
            });
            
            // Function to update submit button state
            function updateSubmitButton() {
                if (plantSelect.value && warehouseSelect.value) {
                    submitBtn.disabled = false;
                } else {
                    submitBtn.disabled = true;
                }
            }
            
            // Initialize on page load if plant is already selected
            if (plantSelect.value) {
                plantSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
</body>
</html>

