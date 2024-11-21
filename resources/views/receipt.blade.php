<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $transaction['id'] }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }

        .invoice-container {
            max-width: 800px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }

        .header p {
            margin: 5px 0;
            color: #777;
        }

        .section-header {
            font-weight: bold;
            font-size: 18px;
            margin-top: 30px;
            margin-bottom: 15px;
            color: #444;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .info-row div {
            color: #555;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .items-table th,
        .items-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .items-table th {
            background-color: #f2f2f2;
            color: #333;
        }

        .total {
            margin-top: 20px;
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            color: #777;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <h1>Invoice</h1>
            <p>Invoice #{{ $transaction['id'] }}</p>
            <p>Date: {{ \Carbon\Carbon::parse($transaction['created_at'])->format('F j, Y, g:i a') }}</p>
        </div>

        <!-- Business and Customer Info -->
        <div class="section">
            <div class="section-header">Business Information</div>
            <div class="info-row">
                <div><strong>Business Name:</strong> {{ $transaction['initiator']['business_name'] }}</div>
                <div><strong>Phone:</strong> {{ $transaction['initiator']['phone_number'] }}</div>
            </div>
            <div class="info-row">
                <div><strong>Email:</strong> {{ $transaction['initiator']['email'] }}</div>
                <div><strong>Location:</strong> {{ $transaction['initiator']['location'] }}</div>
            </div>
        </div>

        <div class="section">
            <div class="section-header">Customer Information</div>
            @if ($transaction['isB2B'] && $transaction['receiver_business'])
                <div class="info-row">
                    <div><strong>Receiver (Business):</strong> {{ $transaction['receiver_business']['business_name'] }}
                    </div>
                    <div><strong>Location:</strong> {{ $transaction['receiver_business']['location'] }}</div>
                </div>
            @elseif(!$transaction['isB2B'] && $transaction['receiver_customer'])
                <div class="info-row">
                    <div><strong>Customer Name:</strong> {{ $transaction['receiver_customer']['full_names'] }}</div>
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
                        <th>Unit Price (KES)</th>
                        <th>Total (KES)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaction['items'] as $item)
                        <tr>
                            <td>{{ $item['item']['item_name'] }}</td>
                            <td>{{ $item['quantity'] }} {{ $item['item']['unit'] }}</td>
                            <td>{{ number_format($item['price'], 2) }}</td>
                            <td>{{ number_format($item['quantity'] * $item['price'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Total Amount -->
        <div class="total">
            Grand Total: {{ number_format($transaction['totalPrice'], 2) }} KES
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for choosing our services!</p>
            <p>If you have any questions, please contact us at {{ $transaction['initiator']['email'] }}.</p>
        </div>
    </div>
</body>

</html>
