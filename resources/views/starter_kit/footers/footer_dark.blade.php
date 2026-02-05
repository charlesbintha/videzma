@extends('layouts.master')

@section('title', 'Pied de page sombre')

@section('css')
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Pied de page sombre</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('layout_light') }}">
                            <svg class="stroke-icon">
                                <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                            </svg></a></li>
                    <li class="breadcrumb-item">Pieds de page</li>
                    <li class="breadcrumb-item active">Pied de page sombre</li>
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
                    <p>Ce gabarit sert a presenter un pied de page sombre. Adaptez le contenu aux pages admin Wadnou.</p>
                    <ul>
                        <li>Assurez un contraste lisible avec le fond sombre.</li>
                        <li>Regroupez les liens utiles (support, contact, aide).</li>
                        <li>Ajoutez les mentions legales et la version.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@endsection
