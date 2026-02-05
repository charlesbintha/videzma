@extends('layouts.doctor')

@section('title', 'Rendez-vous #' . $appointment->id)

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Rendez-vous #{{ $appointment->id }}</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('doctor.appointments.index') }}">Rendez-vous</a></li>
                    <li class="breadcrumb-item active">#{{ $appointment->id }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
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

    <div class="row">
        <!-- Appointment details -->
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Details du rendez-vous</h5>
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
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Date et heure:</strong>
                            <p class="fs-5">{{ $appointment->scheduled_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Consultation liee:</strong>
                            <p><a href="{{ route('doctor.consultations.show', $appointment->consultationRequest) }}">#{{ $appointment->consultation_request_id }}</a></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        @if($appointment->started_at)
                            <div class="col-md-4">
                                <strong>Demarre a:</strong>
                                <p>{{ $appointment->started_at->format('H:i') }}</p>
                            </div>
                        @endif
                        @if($appointment->ended_at)
                            <div class="col-md-4">
                                <strong>Termine a:</strong>
                                <p>{{ $appointment->ended_at->format('H:i') }}</p>
                            </div>
                        @endif
                        @if($appointment->canceled_at)
                            <div class="col-md-4">
                                <strong>Annule le:</strong>
                                <p>{{ $appointment->canceled_at->format('d/m/Y H:i') }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Patient info -->
                    <h6 class="mt-4">Patient</h6>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Nom:</strong>
                            <p>{{ $appointment->consultationRequest?->patient?->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Telephone:</strong>
                            <p>{{ $appointment->consultationRequest?->patient?->phone ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Email:</strong>
                            <p>{{ $appointment->consultationRequest?->patient?->email ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Consultation info -->
                    <h6 class="mt-4">Motif de consultation</h6>
                    <hr>
                    <p>{{ $appointment->consultationRequest?->reason ?? '-' }}</p>

                    <!-- Location info -->
                    @if($appointment->consultationRequest?->location)
                        <h6 class="mt-4">Localisation</h6>
                        <hr>
                        <p>{{ $appointment->consultationRequest->location->address ?? 'Coordonnees: ' . $appointment->consultationRequest->location->latitude . ', ' . $appointment->consultationRequest->location->longitude }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions sidebar -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5>Actions</h5>
                </div>
                <div class="card-body">
                    @if($appointment->status === 'scheduled')
                        <form action="{{ route('doctor.appointments.start', $appointment) }}" method="POST" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fa fa-play"></i> Demarrer le RDV
                            </button>
                        </form>

                        <form action="{{ route('doctor.appointments.reschedule', $appointment) }}" method="POST" class="mb-3">
                            @csrf
                            @method('PATCH')
                            <div class="mb-2">
                                <label class="form-label">Nouvelle date/heure</label>
                                <input type="datetime-local" name="scheduled_at" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="fa fa-calendar"></i> Replanifier
                            </button>
                        </form>

                        <form action="{{ route('doctor.appointments.cancel', $appointment) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Confirmer l\'annulation ?')">
                                <i class="fa fa-times"></i> Annuler le RDV
                            </button>
                        </form>
                    @endif

                    @if($appointment->status === 'in_progress')
                        <form action="{{ route('doctor.appointments.complete', $appointment) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fa fa-check"></i> Terminer le RDV
                            </button>
                        </form>
                    @endif

                    @if(in_array($appointment->status, ['completed', 'canceled']))
                        <p class="text-muted text-center">Ce rendez-vous est termine.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
