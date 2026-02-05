@extends('layouts.master')

@section('title', 'Modifier medecin')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Modifier medecin</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.doctors.index') }}">Medecins</a></li>
                    <li class="breadcrumb-item active">Modifier</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>Mettre a jour le medecin</h5>
        </div>
        <div class="card-body">
            <form method="post" action="{{ route('admin.doctors.update', $doctor) }}">
                @csrf
                @method('put')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="name">Nom</label>
                        <input class="form-control" id="name" name="name" value="{{ old('name', $doctor->name) }}" required>
                        @error('name')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control" id="email" name="email" type="email" value="{{ old('email', $doctor->email) }}" required>
                        @error('email')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="phone">Telephone</label>
                        <input class="form-control" id="phone" name="phone" value="{{ old('phone', $doctor->phone) }}">
                        @error('phone')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="status">Statut</label>
                        <select class="form-select" id="status" name="status" required>
                            @foreach (['pending', 'active', 'suspended'] as $status)
                                <option value="{{ $status }}" @selected(old('status', $doctor->status) === $status)>
                                    {{ ['pending' => 'En attente', 'active' => 'Actif', 'suspended' => 'Suspendu'][$status] ?? $status }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label" for="locale">Langue</label>
                        <input class="form-control" id="locale" name="locale" value="{{ old('locale', $doctor->locale) }}" required>
                        @error('locale')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="specialty">Specialite</label>
                        <input class="form-control" id="specialty" name="specialty" value="{{ old('specialty', $doctor->doctorProfile?->specialty) }}">
                        @error('specialty')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="license_number">Numero de licence</label>
                        <input class="form-control" id="license_number" name="license_number" value="{{ old('license_number', $doctor->doctorProfile?->license_number) }}">
                        @error('license_number')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="verification_status">Statut de verification</label>
                        <select class="form-select" id="verification_status" name="verification_status" required>
                            @foreach (['pending', 'approved', 'rejected'] as $status)
                                <option value="{{ $status }}" @selected(old('verification_status', $doctor->doctorProfile?->verification_status) === $status)>
                                    {{ ['pending' => 'En attente', 'approved' => 'Approuve', 'rejected' => 'Refuse'][$status] ?? $status }}
                                </option>
                            @endforeach
                        </select>
                        @error('verification_status')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label" for="bio">Bio</label>
                        <textarea class="form-control" id="bio" name="bio" rows="3">{{ old('bio', $doctor->doctorProfile?->bio) }}</textarea>
                        @error('bio')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Enregistrer</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.doctors.show', $doctor) }}">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
