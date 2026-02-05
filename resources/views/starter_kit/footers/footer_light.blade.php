@extends('layouts.master')

@section('title', 'Pied de page clair')

@section('css')
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Pied de page clair</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('layout_light') }}">
                            <svg class="stroke-icon">
                                <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                            </svg></a></li>
                    <li class="breadcrumb-item">Pieds de page</li>
                    <li class="breadcrumb-item active">Pied de page clair</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>Exemple de pied de page</h5>
                </div>
                <div class="card-body">
                    <p>Exemple de pied de page clair pour une interface lumineuse. Ajustez les liens et le branding.</p>
                    <ul>
                        <li>Utilisez un fond clair et des icones simples.</li>
                        <li>Gardez un espacement suffisant pour la lisibilite.</li>
                        <li>Ajoutez les liens essentiels et les mentions legales.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@endsection
