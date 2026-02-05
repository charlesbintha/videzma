@extends('layouts.master')

@section('title', 'Mise en page en boite')

@section('css')
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Mise en page en boite</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('layout_light') }}">
                            <svg class="stroke-icon">
                                <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                            </svg></a></li>
                    <li class="breadcrumb-item">Mise en page</li>
                    <li class="breadcrumb-item active">Boite</li>
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
                    <h5>Exemple de mise en page en boite</h5>
                </div>
                <div class="card-body">
                    <p>Le contenu est centre avec une largeur maximale. Cette option convient aux pages admin denses.</p>
                    <ul>
                        <li>Definir une largeur max pour le contenu principal.</li>
                        <li>Utiliser des marges externes pour aerer l'interface.</li>
                        <li>Verifier l'affichage sur mobile.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@endsection
