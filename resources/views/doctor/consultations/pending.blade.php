@extends('layouts.doctor')

@section('title', 'Demandes en attente')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Demandes en attente</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Demandes en attente</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>Demandes necessitant votre attention</h5>
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
                            <th>Patient</th>
                            <th>Telephone</th>
                            <th>Motif</th>
                            <th>Adresse</th>
                            <th>Echeance SLA</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($consultations as $consultation)
                            <tr class="{{ $consultation->sla_due_at && $consultation->sla_due_at->isPast() ? 'table-danger' : '' }}">
                                <td>{{ $consultation->id }}</td>
                                <td>{{ $consultation->patient?->name ?? '-' }}</td>
                                <td>{{ $consultation->patient?->phone ?? '-' }}</td>
                                <td>{{ Str::limit($consultation->reason, 30) }}</td>
                                <td>{{ $consultation->location?->address ? Str::limit($consultation->location->address, 30) : '-' }}</td>
                                <td>
                                    @if($consultation->sla_due_at)
                                        <span class="{{ $consultation->sla_due_at->isPast() ? 'text-danger fw-bold' : ($consultation->sla_due_at->diffInMinutes(now()) < 30 ? 'text-warning fw-bold' : '') }}">
                                            {{ $consultation->sla_due_at->format('d/m H:i') }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('doctor.consultations.show', $consultation) }}" class="btn btn-sm btn-primary">Traiter</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Aucune demande en attente.</td>
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
