@extends('layouts.master')

@section('title', 'Document - ' . ucfirst($document->type))

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Details du document</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.driver-documents.index') }}">Documents</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Informations du document</h5>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Vidangeur</span>
                            <a href="{{ route('admin.drivers.show', $document->driver) }}">
                                <strong>{{ $document->driver->name ?? '-' }}</strong>
                            </a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Type</span>
                            <strong>{{ ucfirst(str_replace('_', ' ', $document->type)) }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Statut</span>
                            @if($document->status === 'approved')
                                <span class="badge bg-success">Approuve</span>
                            @elseif($document->status === 'rejected')
                                <span class="badge bg-danger">Rejete</span>
                            @else
                                <span class="badge bg-warning">En attente</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Date soumission</span>
                            <strong>{{ $document->created_at->format('d/m/Y H:i') }}</strong>
                        </li>
                        @if($document->reviewed_at)
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Date revision</span>
                            <strong>{{ $document->reviewed_at->format('d/m/Y H:i') }}</strong>
                        </li>
                        @endif
                        @if($document->notes)
                        <li class="list-group-item">
                            <span class="text-muted d-block mb-1">Notes</span>
                            <p class="mb-0">{{ $document->notes }}</p>
                        </li>
                        @endif
                    </ul>

                    <a href="{{ route('admin.driver-documents.download', $document) }}" class="btn btn-secondary mb-4">
                        <i class="fa fa-download me-1"></i> Telecharger le fichier
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Verification</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.driver-documents.update', $document) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label" for="status">Statut</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="pending" @selected($document->status === 'pending')>En attente</option>
                                <option value="approved" @selected($document->status === 'approved')>Approuve</option>
                                <option value="rejected" @selected($document->status === 'rejected')>Rejete</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="notes">Notes (optionnel)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Raison du rejet ou commentaires...">{{ old('notes', $document->notes) }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i> Enregistrer
                            </button>
                            <a href="{{ route('admin.driver-documents.index') }}" class="btn btn-secondary">
                                Retour
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Zone dangereuse</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Supprimer ce document de facon permanente.</p>
                    <form action="{{ route('admin.driver-documents.destroy', $document) }}" method="POST" onsubmit="return confirm('Etes-vous sur de vouloir supprimer ce document ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fa fa-trash me-1"></i> Supprimer le document
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
