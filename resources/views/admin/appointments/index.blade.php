@extends('layouts.master')

@section('title', 'Rendez-vous')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Rendez-vous</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Rendez-vous</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>Rendez-vous</h5>
            <span>Suivre la planification, le statut, et la cloture.</span>
        </div>
        <div class="card-body">
            <form class="row g-3 mb-3" method="get">
                <div class="col-md-6">
                    <select class="form-select" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="scheduled" @selected(request('status') === 'scheduled')>Planifie</option>
                        <option value="in_progress" @selected(request('status') === 'in_progress')>En cours</option>
                        <option value="completed" @selected(request('status') === 'completed')>Termine</option>
                        <option value="canceled" @selected(request('status') === 'canceled')>Annule</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">OK</button>
                </div>
            </form>
            @php($statusLabels = ['scheduled' => 'Planifie', 'in_progress' => 'En cours', 'completed' => 'Termine', 'canceled' => 'Annule'])
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Consultation</th>
                            <th>Patient</th>
                            <th>Medecin</th>
                            <th>Planifie</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($appointments as $appointment)
                            <tr>
                                <td>#{{ $appointment->id }}</td>
                                <td>#{{ $appointment->consultation_request_id }}</td>
                                <td>{{ optional(optional($appointment->consultationRequest)->patient)->name ?? '-' }}</td>
                                <td>{{ optional(optional($appointment->consultationRequest)->doctor)->name ?? '-' }}</td>
                                <td>{{ $appointment->scheduled_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $statusLabels[$appointment->status] ?? $appointment->status }}</td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.appointments.show', $appointment) }}">Voir</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">Aucun rendez-vous trouve.</td>
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
