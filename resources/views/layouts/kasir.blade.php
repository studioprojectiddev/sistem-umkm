<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
    <title>Kasir</title>

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            margin: 0;
            background: #f4f6fb;
            font-family: system-ui, -apple-system, BlinkMacSystemFont;
            overflow: hidden;
        }
    </style>
</head>
<body>

    {{-- CONTENT FULL PAGE --}}
    @yield('content')

</body>
</html>