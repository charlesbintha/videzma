@extends('layouts.doctor')

@section('title', 'Tableau de bord - Medecin')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Tableau de bord</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Tableau de bord</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <!-- Metrics -->
    <div class="row">
        <div class="col-xl-3 col-sm-6">
            <div class="card bg-warning">
                <div class="card-body">
                    <h6 class="mb-1 text-white">Demandes en attente</h6>
                    <h3 class="mb-0 text-white">{{ $metrics['consultations_pending'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card bg-success">
                <div class="card-body">
                    <h6 class="mb-1 text-white">Consultations en cours</h6>
                    <h3 class="mb-0 text-white">{{ $metrics['consultations_accepted'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card bg-primary">
                <div class="card-body">
                    <h6 class="mb-1 text-white">RDV aujourd'hui</h6>
                    <h3 class="mb-0 text-white">{{ $metrics['appointments_today'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card bg-info">
                <div class="card-body">
                    <h6 class="mb-1 text-white">RDV a venir</h6>
                    <h3 class="mb-0 text-white">{{ $metrics['appointments_upcoming'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Pending consultations -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Demandes en attente</h5>
                    <a href="{{ route('doctor.consultations.pending') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
                </div>
                <div class="card-body">
                    @if($pendingConsultations->isEmpty())
                        <p class="text-muted">Aucune demande en attente.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Motif</th>
                                        <th>Echeance SLA</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingConsultations as $consultation)
                                        <tr>
                                            <td>{{ $consultation->patient?->name ?? '-' }}</td>
                                            <td>{{ Str::limit($consultation->reason, 30) }}</td>
                                            <td>
                                                @if($consultation->sla_due_at)
                                                    <span class="{{ $consultation->sla_due_at->isPast() ? 'text-danger' : ($consultation->sla_due_at->diffInMinutes(now()) < 30 ? 'text-warning' : '') }}">
                                                        {{ $consultation->sla_due_at->format('d/m H:i') }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('doctor.consultations.show', $consultation) }}" class="btn btn-sm btn-primary">Voir</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Today's appointments -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Rendez-vous aujourd'hui</h5>
                    <a href="{{ route('doctor.appointments.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
                </div>
                <div class="card-body">
                    @if($todayAppointments->isEmpty())
                        <p class="text-muted">Aucun rendez-vous aujourd'hui.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Heure</th>
                                        <th>Patient</th>
                                        <th>Statut</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($todayAppointments as $appointment)
                                        <tr>
                                            <td>{{ $appointment->scheduled_at->format('H:i') }}</td>
                                            <td>{{ $appointment->consultationRequest?->patient?->name ?? '-' }}</td>
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
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
