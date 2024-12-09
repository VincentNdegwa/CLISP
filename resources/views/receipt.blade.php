<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $transaction['id'] }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
        }

        .invoice-container {
            max-width: 900px;
            position: relative;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 10px;
            padding: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            color: #4a4a4a;
        }

        .header p {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .info-box {
            width: 48%;
            padding: 15px;
            border-radius: 8px;
            background-color: #f8f9fa;
        }

        .info-box h3 {
            margin: 0 0 10px;
            font-size: 16px;
            color: #4a4a4a;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
        }

        .info-box p {
            margin: 5px 0;
            font-size: 14px;
            color: #555;
        }

        .transaction-status {
            font-size: 16px;
            font-weight: bold;
            color: #fff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;

        }

        .status-pending {
            color: #ffc107;
        }

        .status-completed {
            color: #28a745;
        }

        .status-paid {
            color: #28a745;
        }

        .status-cancelled {
            color: #dc3545;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .items-table th,
        .items-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .items-table th {
            background-color: #f1f1f1;
            font-weight: bold;
        }

        .items-table td {
            font-size: 14px;
        }

        .totals {
            text-align: right;
            margin-top: 10px;
        }

        .totals p {
            margin: 5px 0;
            font-size: 16px;
            color: #333;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
            color: #666;
        }

        .footer p {
            margin: 5px 0;
        }
    </style>
</head>

<body>
    @php
        $isSameCode = $transaction['sender']->code === $transaction['receiver']->code;
    @endphp
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <h1>Invoice</h1>
            <p>Invoice #: {{ $transaction['id'] }}</p>
            <p>Date: {{ \Carbon\Carbon::parse($transaction['created_at'])->format('F j, Y, g:i a') }}</p>
            <!-- Transaction Status -->
            <div class="transaction-status">
                <div class="status-{{ $transaction['status'] }}">
                    {{ ucfirst($transaction['status']) }}
                </div>
            </div>

        </div>

        <!-- Business and Receiver Info -->
        <div class="info-section">
            <!-- Initiator Info -->
            <div class="info-box">
                <h3>Business (Initiator)</h3>
                <p><strong>Business Name:</strong> {{ $transaction['initiator']['business_name'] }}</p>
                <p><strong>Email:</strong> {{ $transaction['initiator']['email'] }}</p>
                <p><strong>Phone:</strong> {{ $transaction['initiator']['phone_number'] }}</p>
                <p><strong>Location:</strong> {{ $transaction['initiator']['location'] }}</p>
            </div>

            <!-- Receiver Info -->
            <div class="info-box">
                <h3>Receiver</h3>
                @if ($transaction['isB2B'] && $transaction['receiver_business'])
                    <p><strong>Business Name:</strong> {{ $transaction['receiver_business']['business_name'] }}</p>
                    <p><strong>Email:</strong> {{ $transaction['receiver_business']['email'] }}</p>
                    <p><strong>Phone:</strong> {{ $transaction['receiver_business']['phone_number'] }}</p>
                    <p><strong>Location:</strong> {{ $transaction['receiver_business']['location'] }}</p>
                @elseif (!$transaction['isB2B'] && $transaction['receiver_customer'])
                    <p><strong>Name:</strong> {{ $transaction['receiver_customer']['full_names'] }}</p>
                    <p><strong>Email:</strong> {{ $transaction['receiver_customer']['email'] }}</p>
                    <p><strong>Phone:</strong> {{ $transaction['receiver_customer']['phone_number'] }}</p>
                    <p><strong>Address:</strong> {{ $transaction['receiver_customer']['address'] }}</p>
                @else
                    <p>No Receiver Information Available</p>
                @endif
            </div>
        </div>

        <!-- Items -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    @if ($isSameCode)
                        <th>Unit Price ({{ $transaction['sender']->code }})</th>
                        <th>Total ({{ $transaction['sender']->code }})</th>
                    @else
                        <th>Unit Price (Sender - {{ $transaction['sender']->code }})</th>
                        <th>Total (Sender - {{ $transaction['sender']->code }})</th>
                        <th>Unit Price (Receiver - {{ $transaction['receiver']->code }})</th>
                        <th>Total (Receiver - {{ $transaction['receiver']->code }})</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($transaction['items'] as $item)
                    <tr>
                        <td>{{ $item['item']['item_name'] }}</td>
                        <td>{{ $item['item']['description'] ?? 'N/A' }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        @if ($isSameCode)
                            <td>{{ $item['sender']->price }}</td>
                            <td>{{ $item['quantity'] * $item['sender']->price }}</td>
                        @else
                            <td>{{ $item['sender']->price }}</td>
                            <td>{{ $item['quantity'] * $item['sender']->price }}</td>
                            <td>{{ $item['receiver']->price }}</td>
                            <td>{{ $item['quantity'] * $item['receiver']->price }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
            @if ($transaction['sender']->code !== $transaction['receiver']->code)
                <p>
                    <strong>Subtotal:</strong>
                    {{ $transaction['sender']->price }} {{ $transaction['sender']->code }} ||
                    {{ $transaction['receiver']->price }} {{ $transaction['receiver']->code }}
                </p>
            @else
                <p><strong>Subtotal:</strong> {{ $transaction['totalPrice'] }} KES</p>
            @endif
        </div>

        <!-- Payment Details -->
        <div class="">

            <h3>Payment Information</h3>
            <p>{{ $payment['payment_type'] }}</p>
            @if (isset($payment['payment_details']) && is_array($payment['payment_details']))
                <ul style="list-style: none; padding: 0;">
                    @foreach ($payment['payment_details'] as $detail)
                        <li>
                            <strong>{{ $detail['name'] }}:</strong> {{ $detail['value'] }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for doing business with us!</p>
            <p>If you have any questions, please contact {{ $transaction['initiator']['email'] }}.</p>
        </div>
    </div>


</body>

</html>
