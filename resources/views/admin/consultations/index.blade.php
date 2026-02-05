@extends('layouts.master')

@section('title', 'Consultations')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Consultations</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Consultations</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>Demandes de consultation</h5>
            <span>Suivre l'assignation, le statut, et les delais SLA.</span>
        </div>
        <div class="card-body">
            <form class="row g-3 mb-3" method="get">
                <div class="col-md-6">
                    <select class="form-select" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="pending" @selected(request('status') === 'pending')>En attente</option>
                        <option value="assigned" @selected(request('status') === 'assigned')>Assignee</option>
                        <option value="accepted" @selected(request('status') === 'accepted')>Acceptee</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Refusee</option>
                        <option value="canceled" @selected(request('status') === 'canceled')>Annulee</option>
                        <option value="closed" @selected(request('status') === 'closed')>Cloturee</option>
                        <option value="expired" @selected(request('status') === 'expired')>Expiree</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">OK</button>
                </div>
            </form>
            @php($statusLabels = ['pending' => 'En attente', 'assigned' => 'Assignee', 'accepted' => 'Acceptee', 'rejected' => 'Refusee', 'canceled' => 'Annulee', 'closed' => 'Cloturee', 'expired' => 'Expiree'])
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient</th>
                            <th>Medecin</th>
                            <th>Statut</th>
                            <th>Demande le</th>
                            <th>Echeance SLA</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($consultations as $consultation)
                            <tr>
                                <td>#{{ $consultation->id }}</td>
                                <td>{{ optional($consultation->patient)->name ?? '-' }}</td>
                                <td>{{ optional($consultation->doctor)->name ?? '-' }}</td>
                                <td>{{ $statusLabels[$consultation->status] ?? $consultation->status }}</td>
                                <td>{{ $consultation->requested_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $consultation->sla_due_at ? $consultation->sla_due_at->format('Y-m-d H:i') : '-' }}</td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.consultations.show', $consultation) }}">Voir</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">Aucune demande trouvee.</td>
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
