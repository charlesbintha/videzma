@extends('layouts.master')

@section('title', 'Vidangeur - ' . $driver->name)

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Details du vidangeur</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.drivers.index') }}">Vidangeurs</a></li>
                    <li class="breadcrumb-item active">{{ $driver->name }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5>Informations personnelles</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <span class="text-white fs-1">{{ strtoupper(substr($driver->name, 0, 1)) }}</span>
                            </div>
                        </div>
                        <h5 class="mb-1">{{ $driver->name }}</h5>
                        <p class="text-muted mb-0">{{ $driver->email }}</p>
                    </div>

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Telephone</span>
                            <strong>{{ $driver->phone ?? '-' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Statut compte</span>
                            @if($driver->status === 'active')
                                <span class="badge bg-success">Actif</span>
                            @elseif($driver->status === 'inactive')
                                <span class="badge bg-secondary">Inactif</span>
                            @else
                                <span class="badge bg-danger">Suspendu</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Inscription</span>
                            <strong>{{ $driver->created_at->format('d/m/Y') }}</strong>
                        </li>
                    </ul>

                    <div class="mt-4 d-flex gap-2">
                        <a href="{{ route('admin.drivers.edit', $driver) }}" class="btn btn-primary flex-fill">
                            <i class="fa fa-edit me-1"></i> Modifier
                        </a>
                        <form action="{{ route('admin.drivers.destroy', $driver) }}" method="POST" class="flex-fill" onsubmit="return confirm('Supprimer ce vidangeur ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fa fa-trash me-1"></i> Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            @if($driver->driverProfile)
            <div class="card">
                <div class="card-header">
                    <h5>Profil vidangeur</h5>
                </div>
                <div class="card-body">
                    @php($profile = $driver->driverProfile)
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Numero de permis:</strong> {{ $profile->license_number ?? '-' }}</p>
                            <p><strong>Type de vehicule:</strong> {{ ucfirst(str_replace('_', ' ', $profile->vehicle_type ?? '-')) }}</p>
                            <p><strong>Plaque:</strong> {{ $profile->vehicle_plate ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Capacite citerne:</strong> {{ $profile->tank_capacity ? $profile->tank_capacity . ' m³' : '-' }}</p>
                            <p><strong>Zones couvertes:</strong> {{ $profile->zone_coverage ?? '-' }}</p>
                            <p><strong>Statut verification:</strong>
                                @if($profile->verification_status === 'approved')
                                    <span class="badge bg-success">Approuve</span>
                                @elseif($profile->verification_status === 'rejected')
                                    <span class="badge bg-danger">Rejete</span>
                                @else
                                    <span class="badge bg-warning">En attente</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    @if($profile->bio)
                        <p><strong>Bio:</strong> {{ $profile->bio }}</p>
                    @endif
                </div>
            </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Documents</h5>
                </div>
                <div class="card-body">
                    @if($driver->driverDocuments && $driver->driverDocuments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($driver->driverDocuments as $document)
                                        <tr>
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
                                            <td>{{ $document->created_at->format('d/m/Y') }}</td>
                                            <td>
                                                <a href="{{ route('admin.driver-documents.show', $document) }}" class="btn btn-sm btn-info">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.driver-documents.download', $document) }}" class="btn btn-sm btn-secondary">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">Aucun document soumis.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
