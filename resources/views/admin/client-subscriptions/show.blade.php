@extends('layouts.master')

@section('title', 'Detail abonnement')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Detail de l'abonnement</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.client-subscriptions.index') }}">Abonnements</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <!-- Informations client -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Informations client</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nom:</strong> {{ $subscription->client->name ?? 'N/A' }}</p>
                            <p><strong>Email:</strong> {{ $subscription->client->email ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Telephone:</strong> {{ $subscription->client->phone ?? 'Non renseigne' }}</p>
                            <p><strong>Statut compte:</strong>
                                @if($subscription->client?->status === 'active')
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-secondary">{{ $subscription->client?->status }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details du forfait -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Forfait: {{ $subscription->plan->name ?? 'N/A' }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center border-end">
                            <h3 class="text-primary">{{ $subscription->plan->formatted_price ?? 'N/A' }}</h3>
                            <p class="text-muted">Prix du forfait</p>
                        </div>
                        <div class="col-md-4 text-center border-end">
                            <h3>{{ $subscription->plan->interventions_per_period ?? 0 }}</h3>
                            <p class="text-muted">Interventions / {{ $subscription->plan->periodicity_label ?? '' }}</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <h3>{{ $subscription->plan->max_volume_per_intervention ?? 0 }} m³</h3>
                            <p class="text-muted">Volume max / intervention</p>
                        </div>
                    </div>
                    @if($subscription->plan->description)
                        <hr>
                        <p class="text-muted">{{ $subscription->plan->description }}</p>
                    @endif
                </div>
            </div>

            <!-- Utilisation -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Utilisation de la periode</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Interventions utilisees</label>
                            <div class="progress" style="height: 25px;">
                                @php
                                    $maxInterventions = $subscription->plan->interventions_per_period ?? 1;
                                    $interventionPercent = min(100, ($subscription->interventions_used / $maxInterventions) * 100);
                                @endphp
                                <div class="progress-bar {{ $interventionPercent >= 100 ? 'bg-danger' : ($interventionPercent >= 80 ? 'bg-warning' : 'bg-success') }}" role="progressbar" style="width: {{ $interventionPercent }}%">
                                    {{ $subscription->interventions_used }} / {{ $maxInterventions }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Volume utilise</label>
                            <p class="h4">{{ number_format($subscription->volume_used, 1) }} m³</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Jours restants</label>
                            <div class="progress" style="height: 25px;">
                                @php
                                    $totalDays = $subscription->plan->period_days ?? 30;
                                    $daysPercent = min(100, ($subscription->remaining_days / $totalDays) * 100);
                                @endphp
                                <div class="progress-bar {{ $daysPercent <= 10 ? 'bg-danger' : ($daysPercent <= 30 ? 'bg-warning' : 'bg-info') }}" role="progressbar" style="width: {{ $daysPercent }}%">
                                    {{ $subscription->remaining_days }} jours
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Periode</label>
                            <p>
                                {{ $subscription->current_period_start?->format('d/m/Y') }}
                                -
                                {{ $subscription->current_period_end?->format('d/m/Y') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Statut -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Statut</h5>
                </div>
                <div class="card-body text-center">
                    @if($subscription->isExpired())
                        <span class="badge bg-secondary p-3 fs-5">Expire</span>
                    @else
                        @switch($subscription->status)
                            @case('active')
                                <span class="badge bg-success p-3 fs-5">Actif</span>
                                @break
                            @case('paused')
                                <span class="badge bg-warning p-3 fs-5">En pause</span>
                                @break
                            @case('cancelled')
                                <span class="badge bg-danger p-3 fs-5">Annule</span>
                                @break
                        @endswitch
                    @endif

                    <div class="mt-3">
                        @if($subscription->auto_renew)
                            <span class="badge bg-info">Renouvellement auto</span>
                        @else
                            <span class="badge bg-secondary">Pas de renouvellement auto</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Paiement -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Paiement</h5>
                </div>
                <div class="card-body">
                    <p>
                        <strong>Statut:</strong>
                        @if($subscription->payment_status === 'paid')
                            <span class="badge bg-success">Paye</span>
                        @else
                            <span class="badge bg-warning">En attente</span>
                        @endif
                    </p>
                    <p><strong>Methode:</strong> {{ ucfirst(str_replace('_', ' ', $subscription->payment_method ?? 'Non specifie')) }}</p>
                    @if($subscription->paid_at)
                        <p><strong>Paye le:</strong> {{ $subscription->paid_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.client-subscriptions.edit', $subscription) }}" class="btn btn-primary">
                            <i class="fa fa-edit me-1"></i> Modifier
                        </a>

                        @if($subscription->status === 'active' && !$subscription->isExpired())
                            <form action="{{ route('admin.client-subscriptions.pause', $subscription) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="fa fa-pause me-1"></i> Mettre en pause
                                </button>
                            </form>
                        @endif

                        @if($subscription->status === 'paused')
                            <form action="{{ route('admin.client-subscriptions.resume', $subscription) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fa fa-play me-1"></i> Reprendre
                                </button>
                            </form>
                        @endif

                        @if($subscription->isExpired() || $subscription->status === 'cancelled')
                            <form action="{{ route('admin.client-subscriptions.renew', $subscription) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fa fa-refresh me-1"></i> Renouveler
                                </button>
                            </form>
                        @endif

                        @if($subscription->payment_status !== 'paid')
                            <form action="{{ route('admin.client-subscriptions.mark-paid', $subscription) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-success w-100">
                                    <i class="fa fa-check me-1"></i> Marquer comme paye
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('admin.client-subscriptions.index') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left me-1"></i> Retour a la liste
                        </a>
                    </div>
                </div>
            </div>

            <!-- Dates -->
            <div class="card">
                <div class="card-header">
                    <h5>Historique</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Cree le:</th>
                            <td>{{ $subscription->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Mis a jour:</th>
                            <td>{{ $subscription->updated_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                        @if($subscription->paused_at)
                            <tr>
                                <th>En pause le:</th>
                                <td>{{ $subscription->paused_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endif
                        @if($subscription->cancelled_at)
                            <tr>
                                <th>Annule le:</th>
                                <td>{{ $subscription->cancelled_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
