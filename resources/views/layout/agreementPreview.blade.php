<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Agreement Preview</title>


    <style>
        /* Custom styles */
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        body {
            padding: 0 !important;
            position: relative;
        }

        /* Header styling */
        header {

            width: 100%;
            height: 60px;
            background: linear-gradient(to right, #f8fafc, #f1f5f9);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            color: #020617;
            z-index: 1000;
        }

        .back-button {
            display: flex;
            align-items: center;
            background: #020617;
            border-radius: 8px;
            padding: 8px 12px;
            transition: background-color 0.3s ease;
            cursor: pointer;
        }

        .back-button:hover {
            background: #0f172a;
        }

        .back-button svg {
            margin-right: 8px;
            fill: #020617;
        }

        .header-title {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            flex-grow: 1;
        }

        a {
            text-decoration: none;
            color: white;
        }
    </style>
</head>

<body class="bg-gray-900">

    <!-- Header Section -->
    <header class="rounded-b-lg">
        <!-- Back Button -->
        <a href="javascript:history.back()" class="back-button">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="white">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1.707-8.707a1 1 0 010-1.414l3-3a1 1 0 111.414 1.414L10.414 9H14a1 1 0 110 2h-3.586l2.293 2.293a1 1 0 01-1.414 1.414l-3-3z"
                    clip-rule="evenodd" />
            </svg>
            <span>Back</span>
        </a>

        <div class="header-title">Agreement Preview</div>

        <div></div>
    </header>

    <!-- Main Content -->
    <div class="mt-16 p-6">
        @yield('content')
    </div>

</body>

</html>
