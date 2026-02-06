@extends('layouts.master')

@section('title', 'Documents vidangeurs')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Documents vidangeurs</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Documents</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>Liste des documents</h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3 mb-4">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="pending" @selected(request('status') === 'pending')>En attente</option>
                        <option value="approved" @selected(request('status') === 'approved')>Approuve</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Rejete</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">Tous les types</option>
                        <option value="license" @selected(request('type') === 'license')>Permis</option>
                        <option value="vehicle_registration" @selected(request('type') === 'vehicle_registration')>Carte grise</option>
                        <option value="insurance" @selected(request('type') === 'insurance')>Assurance</option>
                        <option value="certificate" @selected(request('type') === 'certificate')>Certificat</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filtrer</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Vidangeur</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Date soumission</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($documents as $document)
                            <tr>
                                <td>{{ $document->driver->name ?? '-' }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $document->type)) }}</td>
                                <td>
                                    @if($document->status === 'approved')
                                        <span class="badge bg-success">Approuve</span>
                                    @elseif($document->status === 'rejected')
                                        <span class="badge bg-danger">Rejete</span>
                                    @else
                                        <span class="badge bg-warning">En attente</span>
                                    @endif
                                </td>
                                <td>{{ $document->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.driver-documents.show', $document) }}" class="btn btn-sm btn-info">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.driver-documents.download', $document) }}" class="btn btn-sm btn-secondary">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Aucun document trouve.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $documents->links() }}
        </div>
    </div>
</div>
@endsection
