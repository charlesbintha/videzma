@extends('layouts.master')

@section('title', 'Details consultation')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Details consultation</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.consultations.index') }}">Consultations</a></li>
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

    @php($statusLabels = ['pending' => 'En attente', 'assigned' => 'Assignee', 'accepted' => 'Acceptee', 'rejected' => 'Refusee', 'canceled' => 'Annulee', 'closed' => 'Cloturee', 'expired' => 'Expiree'])
    @php($appointmentStatusLabels = ['scheduled' => 'Planifie', 'in_progress' => 'En cours', 'completed' => 'Termine', 'canceled' => 'Annule'])

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Demande</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">ID</dt>
                        <dd class="col-sm-8">#{{ $consultation->id }}</dd>
                        <dt class="col-sm-4">Patient</dt>
                        <dd class="col-sm-8">{{ $consultation->patient?->name ?? '-' }}</dd>
                        <dt class="col-sm-4">Medecin</dt>
                        <dd class="col-sm-8">{{ $consultation->doctor?->name ?? '-' }}</dd>
                        <dt class="col-sm-4">Statut</dt>
                        <dd class="col-sm-8">{{ $statusLabels[$consultation->status] ?? $consultation->status }}</dd>
                        <dt class="col-sm-4">Motif</dt>
                        <dd class="col-sm-8">{{ $consultation->reason ?? '-' }}</dd>
                        <dt class="col-sm-4">Demande le</dt>
                        <dd class="col-sm-8">{{ $consultation->requested_at->format('Y-m-d H:i') }}</dd>
                        <dt class="col-sm-4">Echeance SLA</dt>
                        <dd class="col-sm-8">{{ $consultation->sla_due_at ? $consultation->sla_due_at->format('Y-m-d H:i') : '-' }}</dd>
                        <dt class="col-sm-4">Localisation</dt>
                        <dd class="col-sm-8">
                            @if ($consultation->location)
                                {{ $consultation->location->latitude }}, {{ $consultation->location->longitude }}
                            @else
                                -
                            @endif
                        </dd>
                    </dl>
                    <div class="mt-3">
                        <strong>Notes</strong>
                        <p class="mb-0">{{ $consultation->notes ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Mise a jour</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('admin.consultations.update', $consultation) }}">
                        @csrf
                        @method('patch')
                        <div class="mb-3">
                            <label class="form-label" for="doctor_id">Assigner un medecin</label>
                            <select class="form-select" id="doctor_id" name="doctor_id">
                                <option value="">Non assigne</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" @selected(old('doctor_id', $consultation->doctor_id) == $doctor->id)>{{ $doctor->name }}</option>
                                @endforeach
                            </select>
                            @error('doctor_id')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="status">Statut</label>
                            <select class="form-select" id="status" name="status" required>
                                @foreach (['pending', 'assigned', 'accepted', 'rejected', 'canceled', 'closed', 'expired'] as $status)
                                    <option value="{{ $status }}" @selected(old('status', $consultation->status) === $status)>
                                        {{ $statusLabels[$status] ?? $status }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="sla_due_at">Echeance SLA</label>
                            <input class="form-control" id="sla_due_at" name="sla_due_at" type="datetime-local" value="{{ old('sla_due_at', $consultation->sla_due_at ? $consultation->sla_due_at->format('Y-m-d\TH:i') : '') }}">
                            @error('sla_due_at')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="notes">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $consultation->notes) }}</textarea>
                            @error('notes')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                        <button class="btn btn-primary" type="submit">Enregistrer</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Rendez-vous</h5>
                </div>
                <div class="card-body">
                    @if ($consultation->appointment)
                        <p>ID rendez-vous : #{{ $consultation->appointment->id }}</p>
                        <p>Planifie : {{ $consultation->appointment->scheduled_at->format('Y-m-d H:i') }}</p>
                        <p>Statut : {{ $appointmentStatusLabels[$consultation->appointment->status] ?? $consultation->appointment->status }}</p>
                        <a class="btn btn-outline-primary" href="{{ route('admin.appointments.show', $consultation->appointment) }}">Voir rendez-vous</a>
                    @else
                        <form method="post" action="{{ route('admin.consultations.appointments.store', $consultation) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="scheduled_at">Planifier a</label>
                                <input class="form-control" id="scheduled_at" name="scheduled_at" type="datetime-local" value="{{ old('scheduled_at') }}" required>
                                @error('scheduled_at')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                            <button class="btn btn-outline-primary" type="submit">Creer rendez-vous</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
