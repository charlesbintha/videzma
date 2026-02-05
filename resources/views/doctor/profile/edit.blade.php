@extends('layouts.doctor')

@section('title', 'Modifier mon profil')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Modifier mon profil</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('doctor.profile.show') }}">Profil</a></li>
                    <li class="breadcrumb-item active">Modifier</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5>Modifier mes informations</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <p class="mb-0">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('doctor.profile.update') }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <h6>Informations personnelles</h6>
                        <hr>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nom</label>
                                <input type="text" name="name" class="form-control" required value="{{ old('name', $doctor->name) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="{{ $doctor->email }}" disabled>
                                <small class="text-muted">L'email ne peut pas etre modifie.</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telephone</label>
                                <input type="text" name="phone" class="form-control" required value="{{ old('phone', $doctor->phone) }}">
                            </div>
                        </div>

                        <h6 class="mt-4">Informations professionnelles</h6>
                        <hr>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Specialite</label>
                                <input type="text" name="specialty" class="form-control" value="{{ old('specialty', $doctor->doctorProfile?->specialty) }}" placeholder="Ex: Medecine generale, Cardiologie...">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Biographie</label>
                            <textarea name="bio" class="form-control" rows="4" placeholder="Presentez-vous en quelques lignes...">{{ old('bio', $doctor->doctorProfile?->bio) }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                            <a href="{{ route('doctor.profile.show') }}" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
