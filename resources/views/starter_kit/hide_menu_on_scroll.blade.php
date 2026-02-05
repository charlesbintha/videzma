@extends('layouts.master')

@section('title', 'Menu masquable au defilement')

@section('css')
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Menu masquable au defilement</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('layout_light') }}">
                            <svg class="stroke-icon">
                                <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                            </svg></a></li>
                    <li class="breadcrumb-item">Accueil</li>
                    <li class="breadcrumb-item active">Menu masquable</li>
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
                    <h5>Menu au defilement</h5>
                </div>
                <div class="card-body">
                    <p>Cette page montre un menu qui se masque a la descente et reapparait a la remontee.</p>
                    <ul>
                        <li>Activez le script <code>assets/js/hide-on-scroll.js</code>.</li>
                        <li>Testez le comportement sur mobile et sur tablette.</li>
                        <li>Gardez une hauteur de menu stable pour eviter les sauts.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/hide-on-scroll.js') }}"></script>
@endsection
