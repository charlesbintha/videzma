@extends('layouts.auth')

@section('title', 'Connexion')

@section('css')
    <style>
        .auth-page {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 20px;
            overflow: hidden;
            background: #0b1f2a;
        }

        .auth-page::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: url("{{ asset('assets/images/auth/back.jpg') }}");
            background-size: cover;
            background-position: center;
            filter: blur(12px);
            transform: scale(1.08);
            z-index: 0;
        }

        .auth-page::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(10, 28, 40, 0.85), rgba(10, 28, 40, 0.6) 50%, rgba(10, 28, 40, 0.85));
            z-index: 1;
        }

        .auth-shell {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 420px;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.75);
            border-radius: 18px;
            box-shadow: 0 24px 70px rgba(8, 20, 30, 0.35);
            backdrop-filter: blur(6px);
        }

        .auth-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            justify-content: center;
            margin-bottom: 18px;
        }

        .auth-brand img {
            height: 44px;
        }

        .auth-title {
            font-weight: 700;
            letter-spacing: 0.3px;
            margin-bottom: 6px;
        }

        .auth-subtitle {
            color: #5b6b77;
            margin-bottom: 22px;
        }

        .auth-card .form-control {
            border-radius: 10px;
            border-color: #d7dde3;
        }

        .auth-card .btn-primary {
            border-radius: 10px;
            padding: 10px 16px;
        }

        .auth-card .alert {
            border-radius: 12px;
        }
    </style>
@endsection

@section('content')
<div class="auth-page">
    <div class="auth-shell">
        <div class="auth-card card">
            <div class="card-body">
                <div class="auth-brand">
                    <img src="{{ asset('assets/images/logo/wadnou2.png') }}" alt="Wadnou">
                </div>
                <div class="text-center">
                    <h4 class="auth-title">Connexion admin</h4>
                    <p class="auth-subtitle">Connectez-vous pour gerer WADNOU.</p>
                </div>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif
                <form method="post" action="{{ route('login.submit') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Mot de passe</label>
                        <input class="form-control" id="password" type="password" name="password" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Se souvenir de moi</label>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Se connecter</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
