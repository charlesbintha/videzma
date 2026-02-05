@extends('layouts.doctor')

@section('title', 'Mes rendez-vous')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Mes rendez-vous</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Rendez-vous</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5>Liste des rendez-vous</h5>
                <a href="{{ route('doctor.appointments.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Nouveau RDV
                </a>
            </div>
            <form method="GET" class="row g-3 mt-2">
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tous les statuts</option>
                        <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Planifie</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Termine</option>
                        <option value="canceled" {{ request('status') === 'canceled' ? 'selected' : '' }}>Annule</option>
                    </select>
                </div>
                <div class="col-auto">
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
                    <a href="{{ route('doctor.appointments.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
        <div class="card-body">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        <p class="mb-0">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date/Heure</th>
                            <th>Patient</th>
                            <th>Telephone</th>
                            <th>Motif</th>
                            <th>Statut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appointments as $appointment)
                            <tr>
                                <td>{{ $appointment->id }}</td>
                                <td>{{ $appointment->scheduled_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $appointment->consultationRequest?->patient?->name ?? '-' }}</td>
                                <td>{{ $appointment->consultationRequest?->patient?->phone ?? '-' }}</td>
                                <td>{{ Str::limit($appointment->consultationRequest?->reason, 30) }}</td>
                                <td>
                                    @switch($appointment->status)
                                        @case('scheduled')
                                            <span class="badge bg-primary">Planifie</span>
                                            @break
                                        @case('in_progress')
                                            <span class="badge bg-warning">En cours</span>
                                            @break
                                        @case('completed')
                                            <span class="badge bg-success">Termine</span>
                                            @break
                                        @case('canceled')
                                            <span class="badge bg-danger">Annule</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    <a href="{{ route('doctor.appointments.show', $appointment) }}" class="btn btn-sm btn-primary">Voir</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Aucun rendez-vous.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $appointments->links() }}
        </div>
    </div>
</div>
@endsection
