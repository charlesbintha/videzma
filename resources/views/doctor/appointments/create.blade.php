@extends('layouts.doctor')

@section('title', 'Nouveau rendez-vous')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Nouveau rendez-vous</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('doctor.appointments.index') }}">Rendez-vous</a></li>
                    <li class="breadcrumb-item active">Nouveau</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5>Planifier un rendez-vous</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <p class="mb-0">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    @if($consultations->isEmpty())
                        <div class="alert alert-warning">
                            <p class="mb-0">Aucune consultation acceptee sans RDV. Vous devez d'abord accepter une demande de consultation.</p>
                        </div>
                        <a href="{{ route('doctor.consultations.pending') }}" class="btn btn-primary">Voir les demandes en attente</a>
                    @else
                        <form action="{{ route('doctor.appointments.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Consultation</label>
                                <select name="consultation_request_id" class="form-select" required>
                                    <option value="">-- Selectionnez une consultation --</option>
                                    @foreach($consultations as $consultation)
                                        <option value="{{ $consultation->id }}" {{ request('consultation_id') == $consultation->id ? 'selected' : '' }}>
                                            #{{ $consultation->id }} - {{ $consultation->patient?->name ?? 'Patient inconnu' }} - {{ Str::limit($consultation->reason, 40) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date et heure</label>
                                <input type="datetime-local" name="scheduled_at" class="form-control" required min="{{ now()->addMinutes(15)->format('Y-m-d\TH:i') }}">
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Planifier</button>
                                <a href="{{ route('doctor.appointments.index') }}" class="btn btn-outline-secondary">Annuler</a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
