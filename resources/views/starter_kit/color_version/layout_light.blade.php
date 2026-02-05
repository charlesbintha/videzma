@extends('layouts.master')

@section('title', 'Mise en page claire')

@section('css')
@endsection

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Mise en page claire</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('layout_light') }}">
                            <svg class="stroke-icon">
                                <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                            </svg></a></li>
                    <li class="breadcrumb-item">Version couleur</li>
                    <li class="breadcrumb-item active">Mise en page claire</li>
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
                    <h5>Page de demarrage</h5>
                </div>
                <div class="card-body">
                    <p>Cette page est un exemple du template Cuba. Adaptez la structure, la navigation et le branding pour Wadnou.</p>
                    <ul>
                        <li>Gardez les composants utiles et retirez le reste.</li>
                        <li>Utilisez les variables SCSS pour personnaliser les couleurs.</li>
                        <li>Remplacez les logos et les textes par le contenu Wadnou.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@endsection
