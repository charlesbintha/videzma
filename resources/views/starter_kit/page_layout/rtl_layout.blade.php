@extends('layouts.master')

@section('title', 'Mise en page RTL')

@section('css')
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Mise en page RTL</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('layout_light') }}">
                            <svg class="stroke-icon">
                                <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                            </svg></a></li>
                    <li class="breadcrumb-item">Mise en page</li>
                    <li class="breadcrumb-item active">RTL</li>
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
                    <h5>Mode RTL</h5>
                </div>
                <div class="card-body">
                    <p>Cette page montre une mise en page de droite a gauche pour les langues RTL.</p>
                    <ul>
                        <li>Activez les styles RTL uniquement si la langue le demande.</li>
                        <li>Verifiez les icones et les alignements.</li>
                        <li>Gardez les libelles courts pour eviter les debordements.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@endsection
