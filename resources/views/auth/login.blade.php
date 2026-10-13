@extends('layouts.auth')
@section('title', 'Connexion')
@section('content')
<main class="vz-login">
    <section class="vz-login-story">
    <a href="{{ url('/') }}" class="vz-brand"><span class="vz-brand-icon">v.</span><span>videzma<span class="vz-brand-caption">ESPACE ADMINISTRATION</span></span></a>
    <div><h2>Toute votre activité.<br><em>En toute simplicité.</em></h2><p>Les bonnes informations, au bon endroit. Pilotez vos demandes, vos équipes et vos encaissements.</p></div>
    <div class="vz-login-visual" aria-hidden="true"><span>VOTRE ESPACE VIDEZMA</span><strong>Une journée bien organisée.</strong><div class="vz-login-steps"><span>Demande reçue</span><span>Équipe assignée</span><span>Service réalisé</span></div></div>
    <small class="vz-subtle">Videzma · Services de vidange à domicile</small>
    </section>
    <section class="vz-login-form">
            <p class="vz-eyebrow">VOTRE ACTIVITÉ COMMENCE ICI</p>
            <h1>Heureux de vous revoir.</h1>
            <p class="text-muted mb-4">Connectez-vous pour piloter vos services de vidange.</p>
            @if ($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
            <form method="post" action="{{ route('login.submit') }}">
                @csrf
                <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required></div>
                <div class="mb-3"><label class="form-label" for="password">Mot de passe</label><input class="form-control" id="password" type="password" name="password" autocomplete="current-password" required></div>
                <div class="mb-4 form-check"><input class="form-check-input" type="checkbox" id="remember" name="remember"><label class="form-check-label" for="remember">Se souvenir de moi</label></div>
                <button class="btn btn-primary w-100" type="submit">Se connecter</button>
            </form>
    <p class="vz-subtle">Clients et vidangeurs : retrouvez vos services dans l’application mobile.</p>
    </section>
</main>
@endsection
