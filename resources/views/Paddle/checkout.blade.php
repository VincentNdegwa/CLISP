<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    @paddleJS
</head>

<body class="font-sans antialiased bg-gray-100 text-gray-900">
    <div class="min-h-screen flex items-center justify-center">

        @if ($payment_status == null)
            <div class="bg-white shadow-md rounded-lg w-full max-w-md p-8">
                <!-- Header Section -->
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-semibold text-gray-800">Checkout</h1>
                    <p class="text-gray-600 mt-2">Complete your subscription in a few simple steps.</p>
                </div>

                <!-- Business Details -->
                @if (isset($business))
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
                @endif

                <!-- Plan Details -->
                @if (isset($subscription))
                    <div class="bg-gray-50 p-4 rounded-md border border-gray-200 mb-6">
                        <h2 class="text-lg font-medium text-gray-700">
                            {{ $subscription->name }} Plan
                        </h2>
                        <p class="text-gray-500 text-sm mt-1">
                            {{ $subscription->description }}
                        </p>
                        <div class="flex justify-between items-center mt-4">
                            <span class="text-gray-800 text-lg font-semibold">
                                {{ $subscription->currency }} {{ number_format($subscription->price, 2) }}
                            </span>
                            <span class="text-sm text-gray-500">
                                Billed {{ $subscription->billing_cycle }}
                            </span>
                        </div>
                    </div>
                @endif


                <!-- Action Button -->
                @if ($checkout != null)
                    <x-paddle-button :checkout="$checkout"
                        class="w-full bg-red-600 hover:bg-red-700 text-white text-md font-medium p-3 rounded-md">
                        Subscribe Now
                    </x-paddle-button>
                @endif

                <!-- Footer Section -->
                <div class="text-center mt-6">
                    <p class="text-sm text-gray-500">
                        By subscribing, you agree to our <a href="#" class="text-red-500 underline">Terms</a> and
                        <a href="#" class="text-red-500 underline">Privacy Policy</a>.
                    </p>
                </div>
            </div>
        @elseif ($payment_status == 'subscribed')
            <div class="bg-white shadow-md rounded-lg w-full max-w-md p-8">
                <!-- Header Section -->
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-semibold text-gray-800">Active Subscription</h1>
                    <p class="text-gray-600 mt-2">Thank you for subscribing to the {{ $subscription->name }} Plan.</p>
                </div>

                <!-- Business Details -->
                @if (isset($business))
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
                @endif

                <!-- Action Button -->
                <a href="{{ route('dashboard') }}"
                    class="w-full block bg-green-600 hover:bg-green-700 text-white text-md font-medium p-3 rounded-md text-center">
                    Go to Dashboard
                </a>
            </div>
        @endif
    </div>
</body>

</html>
