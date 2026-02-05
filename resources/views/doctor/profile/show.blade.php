@extends('layouts.doctor')

@section('title', 'Mon profil')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Mon profil</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Profil</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Informations personnelles</h5>
                    <a href="{{ route('doctor.profile.edit') }}" class="btn btn-outline-primary">
                        <i class="fa fa-edit"></i> Modifier
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <strong>Nom</strong>
                            <p class="fs-5">{{ $doctor->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Email</strong>
                            <p class="fs-5">{{ $doctor->email }}</p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <strong>Telephone</strong>
                            <p class="fs-5">{{ $doctor->phone ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Statut du compte</strong>
                            <p>
                                @if($doctor->status === 'active')
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-danger">{{ $doctor->status }}</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <hr>

                    <h6 class="mt-4">Informations professionnelles</h6>

                    <div class="row mb-4 mt-3">
                        <div class="col-md-6">
                            <strong>Specialite</strong>
                            <p class="fs-5">{{ $doctor->doctorProfile?->specialty ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Numero de licence</strong>
                            <p class="fs-5">{{ $doctor->doctorProfile?->license_number ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <strong>Statut de verification</strong>
                            <p>
                                @switch($doctor->doctorProfile?->verification_status)
                                    @case('verified')
                                        <span class="badge bg-success">Verifie</span>
                                        @break
                                    @case('pending')
                                        <span class="badge bg-warning">En attente</span>
                                        @break
                                    @case('rejected')
                                        <span class="badge bg-danger">Rejete</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">Non soumis</span>
                                @endswitch
                            </p>
                        </div>
                        @if($doctor->doctorProfile?->verified_at)
                            <div class="col-md-6">
                                <strong>Verifie le</strong>
                                <p>{{ $doctor->doctorProfile->verified_at->format('d/m/Y') }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="mb-4">
                        <strong>Biographie</strong>
                        <p>{{ $doctor->doctorProfile?->bio ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
