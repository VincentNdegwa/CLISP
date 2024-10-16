<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval & Payment Required</title>
    <style>
        body {
            background-color: #f9fafb;
            color: #4a5568;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            font-weight: 400;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h1 {
            font-size: 24px;
            font-weight: bold;
            color: #1a202c;
        }

        p {
            margin: 10px 0;
        }

        ul {
            margin-top: 20px;
            padding-left: 20px;
            color: #313641;
        }

        .total {
            font-weight: bold;
            font-size: 18px;
        }

        .note {
            color: #4a5568;
            margin-top: 20px;
        }

        .footer {
            margin-top: 30px;
            color: #4a5568;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Approval & Payment Required for Dispatch</h1>

        <p>Dear <strong>{{ $transactionData['receiver_business']['business_name'] }}</strong>,</p>

        <p>We would like to inform you that the following items are pending dispatch to your business. Please review the
            transaction details and proceed with the necessary approval and payment:</p>

        <ul>
            @foreach ($transactionData['items'] as $item)
                <li><strong>Item ID:</strong> {{ $item['item_id'] }}, <strong>Quantity:</strong>
                    {{ $item['quantity'] }}, <strong>Price:</strong> {{ number_format($item['price'], 2) }} KES</li>
            @endforeach
        </ul>

        <p class="total">Total Amount Due: <strong>{{ number_format($totalPrice, 2) }} KES</strong></p>
        <p><strong>Transaction Type:</strong> {{ ucfirst($transactionData['type']) }}</p>

        @if ($transactionData['type'] === 'leasing' || $transactionData['type'] === 'borrowing')
            <p class="note">
                Please note: This transaction will be terminated if not approved within 2 days of the lease start date
                (<strong>{{ $transactionData['lease_start_date'] }}</strong>).
                <strong>Lease End Date:</strong> {{ $transactionData['lease_end_date'] }}.
            </p>
            <p class="note">The agreement is attached below for your reference.</p>
        @endif

        <p class="footer">To complete this process, please click the button below to approve the transaction and proceed
            with the payment:</p>

        <p class="footer">Once we receive your approval and payment, we will dispatch the items promptly.</p>

        <p class="footer">Thank you for your cooperation.</p>
    </div>

</body>

</html>
