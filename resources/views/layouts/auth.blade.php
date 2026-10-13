<!DOCTYPE html>
<html lang="fr">
<head>
    @include('layouts.head')
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/videzma-admin.css').'?v='.filemtime(public_path('assets/css/videzma-admin.css')) }}">
    @yield('css')
</head>
<body class="vz-app">
    @yield('content')
</body>
</html>
