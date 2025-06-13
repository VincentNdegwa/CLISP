<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="mode"> 

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'pulse-slower': 'pulse 6s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                }
            },
            darkMode: 'class'
        }
    </script>
    @paddleJS
</head>

<body class="font-sans antialiased bg-slate-100 text-slate-900 dark:bg-slate-900 dark:text-slate-50 bg-gradient-to-br from-slate-50 to-slate-200 dark:from-slate-950 dark:to-slate-900">
    <!-- Decorative background elements -->
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute top-0 right-0 w-1/3 h-1/3 bg-gradient-to-bl from-rose-500/10 to-blue-500/10 rounded-bl-full blur-3xl animate-pulse-slow"></div>
        <div class="absolute bottom-0 left-0 w-1/3 h-1/3 bg-gradient-to-tr from-amber-500/10 to-purple-500/10 rounded-tr-full blur-3xl animate-pulse-slower"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-1/2 h-1/2 bg-gradient-to-t from-blue-500/5 to-slate-500/5 rounded-full blur-3xl"></div>
    </div>

    <div class="min-h-screen flex items-center justify-center p-4">
        @if ($payment_status == null)
            <div class="bg-white dark:bg-slate-800 shadow-xl rounded-xl w-full max-w-md p-8 border border-slate-200/50 dark:border-slate-700/50 relative overflow-hidden">
                <!-- Decorative corner accent -->
                <div class="absolute top-0 right-0 h-20 w-20">
                    <div class="absolute top-0 right-0 h-full w-full bg-gradient-to-bl from-rose-500 to-rose-600 -rotate-45 transform origin-top-right"></div>
                </div>
                
                <!-- Header Section -->
                <div class="text-center mb-8 relative">
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Checkout</h1>
                    <p class="text-slate-500 dark:text-slate-400 mt-2">Complete your subscription in a few simple steps.</p>
                </div>

                <!-- Business Details -->
                @if (isset($business))
                    <div class="bg-slate-50 dark:bg-slate-700/50 p-5 rounded-lg border border-slate-200 dark:border-slate-600/50 mb-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-200 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-rose-500 dark:text-rose-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm3 1h6v4H7V5zm8 8v2H5v-2h10zm0-3v2H5v-2h10z" clip-rule="evenodd" />
                            </svg>
                            Business Details
                        </h2>
                        <div class="mt-3 space-y-2 text-slate-600 dark:text-slate-300">
                            <p class="flex items-center text-sm">
                                <span class="font-semibold min-w-24 text-slate-700 dark:text-slate-300">Name:</span> 
                                <span class="ml-2">{{ $business->business_name }}</span>
                            </p>
                            <p class="flex items-center text-sm">
                                <span class="font-semibold min-w-24 text-slate-700 dark:text-slate-300">Email:</span> 
                                <span class="ml-2">{{ $business->email }}</span>
                            </p>
                            <p class="flex items-center text-sm">
                                <span class="font-semibold min-w-24 text-slate-700 dark:text-slate-300">Location:</span> 
                                <span class="ml-2">{{ $business->location }}</span>
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Plan Details -->
                @if (isset($subscription))
                    <div class="bg-slate-50 dark:bg-slate-700/50 p-5 rounded-lg border border-slate-200 dark:border-slate-600/50 mb-8 shadow-sm">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-200 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-rose-500 dark:text-rose-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                                        <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $subscription->name }} Plan
                                </h2>
                                <p class="text-slate-500 dark:text-slate-400 text-sm mt-2 ml-7">
                                    {{ $subscription->description }}
                                </p>
                            </div>
                            <div class="bg-gradient-to-r from-rose-500 to-rose-600 text-white px-3 py-1 rounded-full text-xs font-semibold shadow-sm">
                                {{ $subscription->billing_cycle }}
                            </div>
                        </div>
                        
                        <div class="mt-6 bg-white dark:bg-slate-800 rounded-md p-4 border border-slate-200 dark:border-slate-600 shadow-sm">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500 dark:text-slate-400 text-sm">Subscription Price</span>
                                <span class="text-slate-800 dark:text-white font-semibold text-lg">
                                    {{ $subscription->currency }} {{ number_format($subscription->price, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Action Button -->
                @if ($checkout != null)
                    <x-paddle-button :checkout="$checkout"
                        class="w-full bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white text-md font-semibold py-4 px-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                        Subscribe Now
                    </x-paddle-button>
                @endif

                <!-- Footer Section -->
                <div class="text-center mt-8">
                    <p class="text-sm text-slate-400 dark:text-slate-500">
                        By subscribing, you agree to our <a href="#" class="text-rose-500 hover:text-rose-600 dark:text-rose-400 dark:hover:text-rose-300 underline">Terms</a> and
                        <a href="#" class="text-rose-500 hover:text-rose-600 dark:text-rose-400 dark:hover:text-rose-300 underline">Privacy Policy</a>.
                    </p>
                </div>
            </div>
        @elseif ($payment_status == 'subscribed')
            <div class="bg-white dark:bg-slate-800 shadow-xl rounded-xl w-full max-w-md p-8 border border-slate-200/50 dark:border-slate-700/50 relative overflow-hidden">
                <!-- Success decoration -->
                <div class="absolute top-0 right-0 h-20 w-20">
                    <div class="absolute top-0 right-0 h-full w-full bg-gradient-to-bl from-emerald-500 to-emerald-600 -rotate-45 transform origin-top-right"></div>
                </div>
                
                <!-- Success icon -->
                <div class="flex justify-center my-6">
                    <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                
                <!-- Header Section -->
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Active Subscription</h1>
                    <p class="text-slate-500 dark:text-slate-400 mt-2">Thank you for subscribing to the {{ $subscription->name }} Plan.</p>
                </div>

                <!-- Business Details -->
                @if (isset($business))
                    <div class="bg-slate-50 dark:bg-slate-700/50 p-5 rounded-lg border border-slate-200 dark:border-slate-600/50 mb-8 shadow-sm">
                        <h2 class="text-lg font-semibold text-slate-700 dark:text-slate-200 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-emerald-500 dark:text-emerald-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm3 1h6v4H7V5zm8 8v2H5v-2h10zm0-3v2H5v-2h10z" clip-rule="evenodd" />
                            </svg>
                            Business Details
                        </h2>
                        <div class="mt-3 space-y-2 text-slate-600 dark:text-slate-300">
                            <p class="flex items-center text-sm">
                                <span class="font-semibold min-w-24 text-slate-700 dark:text-slate-300">Name:</span> 
                                <span class="ml-2">{{ $business->business_name }}</span>
                            </p>
                            <p class="flex items-center text-sm">
                                <span class="font-semibold min-w-24 text-slate-700 dark:text-slate-300">Email:</span> 
                                <span class="ml-2">{{ $business->email }}</span>
                            </p>
                            <p class="flex items-center text-sm">
                                <span class="font-semibold min-w-24 text-slate-700 dark:text-slate-300">Location:</span> 
                                <span class="ml-2">{{ $business->location }}</span>
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Action Button -->
                <a href="{{ route('dashboard') }}"
                    class="w-full block bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white text-md font-semibold py-4 px-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                    </svg>
                    Go to Dashboard
                </a>

                <!-- Subscription Details -->
                <div class="mt-8 text-center">
                    <div class="text-sm text-slate-400 dark:text-slate-500 flex items-center justify-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Your subscription is active and secure
                    </div>
                </div>
            </div>
        @endif
    </div>
</body>

</html>
