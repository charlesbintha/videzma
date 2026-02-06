@extends('layouts.master')

@section('title', 'Vidangeurs')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Vidangeurs</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Vidangeurs</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Liste des vidangeurs</h5>
            <a href="{{ route('admin.drivers.create') }}" class="btn btn-primary">
                <i class="fa fa-plus me-1"></i> Ajouter
            </a>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="pending" @selected(request('status') === 'pending')>En attente</option>
                        <option value="approved" @selected(request('status') === 'approved')>Approuve</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Rejete</option>
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
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Telephone</th>
                            <th>Statut verification</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($drivers as $driver)
                            <tr>
                                <td>{{ $driver->name }}</td>
                                <td>{{ $driver->email }}</td>
                                <td>{{ $driver->phone ?? '-' }}</td>
                                <td>
                                    @php($status = $driver->driverProfile?->verification_status ?? 'pending')
                                    @if($status === 'approved')
                                        <span class="badge bg-success">Approuve</span>
                                    @elseif($status === 'rejected')
                                        <span class="badge bg-danger">Rejete</span>
                                    @else
                                        <span class="badge bg-warning">En attente</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.drivers.show', $driver) }}" class="btn btn-sm btn-info">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.drivers.edit', $driver) }}" class="btn btn-sm btn-primary">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Aucun vidangeur trouve.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $drivers->links() }}
        </div>
    </div>
</div>
@endsection
