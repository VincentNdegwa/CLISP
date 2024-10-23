<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    <script>
        // Function to dynamically load the PayPal SDK
        function loadPayPalScript(currency) {
            const script = document.createElement('script');
            script.src =
                `https://www.paypal.com/sdk/js?client-id={{ config('paypal.client') }}&currency=${currency}&components=buttons&enable-funding=venmo,paylater,card`;
            document.head.appendChild(script);
        }

        // Fetch the business data from local storage
        document.addEventListener('DOMContentLoaded', () => {
            let defaultBusiness = JSON.parse(localStorage.getItem('default_business'));

            let currency = defaultBusiness ? defaultBusiness.currency_code : 'USD';

            loadPayPalScript(currency);
        });
    </script>

    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased text-slate-950">
    @inertia
</body>

</html>
