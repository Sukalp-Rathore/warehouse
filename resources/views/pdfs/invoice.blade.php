<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Medical Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .clinic-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .clinic-address {
            font-size: 14px;
            color: #666;
        }
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .patient-info, .invoice-details {
            width: 48%;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .info-row {
            margin-bottom: 8px;
        }
        .label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .total-section {
            text-align: right;
            margin-top: 20px;
        }
        .total-row {
            margin-bottom: 5px;
        }
        .grand-total {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 10px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="clinic-name">HEALTH CLINIC</div>
        <div class="clinic-address">123 Medical Center Drive, Healthcare City, HC 12345</div>
        <div class="clinic-address">Phone: (555) 123-4567 | Email: info@healthclinic.com</div>
    </div>

    <div class="invoice-info">
        <div class="patient-info">
            <div class="section-title">Patient Information</div>
            <div class="info-row">
                <span class="label">Name:</span>
                <span>{{ $visit->name }}</span>
            </div>
            <div class="info-row">
                <span class="label">Phone:</span>
                <span>{{ $visit->phone }}</span>
            </div>
            <div class="info-row">
                <span class="label">Age:</span>
                <span>{{ $visit->age }} years</span>
            </div>
            <div class="info-row">
                <span class="label">Gender:</span>
                <span>{{ $visit->gender }}</span>
            </div>
            <div class="info-row">
                <span class="label">Address:</span>
                <span>{{ $visit->address }}</span>
            </div>
        </div>

        <div class="invoice-details">
            <div class="section-title">Invoice Details</div>
            <div class="info-row">
                <span class="label">Invoice #:</span>
                <span>INV-{{ str_pad($visit->_id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="info-row">
                <span class="label">Date:</span>
                <span>{{ $visit->visit_date ? $visit->visit_date->format('d/m/Y') : now()->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Doctor:</span>
                <span>{{ $visit->doctor_name }}</span>
            </div>
            <div class="info-row">
                <span class="label">Status:</span>
                <span>{{ $visit->status ?? 'Completed' }}</span>
            </div>
        </div>
    </div>

    <div class="section-title">Services & Charges</div>
    
    <table class="table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Details</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Consultation Fee</td>
                <td>Medical consultation and examination</td>
                <td>₹{{ number_format($visit->visit_fee, 2) }}</td>
            </tr>
            
            @if(!empty($visit->injections))
                @foreach($visit->injections as $injection)
                <tr>
                    <td>Injection</td>
                    <td>{{ $injection['name'] ?? 'N/A' }}</td>
                    <td>₹{{ number_format($injection['price'] ?? 0, 2) }}</td>
                </tr>
                @endforeach
            @endif
            
            @if(!empty($visit->procedures))
                @foreach($visit->procedures as $procedure)
                <tr>
                    <td>Procedure</td>
                    <td>{{ $procedure['name'] ?? 'N/A' }}</td>
                    <td>₹{{ number_format($procedure['price'] ?? 0, 2) }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-row">
            <span class="label">Subtotal:</span>
            <span>₹{{ number_format($visit->visit_fee, 2) }}</span>
        </div>
        @if(!empty($visit->injections))
        <div class="total-row">
            <span class="label">Injections:</span>
            <span>₹{{ number_format(collect($visit->injections)->sum('price'), 2) }}</span>
        </div>
        @endif
        @if(!empty($visit->procedures))
        <div class="total-row">
            <span class="label">Procedures:</span>
            <span>₹{{ number_format(collect($visit->procedures)->sum('price'), 2) }}</span>
        </div>
        @endif
        <div class="total-row grand-total">
            <span class="label">Total Amount:</span>
            <span>₹{{ number_format($visit->total_charge, 2) }}</span>
        </div>
    </div>

    @if(!empty($visit->diagnosis))
    <div style="margin-top: 30px;">
        <div class="section-title">Diagnosis</div>
        <p>{{ $visit->diagnosis }}</p>
    </div>
    @endif

    @if(!empty($visit->medicines))
    <div style="margin-top: 30px;">
        <div class="section-title">Prescribed Medicines</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>Duration</th>
                    <th>Instructions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($visit->medicines as $medicine)
                <tr>
                    <td>{{ $medicine['name'] ?? 'N/A' }}</td>
                    <td>{{ $medicine['duration'] ?? 'N/A' }}</td>
                    <td>{{ $medicine['time'] ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(!empty($visit->remarks))
    <div style="margin-top: 30px;">
        <div class="section-title">Remarks</div>
        <p>{{ $visit->remarks }}</p>
    </div>
    @endif

    <div class="footer">
        <p>Thank you for choosing our clinic. For any queries, please contact us.</p>
        <p>This is a computer generated invoice. No signature required.</p>
    </div>
</body>
</html>
