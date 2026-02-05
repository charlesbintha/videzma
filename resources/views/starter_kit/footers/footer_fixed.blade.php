@extends('layouts.master')

@section('title', 'Pied de page fixe')

@section('css')
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Pied de page fixe</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('layout_light') }}">
                            <svg class="stroke-icon">
                                <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                            </svg></a></li>
                    <li class="breadcrumb-item">Pieds de page</li>
                    <li class="breadcrumb-item active">Pied de page fixe</li>
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
                    <h5>Exemple de pied de page fixe</h5>
                </div>
                <div class="card-body">
                    <p>Ce pied de page reste visible lors du defilement. Utilisez-le si le contenu est court.</p>
                    <ul>
                        <li>Activez la position fixe seulement si la hauteur reste faible.</li>
                        <li>Verifiez le comportement sur mobile et sur ecrans petits.</li>
                        <li>Evitez de masquer le contenu principal.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@endsection
