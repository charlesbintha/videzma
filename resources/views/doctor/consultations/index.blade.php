@extends('layouts.doctor')

@section('title', 'Mes consultations')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Mes consultations</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Consultations</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>Liste des consultations</h5>
            <form method="GET" class="row g-3 mt-2">
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tous les statuts</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigne</option>
                        <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Accepte</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejete</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Cloture</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expire</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
                    <a href="{{ route('doctor.consultations.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
        <div class="card-body">
            @if(session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Motif</th>
                            <th>Statut</th>
                            <th>Demande le</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consultations as $consultation)
                            <tr>
                                <td>{{ $consultation->id }}</td>
                                <td>{{ $consultation->patient?->name ?? '-' }}</td>
                                <td>{{ Str::limit($consultation->reason, 40) }}</td>
                                <td>
                                    @switch($consultation->status)
                                        @case('pending')
                                            <span class="badge bg-warning">En attente</span>
                                            @break
                                        @case('assigned')
                                            <span class="badge bg-info">Assigne</span>
                                            @break
                                        @case('accepted')
                                            <span class="badge bg-success">Accepte</span>
                                            @break
                                        @case('rejected')
                                            <span class="badge bg-danger">Rejete</span>
                                            @break
                                        @case('closed')
                                            <span class="badge bg-secondary">Cloture</span>
                                            @break
                                        @case('expired')
                                            <span class="badge bg-dark">Expire</span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-dark">{{ $consultation->status }}</span>
                                    @endswitch
                                </td>
                                <td>{{ $consultation->requested_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('doctor.consultations.show', $consultation) }}" class="btn btn-sm btn-primary">Voir</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Aucune consultation.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $consultations->links() }}
        </div>
    </div>
</div>
@endsection
