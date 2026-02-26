@extends('layouts.master')

@section('title', 'Paiements')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Suivi des paiements</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Paiements</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">

    {{-- Statistiques --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-start border-success border-3 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:50px;height:50px">
                        <i class="fa fa-coins text-success"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 text-muted">Revenus totaux</h6>
                        <h4 class="mb-0 text-success">{{ number_format($stats['total_revenue'], 0, ',', ' ') }} <small class="fs-6">FCFA</small></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-start border-primary border-3 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:50px;height:50px">
                        <i class="fa fa-chart-bar text-primary"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 text-muted">Revenus ce mois</h6>
                        <h4 class="mb-0 text-primary">{{ number_format($stats['revenue_this_month'], 0, ',', ' ') }} <small class="fs-6">FCFA</small></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-start border-warning border-3 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:50px;height:50px">
                        <i class="fa fa-clock text-warning"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 text-muted">En attente</h6>
                        <h4 class="mb-0 text-warning">{{ $stats['pending_count'] }}</h4>
                        @if($stats['pending_amount'] > 0)
                            <small class="text-muted">{{ number_format($stats['pending_amount'], 0, ',', ' ') }} FCFA</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-start border-danger border-3 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:50px;height:50px">
                        <i class="fa fa-times-circle text-danger"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 text-muted">Échoués / annulés</h6>
                        <h4 class="mb-0 text-danger">{{ $stats['failed_count'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small text-muted">Recherche client / adresse</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Nom ou adresse..." value="{{ $search }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="all"          @selected($type === 'all')>Tous</option>
                        <option value="service"      @selected($type === 'service')>Demandes</option>
                        <option value="subscription" @selected($type === 'subscription')>Abonnements</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Statut paiement</label>
                    <select name="payment_status" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="paid"    @selected($paymentStatus === 'paid')>Payé</option>
                        <option value="pending" @selected($paymentStatus === 'pending')>En attente</option>
                        <option value="failed"  @selected($paymentStatus === 'failed')>Échoué</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Moyen de paiement</label>
                    <select name="payment_method" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="orange_money" @selected($paymentMethod === 'orange_money')>Orange Money</option>
                        <option value="wave"         @selected($paymentMethod === 'wave')>Wave</option>
                        <option value="cash"         @selected($paymentMethod === 'cash')>Espèces</option>
                        <option value="card"         @selected($paymentMethod === 'card')>Carte</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Du</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}">
                </div>
                <div class="col-md-1">
                    <label class="form-label small text-muted">Au</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}">
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-secondary btn-sm w-100">
                        <i class="fa fa-filter"></i>
                    </button>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary btn-sm" title="Réinitialiser">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    @php
        $methodLabels = ['orange_money' => 'Orange Money', 'wave' => 'Wave', 'cash' => 'Espèces', 'card' => 'Carte'];
        $methodBadge  = ['orange_money' => 'bg-warning text-dark', 'wave' => 'bg-info text-dark', 'cash' => 'bg-secondary', 'card' => 'bg-dark'];
    @endphp

    {{-- Tableau demandes de service --}}
    @if($type !== 'subscription' && $servicePayments->isNotEmpty())
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fa fa-water me-2"></i>Demandes de vidange</h5>
            <span class="badge bg-secondary">{{ $servicePayments->count() }} enregistrement(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Client</th>
                            <th>Adresse</th>
                            <th>Montant</th>
                            <th>Moyen</th>
                            <th>Statut</th>
                            <th>Référence Paytech</th>
                            <th>Date paiement</th>
                            <th>Date demande</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($servicePayments as $sr)
                        <tr>
                            <td class="text-muted small">#{{ $sr->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $sr->client->name ?? '-' }}</div>
                                <div class="text-muted small">{{ $sr->client->phone ?? '' }}</div>
                            </td>
                            <td class="text-truncate" style="max-width:160px" title="{{ $sr->address }}">{{ $sr->address }}</td>
                            <td class="fw-bold text-primary text-nowrap">{{ number_format($sr->price_amount ?? 0, 0, ',', ' ') }} FCFA</td>
                            <td>
                                <span class="badge {{ $methodBadge[$sr->payment_method ?? ''] ?? 'bg-secondary' }}">
                                    {{ $methodLabels[$sr->payment_method ?? ''] ?? $sr->payment_method }}
                                </span>
                            </td>
                            <td>
                                @if($sr->payment_status === 'paid')
                                    <span class="badge bg-success">✓ Payé</span>
                                @elseif($sr->payment_status === 'failed')
                                    <span class="badge bg-danger">✗ Échoué</span>
                                @else
                                    <span class="badge bg-warning">⏳ En attente</span>
                                @endif
                            </td>
                            <td>
                                @if($sr->payment_reference)
                                    <code class="small">{{ $sr->payment_reference }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-nowrap small">{{ $sr->paid_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td class="text-nowrap small text-muted">{{ $sr->requested_at?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                <a href="{{ route('admin.service-requests.show', $sr->id) }}" class="btn btn-sm btn-outline-secondary" title="Voir">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td colspan="3" class="text-end">Total payé :</td>
                            <td class="text-success">
                                {{ number_format($servicePayments->where('payment_status', 'paid')->sum('price_amount'), 0, ',', ' ') }} FCFA
                            </td>
                            <td colspan="6"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @elseif($type !== 'subscription')
    <div class="alert alert-light text-center py-4">Aucune demande de service correspondante.</div>
    @endif

    {{-- Tableau abonnements --}}
    @if($type !== 'service' && $subscriptionPayments->isNotEmpty())
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fa fa-repeat me-2"></i>Abonnements (forfaits)</h5>
            <span class="badge bg-secondary">{{ $subscriptionPayments->count() }} enregistrement(s)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Client</th>
                            <th>Forfait</th>
                            <th>Montant</th>
                            <th>Moyen</th>
                            <th>Statut</th>
                            <th>Référence Paytech</th>
                            <th>Date paiement</th>
                            <th>Souscrit le</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subscriptionPayments as $sub)
                        <tr>
                            <td class="text-muted small">#{{ $sub->id }}</td>
                            <td>
                                <div class="fw-semibold">{{ $sub->client->name ?? '-' }}</div>
                                <div class="text-muted small">{{ $sub->client->phone ?? '' }}</div>
                            </td>
                            <td>{{ $sub->plan->name ?? '-' }}</td>
                            <td class="fw-bold text-primary text-nowrap">
                                {{ number_format($sub->plan->price ?? 0, 0, ',', ' ') }} FCFA
                            </td>
                            <td>
                                <span class="badge {{ $methodBadge[$sub->payment_method ?? ''] ?? 'bg-secondary' }}">
                                    {{ $methodLabels[$sub->payment_method ?? ''] ?? $sub->payment_method }}
                                </span>
                            </td>
                            <td>
                                @if($sub->payment_status === 'paid')
                                    <span class="badge bg-success">✓ Payé</span>
                                @elseif($sub->payment_status === 'failed')
                                    <span class="badge bg-danger">✗ Échoué</span>
                                @else
                                    <span class="badge bg-warning">⏳ En attente</span>
                                @endif
                            </td>
                            <td>
                                @if($sub->payment_reference)
                                    <code class="small">{{ $sub->payment_reference }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-nowrap small">{{ $sub->paid_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td class="text-nowrap small text-muted">{{ $sub->created_at?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                <a href="{{ route('admin.client-subscriptions.show', $sub->id) }}" class="btn btn-sm btn-outline-secondary" title="Voir">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light fw-semibold">
                        <tr>
                            <td colspan="3" class="text-end">Total payé :</td>
                            <td class="text-success">
                                {{ number_format(
                                    $subscriptionPayments->where('payment_status', 'paid')->sum(fn($s) => $s->plan->price ?? 0),
                                    0, ',', ' '
                                ) }} FCFA
                            </td>
                            <td colspan="6"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @elseif($type !== 'service')
    <div class="alert alert-light text-center py-4">Aucun abonnement correspondant.</div>
    @endif

</div>
@endsection
