@extends('layouts.master')

@section('title', 'Details rendez-vous')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Details rendez-vous</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.appointments.index') }}">Rendez-vous</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @php($statusLabels = ['scheduled' => 'Planifie', 'in_progress' => 'En cours', 'completed' => 'Termine', 'canceled' => 'Annule'])

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Rendez-vous</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">ID</dt>
                        <dd class="col-sm-8">#{{ $appointment->id }}</dd>
                        <dt class="col-sm-4">Consultation</dt>
                        <dd class="col-sm-8">
                            <a href="{{ route('admin.consultations.show', $appointment->consultationRequest) }}">#{{ $appointment->consultation_request_id }}</a>
                        </dd>
                        <dt class="col-sm-4">Patient</dt>
                        <dd class="col-sm-8">{{ $appointment->consultationRequest?->patient?->name ?? '-' }}</dd>
                        <dt class="col-sm-4">Medecin</dt>
                        <dd class="col-sm-8">{{ $appointment->consultationRequest?->doctor?->name ?? '-' }}</dd>
                        <dt class="col-sm-4">Planifie</dt>
                        <dd class="col-sm-8">{{ $appointment->scheduled_at->format('Y-m-d H:i') }}</dd>
                        <dt class="col-sm-4">Statut</dt>
                        <dd class="col-sm-8">{{ $statusLabels[$appointment->status] ?? $appointment->status }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Mise a jour</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('admin.appointments.update', $appointment) }}">
                        @csrf
                        @method('patch')
                        <div class="mb-3">
                            <label class="form-label" for="status">Statut</label>
                            <select class="form-select" id="status" name="status" required>
                                @foreach (['scheduled', 'in_progress', 'completed', 'canceled'] as $status)
                                    <option value="{{ $status }}" @selected(old('status', $appointment->status) === $status)>
                                        {{ ['scheduled' => 'Planifie', 'in_progress' => 'En cours', 'completed' => 'Termine', 'canceled' => 'Annule'][$status] ?? $status }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="scheduled_at">Planifie le</label>
                            <input class="form-control" id="scheduled_at" name="scheduled_at" type="datetime-local" value="{{ old('scheduled_at', $appointment->scheduled_at->format('Y-m-d\TH:i')) }}" required>
                            @error('scheduled_at')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                        <button class="btn btn-primary" type="submit">Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
