@extends('layouts.master')

@section('title', 'Verifier document')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Document medecin</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.doctor-documents.index') }}">Documents medecins</a></li>
                    <li class="breadcrumb-item active">Verifier</li>
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
            <div class="card">
                <div class="card-header">
                    <h5>Details</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Medecin</dt>
                        <dd class="col-sm-8">
                            @if ($document->doctor)
                                <a href="{{ route('admin.doctors.show', $document->doctor) }}">{{ $document->doctor->name }}</a>
                            @else
                                -
                            @endif
                        </dd>
                        <dt class="col-sm-4">Type</dt>
                        <dd class="col-sm-8">{{ $document->type }}</dd>
                        <dt class="col-sm-4">Statut</dt>
                        <dd class="col-sm-8">{{ $document->status }}</dd>
                        <dt class="col-sm-4">Fichier</dt>
                        <dd class="col-sm-8">
                            @if ($document->file_path)
                                <a href="{{ route('admin.doctor-documents.download', $document) }}">Telecharger</a>
                            @else
                                -
                            @endif
                        </dd>
                        <dt class="col-sm-4">Verifie par</dt>
                        <dd class="col-sm-8">{{ $document->reviewer?->name ?? '-' }}</dd>
                        <dt class="col-sm-4">Verifie le</dt>
                        <dd class="col-sm-8">{{ $document->reviewed_at ? $document->reviewed_at->format('Y-m-d H:i') : '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Verification</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('admin.doctor-documents.update', $document) }}">
                        @csrf
                        @method('patch')
                        <div class="mb-3">
                            <label class="form-label" for="status">Statut</label>
                            <select class="form-select" id="status" name="status">
                                @foreach (['pending', 'approved', 'rejected'] as $status)
                                    <option value="{{ $status }}" @selected(old('status', $document->status) === $status)>
                                        {{ ['pending' => 'En attente', 'approved' => 'Approuve', 'rejected' => 'Refuse'][$status] ?? $status }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="notes">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4">{{ old('notes', $document->notes) }}</textarea>
                            @error('notes')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" type="submit">Enregistrer</button>
                            <a class="btn btn-outline-secondary" href="{{ route('admin.doctor-documents.index') }}">Retour</a>
                        </div>
                    </form>
                    <form class="mt-3" method="post" action="{{ route('admin.doctor-documents.destroy', $document) }}">
                        @csrf
                        @method('delete')
                        <button class="btn btn-outline-danger" type="submit">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
