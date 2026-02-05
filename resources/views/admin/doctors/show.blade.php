@extends('layouts.master')

@section('title', 'Details du medecin')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Details du medecin</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.doctors.index') }}">Medecins</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row">
        <div class="col-xl-6">
            @php($statusLabels = ['pending' => 'En attente', 'active' => 'Actif', 'suspended' => 'Suspendu'])
            @php($verificationLabels = ['pending' => 'En attente', 'approved' => 'Approuve', 'rejected' => 'Refuse'])
            <div class="card">
                <div class="card-header">
                    <h5>Profil</h5>
                    <div class="card-header-right">
                        <a class="btn btn-sm btn-primary" href="{{ route('admin.doctors.edit', $doctor) }}">Modifier</a>
                        <form class="d-inline" method="post" action="{{ route('admin.doctors.destroy', $doctor) }}">
                            @csrf
                            @method('delete')
                            <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Nom</dt>
                        <dd class="col-sm-8">{{ $doctor->name }}</dd>
                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">{{ $doctor->email }}</dd>
                        <dt class="col-sm-4">Telephone</dt>
                        <dd class="col-sm-8">{{ $doctor->phone ?? '-' }}</dd>
                        <dt class="col-sm-4">Statut</dt>
                        <dd class="col-sm-8">{{ $statusLabels[$doctor->status] ?? $doctor->status }}</dd>
                        <dt class="col-sm-4">Langue</dt>
                        <dd class="col-sm-8">{{ $doctor->locale }}</dd>
                        <dt class="col-sm-4">Specialite</dt>
                        <dd class="col-sm-8">{{ $doctor->doctorProfile?->specialty ?? '-' }}</dd>
                        <dt class="col-sm-4">Licence</dt>
                        <dd class="col-sm-8">{{ $doctor->doctorProfile?->license_number ?? '-' }}</dd>
                        <dt class="col-sm-4">Verification</dt>
                        <dd class="col-sm-8">{{ $verificationLabels[$doctor->doctorProfile?->verification_status] ?? ($doctor->doctorProfile?->verification_status ?? '-') }}</dd>
                        <dt class="col-sm-4">Verifie le</dt>
                        <dd class="col-sm-8">{{ $doctor->doctorProfile?->verified_at ? $doctor->doctorProfile->verified_at->format('Y-m-d') : '-' }}</dd>
                    </dl>
                    <div class="mt-3">
                        <strong>Bio</strong>
                        <p class="mb-0">{{ $doctor->doctorProfile?->bio ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Documents</h5>
                </div>
                <div class="card-body">
                    <form class="row g-3 mb-4" method="post" action="{{ route('admin.doctors.documents.store', $doctor) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label" for="type">Type</label>
                            <input class="form-control" id="type" name="type" value="{{ old('type') }}" placeholder="licence, diplome, certificat" required>
                            @error('type')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" for="document">Fichier</label>
                            <input class="form-control" id="document" name="document" type="file" required>
                            @error('document')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="notes">Notes</label>
                            <input class="form-control" id="notes" name="notes" value="{{ old('notes') }}">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-outline-primary" type="submit">Televerser</button>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Statut</th>
                                    <th>Fichier</th>
                                    <th>Verifie par</th>
                                    <th>Verifie le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($doctor->doctorDocuments as $document)
                                    <tr>
                                        <td>{{ $document->type }}</td>
                                        <td>{{ $verificationLabels[$document->status] ?? $document->status }}</td>
                                        <td>
                                            @if ($document->file_path)
                                                <a href="{{ route('admin.doctor-documents.download', $document) }}">Telecharger</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $document->reviewer?->name ?? '-' }}</td>
                                        <td>{{ $document->reviewed_at ? $document->reviewed_at->format('Y-m-d') : '-' }}</td>
                                        <td>
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.doctor-documents.show', $document) }}">Verifier</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">Aucun document televerse.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
