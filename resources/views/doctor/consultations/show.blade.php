@extends('layouts.doctor')

@section('title', 'Consultation #' . $consultation->id)

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Consultation #{{ $consultation->id }}</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('doctor.consultations.index') }}">Consultations</a></li>
                    <li class="breadcrumb-item active">#{{ $consultation->id }}</li>
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
        <!-- Consultation details -->
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Details de la consultation</h5>
                    @switch($consultation->status)
                        @case('pending')
                        @case('assigned')
                            <span class="badge bg-warning">En attente</span>
                            @break
                        @case('accepted')
                            <span class="badge bg-success">Accepte</span>
                            @break
                        @case('rejected')
                            <span class="badge bg-danger">Rejete</span>
                            @break
                        @case('closed')
                            <span class="badge bg-secondary">Cloture</span>
                            @break
                        @case('expired')
                            <span class="badge bg-dark">Expire</span>
                            @break
                    @endswitch
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Motif:</strong>
                            <p>{{ $consultation->reason }}</p>
                        </div>
                        <div class="col-md-6">
                            <strong>Notes:</strong>
                            <p>{{ $consultation->notes ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Demande le:</strong>
                            <p>{{ $consultation->requested_at?->format('d/m/Y H:i') ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Echeance SLA:</strong>
                            <p class="{{ $consultation->sla_due_at && $consultation->sla_due_at->isPast() ? 'text-danger' : '' }}">
                                {{ $consultation->sla_due_at?->format('d/m/Y H:i') ?? '-' }}
                            </p>
                        </div>
                        <div class="col-md-4">
                            @if($consultation->accepted_at)
                                <strong>Accepte le:</strong>
                                <p>{{ $consultation->accepted_at->format('d/m/Y H:i') }}</p>
                            @elseif($consultation->rejected_at)
                                <strong>Rejete le:</strong>
                                <p>{{ $consultation->rejected_at->format('d/m/Y H:i') }}</p>
                            @elseif($consultation->closed_at)
                                <strong>Cloture le:</strong>
                                <p>{{ $consultation->closed_at->format('d/m/Y H:i') }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Patient info -->
                    <h6 class="mt-4">Patient</h6>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Nom:</strong>
                            <p>{{ $consultation->patient?->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Telephone:</strong>
                            <p>{{ $consultation->patient?->phone ?? '-' }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Email:</strong>
                            <p>{{ $consultation->patient?->email ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Location info -->
                    @if($consultation->location)
                        <h6 class="mt-4">Localisation</h6>
                        <hr>
                        <p>{{ $consultation->location->address ?? 'Coordonnees: ' . $consultation->location->latitude . ', ' . $consultation->location->longitude }}</p>
                    @endif

                    <!-- Appointment info -->
                    @if($consultation->appointment)
                        <h6 class="mt-4">Rendez-vous</h6>
                        <hr>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Planifie pour:</strong>
                                <p>{{ $consultation->appointment->scheduled_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="col-md-4">
                                <strong>Statut:</strong>
                                <p>{{ $consultation->appointment->status }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Comments section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Commentaires</h5>
                </div>
                <div class="card-body">
                    @forelse($consultation->comments as $comment)
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $comment->author?->name ?? 'Inconnu' }}</strong>
                                <small class="text-muted">{{ $comment->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                            <p class="mb-0 mt-2">{{ $comment->content }}</p>
                            @if($comment->is_internal)
                                <small class="text-warning"><i class="fa fa-lock"></i> Note interne</small>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted">Aucun commentaire.</p>
                    @endforelse

                    <!-- Add comment form -->
                    <form action="{{ route('doctor.consultations.comments.store', $consultation) }}" method="POST" class="mt-4">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Ajouter un commentaire</label>
                            <textarea name="content" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" name="is_internal" value="1" class="form-check-input" id="is_internal">
                            <label class="form-check-label" for="is_internal">Note interne (non visible par le patient)</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </form>
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
                    @if(in_array($consultation->status, ['pending', 'assigned']))
                        <form action="{{ route('doctor.consultations.accept', $consultation) }}" method="POST" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fa fa-check"></i> Accepter la demande
                            </button>
                        </form>

                        <form action="{{ route('doctor.consultations.reject', $consultation) }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <textarea name="reason" class="form-control" rows="2" placeholder="Raison du rejet (optionnel)"></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fa fa-times"></i> Rejeter la demande
                            </button>
                        </form>
                    @endif

                    @if($consultation->status === 'accepted')
                        @if(!$consultation->appointment)
                            <a href="{{ route('doctor.appointments.create') }}?consultation_id={{ $consultation->id }}" class="btn btn-primary w-100 mb-3">
                                <i class="fa fa-calendar-plus"></i> Planifier un RDV
                            </a>
                        @endif

                        <form action="{{ route('doctor.consultations.close', $consultation) }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <textarea name="notes" class="form-control" rows="2" placeholder="Notes de cloture (optionnel)"></textarea>
                            </div>
                            <button type="submit" class="btn btn-secondary w-100">
                                <i class="fa fa-archive"></i> Cloturer la consultation
                            </button>
                        </form>
                    @endif

                    @if(in_array($consultation->status, ['closed', 'rejected', 'expired']))
                        <p class="text-muted text-center">Cette consultation est terminee.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
