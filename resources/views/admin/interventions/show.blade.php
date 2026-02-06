@extends('layouts.master')

@section('title', 'Intervention #' . $intervention->id)

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Intervention #{{ $intervention->id }}</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.interventions.index') }}">Interventions</a></li>
                    <li class="breadcrumb-item active">#{{ $intervention->id }}</li>
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
                    <h5>Details de l'intervention</h5>
                    @switch($intervention->status)
                        @case('scheduled')
                            <span class="badge bg-info fs-6">Planifiee</span>
                            @break
                        @case('in_progress')
                            <span class="badge bg-warning fs-6">En cours</span>
                            @break
                        @case('completed')
                            <span class="badge bg-success fs-6">Terminee</span>
                            @break
                        @default
                            <span class="badge bg-danger fs-6">{{ ucfirst($intervention->status) }}</span>
                    @endswitch
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Client</h6>
                            @if($intervention->serviceRequest?->client)
                                <p class="mb-1"><strong>{{ $intervention->serviceRequest->client->name }}</strong></p>
                                <p class="mb-1">{{ $intervention->serviceRequest->client->email }}</p>
                                <p class="mb-0">{{ $intervention->serviceRequest->client->phone ?? '' }}</p>
                            @else
                                <p class="text-muted">-</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Vidangeur</h6>
                            @if($intervention->serviceRequest?->driver)
                                <p class="mb-1"><strong>{{ $intervention->serviceRequest->driver->name }}</strong></p>
                                <p class="mb-1">{{ $intervention->serviceRequest->driver->email }}</p>
                                <p class="mb-0">{{ $intervention->serviceRequest->driver->phone ?? '' }}</p>
                            @else
                                <p class="text-muted">-</p>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Adresse</h6>
                            <p>{{ $intervention->serviceRequest?->address ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Type de fosse</h6>
                            <p>{{ ucfirst(str_replace('_', ' ', $intervention->serviceRequest?->fosse_type ?? '-')) }}</p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Date prevue</h6>
                            <p class="fs-5">{{ $intervention->scheduled_at?->format('d/m/Y H:i') ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Volume vidange</h6>
                            <p class="fs-5">{{ $intervention->actual_volume ?? '-' }} m³</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Duree</h6>
                            <p class="fs-5">{{ $intervention->duration_minutes ? $intervention->duration_minutes . ' min' : '-' }}</p>
                        </div>
                    </div>

                    @if($intervention->driver_notes)
                        <div class="mb-4">
                            <h6 class="text-muted mb-2">Notes du vidangeur</h6>
                            <p class="bg-light p-3 rounded">{{ $intervention->driver_notes }}</p>
                        </div>
                    @endif

                    <div class="row">
                        @if($intervention->photo_before)
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-2">Photo avant</h6>
                            <img src="{{ Storage::url($intervention->photo_before) }}" class="img-fluid rounded" alt="Photo avant" onerror="this.src='https://via.placeholder.com/400x300?text=Photo+non+disponible'">
                        </div>
                        @endif
                        @if($intervention->photo_after)
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-2">Photo apres</h6>
                            <img src="{{ Storage::url($intervention->photo_after) }}" class="img-fluid rounded" alt="Photo apres" onerror="this.src='https://via.placeholder.com/400x300?text=Photo+non+disponible'">
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Historique</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Intervention planifiee</span>
                            <span>{{ $intervention->scheduled_at?->format('d/m/Y H:i') ?? '-' }}</span>
                        </li>
                        @if($intervention->started_at)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Intervention demarree</span>
                            <span>{{ $intervention->started_at->format('d/m/Y H:i') }}</span>
                        </li>
                        @endif
                        @if($intervention->completed_at)
                        <li class="list-group-item d-flex justify-content-between text-success">
                            <span>Intervention terminee</span>
                            <span>{{ $intervention->completed_at->format('d/m/Y H:i') }}</span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5>Modifier l'intervention</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.interventions.update', $intervention) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label" for="status">Statut</label>
                            <select class="form-select" id="status" name="status">
                                <option value="scheduled" @selected($intervention->status === 'scheduled')>Planifiee</option>
                                <option value="in_progress" @selected($intervention->status === 'in_progress')>En cours</option>
                                <option value="completed" @selected($intervention->status === 'completed')>Terminee</option>
                                <option value="cancelled" @selected($intervention->status === 'cancelled')>Annulee</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="actual_volume">Volume reel (m³)</label>
                            <input type="number" step="0.1" class="form-control" id="actual_volume" name="actual_volume" value="{{ old('actual_volume', $intervention->actual_volume) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="driver_notes">Notes</label>
                            <textarea class="form-control" id="driver_notes" name="driver_notes" rows="3">{{ old('driver_notes', $intervention->driver_notes) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa fa-save me-1"></i> Enregistrer
                        </button>
                    </form>
                </div>
            </div>

            @if($intervention->serviceRequest)
            <div class="card">
                <div class="card-header">
                    <h5>Demande associee</h5>
                </div>
                <div class="card-body">
                    <p><strong>ID:</strong> #{{ $intervention->serviceRequest->id }}</p>
                    <p><strong>Prix:</strong> {{ number_format($intervention->serviceRequest->price_amount ?? 0, 0, ',', ' ') }} FCFA</p>
                    <p><strong>Paiement:</strong>
                        @if($intervention->serviceRequest->payment_status === 'paid')
                            <span class="badge bg-success">Paye</span>
                        @else
                            <span class="badge bg-warning">En attente</span>
                        @endif
                    </p>
                    <a href="{{ route('admin.service-requests.show', $intervention->serviceRequest) }}" class="btn btn-outline-primary w-100">
                        Voir la demande
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
