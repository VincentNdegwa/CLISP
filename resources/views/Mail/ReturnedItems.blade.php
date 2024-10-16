<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Return Notification</title>
</head>
<body style="background-color: #f9fafb; color: #4a5568; font-family: 'Inter', sans-serif;">

    <div style="max-width: 600px; margin: 20px auto; padding: 20px; background-color: #ffffff; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); border-radius: 8px;">
        <h1 style="font-size: 24px; font-weight: bold; color: #1a202c;">Notification of Returned Items</h1>
        <p style="margin-top: 20px; color: #718096;">
            Dear <span style="font-weight: 600;">{{ $initiatorBusinessName }}</span>,
        </p>

        <p style="margin-top: 10px;">
            The following items have been returned by <span style="font-weight: 600;">{{ $receiverBusinessName }}</span> to your business:
        </p>

        <!-- Display returned items list -->
        <ul style="margin-top: 20px; padding-left: 20px; color: #4a5568;">
            @foreach ($items as $item)
                <li>{{ $item->name }} (Quantity: {{ $item->quantity }})</li>
            @endforeach
        </ul>

        <p style="margin-top: 20px;">
            Please confirm that you have received the items in good condition.
        </p>

        <!-- Confirm Button -->
        <a href="{{ $confirmationLink }}" style="display: inline-block; margin-top: 30px; padding: 10px 20px; background-color: #f43f5e; color: #ffffff; text-decoration: none; border-radius: 4px;">
            Confirm Items Reception
        </a>

        <p style="margin-top: 30px; color: #a0aec0;">
            We appreciate your timely response to this notification.
        </p>
    </div>

</body>
</html>
