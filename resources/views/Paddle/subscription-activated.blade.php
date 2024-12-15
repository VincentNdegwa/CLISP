<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="font-sans antialiased bg-gray-100 text-gray-900">
    <div class="min-h-screen flex items-center justify-center">

        <div class="bg-white shadow-md rounded-lg w-full max-w-md p-8">
            <!-- Header Section -->
            <div class="text-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Subscription Activated</h1>
                <p class="text-gray-600 mt-2">Thank you for activating your subscription!</p>
            </div>

            <!-- Business Details -->
            <div class="bg-gray-50 p-4 rounded-md border border-gray-200 mb-6">
                <h2 class="text-lg font-medium text-gray-700">Business Details</h2>
                <p class="text-sm text-gray-600 mt-2">
                    <span class="font-semibold">Name:</span> {{ $business->business_name }}
                </p>
                <p class="text-sm text-gray-600 mt-1">
                    <span class="font-semibold">Email:</span> {{ $business->email }}
                </p>
                <p class="text-sm text-gray-600 mt-1">
                    <span class="font-semibold">Location:</span> {{ $business->location }}
                </p>
            </div>

            <!-- Action Button -->
            <a href="{{ route('dashboard') }}"
                class="w-full block bg-green-600 hover:bg-green-700 text-white text-md font-medium p-3 rounded-md text-center">
                Go to Dashboard
            </a>
        </div>
    </div>
</body>

</html>
