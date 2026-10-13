<!DOCTYPE html>
<html lang="fr">
<head>
    @include('layouts.head')
    <link rel="stylesheet" href="{{ asset('assets/css/vendors/bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/videzma-admin.css').'?v='.filemtime(public_path('assets/css/videzma-admin.css')) }}">
    @yield('css')
    @stack('styles')
</head>
<body class="vz-app">
    <a class="vz-skip" href="#main-content">Aller au contenu</a>
    @include('layouts.sidebar')
    <div class="vz-workspace">
        @include('layouts.header')
        <main id="main-content" tabindex="-1">
            @if ($errors->any())
                <div class="alert alert-danger" role="alert"><strong>Veuillez vérifier les informations.</strong><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @if (session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
            @yield('main_content')
        </main>
        <footer class="vz-footer"><span>© {{ date('Y') }} Videzma</span><span>La vidange, en toute simplicité.</span></footer>
    </div>
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/icons/feather-icon/feather.min.js') }}"></script>
    <script src="{{ asset('assets/js/videzma-admin.js') }}"></script>
    @yield('scripts')
    @stack('scripts')
</body>
</html>
