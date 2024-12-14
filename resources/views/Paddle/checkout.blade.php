<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @paddleJS
</head>

<body class="font-sans antialiased text-slate-950"></body>
<div class="container">
    <h1>Checkout</h1>
    <p>Click below to proceed with your subscription.</p>

    <x-paddle-button :checkout="$checkout" class="px-8 py-4">
        Subscribe Now
    </x-paddle-button>
</div>

</html>
