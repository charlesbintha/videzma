@extends('layouts.master')

@section('title', 'Demande #' . $serviceRequest->id)

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Demande de service #{{ $serviceRequest->id }}</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.service-requests.index') }}">Demandes</a></li>
                    <li class="breadcrumb-item active">#{{ $serviceRequest->id }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Details de la demande</h5>
                    @switch($serviceRequest->status)
                        @case('pending')
                            <span class="badge bg-warning fs-6">En attente</span>
                            @break
                        @case('assigned')
                            <span class="badge bg-info fs-6">Assigne</span>
                            @break
                        @case('accepted')
                            <span class="badge bg-primary fs-6">Accepte</span>
                            @break
                        @case('in_progress')
                            <span class="badge bg-secondary fs-6">En cours</span>
                            @break
                        @case('completed')
                            <span class="badge bg-success fs-6">Termine</span>
                            @break
                        @default
                            <span class="badge bg-danger fs-6">{{ ucfirst($serviceRequest->status) }}</span>
                    @endswitch
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Client</h6>
                            <p class="mb-1"><strong>{{ $serviceRequest->client->name ?? '-' }}</strong></p>
                            <p class="mb-1">{{ $serviceRequest->client->email ?? '' }}</p>
                            <p class="mb-0">{{ $serviceRequest->client->phone ?? '' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Vidangeur assigne</h6>
                            @if($serviceRequest->driver)
                                <p class="mb-1"><strong>{{ $serviceRequest->driver->name }}</strong></p>
                                <p class="mb-1">{{ $serviceRequest->driver->email }}</p>
                                <p class="mb-0">{{ $serviceRequest->driver->phone ?? '' }}</p>
                            @else
                                <p class="text-muted">Non assigne</p>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Adresse</h6>
                            <p>{{ $serviceRequest->address }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Type de fosse</h6>
                            <p>{{ ucfirst(str_replace('_', ' ', $serviceRequest->fosse_type ?? '-')) }}</p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Volume estime</h6>
                            <p class="fs-5">{{ $serviceRequest->estimated_volume ?? '-' }} m³</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Volume reel</h6>
                            <p class="fs-5">{{ $serviceRequest->actual_volume ?? '-' }} m³</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Urgence</h6>
                            @if($serviceRequest->urgency_level === 'emergency')
                                <span class="badge bg-danger">Urgence</span>
                            @elseif($serviceRequest->urgency_level === 'urgent')
                                <span class="badge bg-warning">Urgent</span>
                            @else
                                <span class="badge bg-secondary">Normal</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Prix</h6>
                            <p class="fs-4 text-primary">{{ number_format($serviceRequest->price_amount ?? 0, 0, ',', ' ') }} FCFA</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Mode de paiement</h6>
                            <p>{{ ucfirst(str_replace('_', ' ', $serviceRequest->payment_method ?? '-')) }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Statut paiement</h6>
                            @if($serviceRequest->payment_status === 'paid')
                                <span class="badge bg-success">Paye</span>
                            @else
                                <span class="badge bg-warning">En attente</span>
                            @endif
                        </div>
                    </div>

                    @if($serviceRequest->client_notes)
                        <div class="mb-4">
                            <h6 class="text-muted mb-2">Notes du client</h6>
                            <p class="bg-light p-3 rounded">{{ $serviceRequest->client_notes }}</p>
                        </div>
                    @endif

                    @if($serviceRequest->rating)
                        <div class="mb-4">
                            <h6 class="text-muted mb-2">Evaluation</h6>
                            <p>
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa fa-star {{ $i <= $serviceRequest->rating ? 'text-warning' : 'text-muted' }}"></i>
                                @endfor
                                <span class="ms-2">({{ $serviceRequest->rating }}/5)</span>
                            </p>
                            @if($serviceRequest->rating_comment)
                                <p class="bg-light p-3 rounded">{{ $serviceRequest->rating_comment }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Historique</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Demande creee</span>
                            <span>{{ $serviceRequest->requested_at?->format('d/m/Y H:i') ?? $serviceRequest->created_at->format('d/m/Y H:i') }}</span>
                        </li>
                        @if($serviceRequest->assigned_at)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Vidangeur assigne</span>
                            <span>{{ $serviceRequest->assigned_at->format('d/m/Y H:i') }}</span>
                        </li>
                        @endif
                        @if($serviceRequest->accepted_at)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Demande acceptee</span>
                            <span>{{ $serviceRequest->accepted_at->format('d/m/Y H:i') }}</span>
                        </li>
                        @endif
                        @if($serviceRequest->rejected_at)
                        <li class="list-group-item d-flex justify-content-between text-danger">
                            <span>Demande rejetee</span>
                            <span>{{ $serviceRequest->rejected_at->format('d/m/Y H:i') }}</span>
                        </li>
                        @endif
                        @if($serviceRequest->started_at)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Intervention demarree</span>
                            <span>{{ $serviceRequest->started_at->format('d/m/Y H:i') }}</span>
                        </li>
                        @endif
                        @if($serviceRequest->completed_at)
                        <li class="list-group-item d-flex justify-content-between text-success">
                            <span>Intervention terminee</span>
                            <span>{{ $serviceRequest->completed_at->format('d/m/Y H:i') }}</span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            @if($serviceRequest->status === 'pending' || $serviceRequest->status === 'assigned')
            <div class="card">
                <div class="card-header">
                    <h5>Assigner un vidangeur</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.service-requests.assign', $serviceRequest) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="driver_id">Vidangeur</label>
                            <select class="form-select" id="driver_id" name="driver_id" required>
                                <option value="">Selectionner...</option>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}" @selected($serviceRequest->driver_id == $driver->id)>
                                        {{ $driver->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa fa-user-check me-1"></i> Assigner
                        </button>
                    </form>
                </div>
            </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5>Modifier le statut</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.service-requests.update', $serviceRequest) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label" for="status">Statut</label>
                            <select class="form-select" id="status" name="status">
                                <option value="pending" @selected($serviceRequest->status === 'pending')>En attente</option>
                                <option value="assigned" @selected($serviceRequest->status === 'assigned')>Assigne</option>
                                <option value="accepted" @selected($serviceRequest->status === 'accepted')>Accepte</option>
                                <option value="in_progress" @selected($serviceRequest->status === 'in_progress')>En cours</option>
                                <option value="completed" @selected($serviceRequest->status === 'completed')>Termine</option>
                                <option value="cancelled" @selected($serviceRequest->status === 'cancelled')>Annule</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="admin_notes">Notes admin</label>
                            <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3">{{ old('admin_notes', $serviceRequest->admin_notes) }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-secondary w-100">
                            <i class="fa fa-save me-1"></i> Mettre a jour
                        </button>
                    </form>
                </div>
            </div>

            @if($serviceRequest->intervention)
            <div class="card">
                <div class="card-header">
                    <h5>Intervention associee</h5>
                </div>
                <div class="card-body">
                    <p><strong>Statut:</strong> {{ ucfirst($serviceRequest->intervention->status) }}</p>
                    <p><strong>Prevue:</strong> {{ $serviceRequest->intervention->scheduled_at?->format('d/m/Y H:i') ?? '-' }}</p>
                    <a href="{{ route('admin.interventions.show', $serviceRequest->intervention) }}" class="btn btn-outline-primary w-100">
                        Voir l'intervention
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
