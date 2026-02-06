@extends('layouts.master')

@section('title', 'Interventions')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Interventions</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Interventions</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>Liste des interventions</h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3 mb-4">
                <div class="col-md-3">
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Tous les statuts</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
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
                            <th>#</th>
                            <th>Client</th>
                            <th>Vidangeur</th>
                            <th>Date prevue</th>
                            <th>Statut</th>
                            <th>Volume</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($interventions as $intervention)
                            <tr>
                                <td>{{ $intervention->id }}</td>
                                <td>{{ $intervention->serviceRequest?->client?->name ?? '-' }}</td>
                                <td>{{ $intervention->serviceRequest?->driver?->name ?? '-' }}</td>
                                <td>{{ $intervention->scheduled_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td>
                                    @switch($intervention->status)
                                        @case('scheduled')
                                            <span class="badge bg-info">Planifiee</span>
                                            @break
                                        @case('in_progress')
                                            <span class="badge bg-warning">En cours</span>
                                            @break
                                        @case('completed')
                                            <span class="badge bg-success">Terminee</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge bg-danger">Annulee</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $intervention->status }}</span>
                                    @endswitch
                                </td>
                                <td>{{ $intervention->actual_volume ? $intervention->actual_volume . ' m³' : '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.interventions.show', $intervention) }}" class="btn btn-sm btn-info">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Aucune intervention trouvee.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $interventions->links() }}
        </div>
    </div>
</div>
@endsection
