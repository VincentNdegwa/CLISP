<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agreement Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .agreement-container {
            background-color: #ffffff;
            padding: 30px;
            margin: 0 auto;
            max-width: 800px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }

        .content {
            margin-top: 20px;
            line-height: 1.6;
            font-size: 16px;
            color: #333;
        }

        .content h3 {
            color: #555;
        }

        .details-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .details-table td,
        .details-table th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .details-table th {
            background-color: #f2f2f2;
            text-align: left;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
        }

        .signatures {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-block {
            width: 40%;
            text-align: center;
        }

        .signature-line {
            margin-top: 60px;
            border-bottom: 1px solid #333;
        }

        .company-logo {
            width: 150px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

    <div class="agreement-container">
        <!-- Header with Business Information -->
        <div class="header">
            <img class="company-logo" src="{{ asset('path-to-your-logo.png') }}" alt="Company Logo">
            <h1>Lease/Borrow Agreement</h1>
            <p>{{ $company_name }}<br>
                {{ $company_address }}<br>
                {{ $company_phone }} | {{ $company_email }}</p>
        </div>

        <!-- Agreement Content -->
        <div class="content">
            <h3>Agreement Details</h3>
            <p>This Agreement (the "Agreement") is entered into on {{ date('F j, Y') }} between:</p>
            <strong>Lessor/Borrower:</strong> {{ $lessor_name }}<br>
            <strong>Lessee/Borrowee:</strong> {{ $lessee_name }}<br><br>

            <h3>Terms and Conditions</h3>
            <p>
                This agreement outlines the terms and conditions under which the following item(s) will be leased or
                borrowed:
            </p>

            <!-- Lease or Borrow Items -->
            <table class="details-table">
                <tr>
                    <th>Item Description</th>
                    <th>Quantity</th>
                    <th>Duration</th>
                    <th>Amount (if any)</th>
                </tr>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item['description'] }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>{{ $item['duration'] }}</td>
                        <td>{{ $item['amount'] }}</td>
                    </tr>
                @endforeach
            </table>

            <h3>Responsibilities</h3>
            <p>The {{ $lessee_name }} agrees to return the item(s) in the same condition as provided, on or before
                {{ $return_date }}. Any damages or losses will be compensated as per the agreed terms. The
                {{ $lessor_name }} reserves the right to charge fees or penalties as defined in the agreement.</p>

            <h3>Termination of Agreement</h3>
            <p>This agreement will be terminated upon the return of all leased/borrowed items or upon written
                notification by either party, subject to the conditions outlined in this document.</p>

            <h3>Signatures</h3>
            <p>The parties hereby agree to the terms and conditions stated above.</p>
        </div>

        <!-- Signatures Section -->
        <div class="signatures">
            <div class="signature-block">
                <div class="signature-line"></div>
                <p>Signature of Lessor/Borrower</p>
                <p>{{ $lessor_name }}</p>
            </div>
            <div class="signature-block">
                <div class="signature-line"></div>
                <p>Signature of Lessee/Borrowee</p>
                <p>{{ $lessee_name }}</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Generated on {{ date('F j, Y') }} by {{ $company_name }}.</p>
        </div>
    </div>

</body>

</html>
