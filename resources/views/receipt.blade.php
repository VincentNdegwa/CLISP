<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $transaction['id'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .receipt-container {
            max-width: 700px;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2,
        h3 {
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-header {
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
            text-transform: uppercase;
            border-bottom: 2px solid #000;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table th,
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .items-table th {
            background-color: #f0f0f0;
        }

        .total {
            font-size: 20px;
            font-weight: bold;
            text-align: right;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="header">
            <h2>Transaction Receipt</h2>
            <h3>Receipt #{{ $transaction['id'] }}</h3>
            <p>Date: {{ \Carbon\Carbon::parse($transaction['created_at'])->format('F j, Y, g:i a') }}</p>
        </div>

        <!-- Initiator and Receiver Info -->
        <div class="section">
            <div class="section-header">Transaction Details</div>
            <div class="info-row">
                <div><strong>Type:</strong> {{ ucfirst($transaction['type']) }}</div>
                <div><strong>Status:</strong> {{ ucfirst($transaction['status']) }}</div>
            </div>
            <div class="info-row">
                <div><strong>Transaction Type:</strong>
                    {{ $transaction['transaction_type'] == 'Outgoing' ? 'Outgoing (Purchase)' : 'Incoming (Sale)' }}</div>
                <div><strong>Total Price:</strong> {{ number_format($transaction['totalPrice'], 2) }} KES</div>
            </div>
        </div>

        <!-- Initiator and Receiver (Business/Customer) -->
        <div class="section">
            <div class="section-header">Customer Information</div>
            <div class="info-row">
                <div><strong>Initiator:</strong> {{ $transaction['initiator']['business_name'] }}</div>
                <div><strong>Phone:</strong> {{ $transaction['initiator']['phone_number'] }}</div>
            </div>
            <div class="info-row">
                <div><strong>Email:</strong> {{ $transaction['initiator']['email'] }}</div>
                <div><strong>Location:</strong> {{ $transaction['initiator']['location'] }}</div>
            </div>
            @if ($transaction['isB2B'] && $transaction['receiver_business'])
                <div class="info-row">
                    <div><strong>Receiver (Business):</strong> {{ $transaction['receiver_business']['business_name'] }}</div>
                    <div><strong>Location:</strong> {{ $transaction['receiver_business']['location'] }}</div>
                </div>
            @elseif(!$transaction['isB2B'] && $transaction['receiver_customer'])
                <div class="info-row">
                    <div><strong>Receiver (Customer):</strong> {{ $transaction['receiver_customer']['full_names'] }}</div>
                    <div><strong>Phone:</strong> {{ $transaction['receiver_customer']['phone_number'] }}</div>
                </div>
            @endif
        </div>

        <!-- Items Table -->
        <div class="section">
            <div class="section-header">Items</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaction['items'] as $item)
                        <tr>
                            <td>{{ $item['item']['item_name'] }}</td>
                            <td>{{ $item['quantity'] }} {{ $item['item']['unit'] }}</td>
                            <td>{{ number_format($item['price'], 2) }} KES</td>
                            <td>{{ number_format($item['quantity'] * $item['price'], 2) }} KES</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Total Section -->
        <div class="total">
            Total: {{ number_format($transaction['totalPrice'], 2) }} KES
        </div>

        <!-- Footer -->
        <div class="header">
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>

</html>
