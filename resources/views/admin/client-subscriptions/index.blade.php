@extends('layouts.master')

@section('title', 'Abonnements clients')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Abonnements clients</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Abonnements</li>
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

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total</h5>
                    <h2>{{ $stats['total'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Actifs</h5>
                    <h2>{{ $stats['active'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">En pause</h5>
                    <h2>{{ $stats['paused'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5 class="card-title">Expires</h5>
                    <h2>{{ $stats['expired'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Liste des abonnements</h5>
            <a href="{{ route('admin.client-subscriptions.create') }}" class="btn btn-primary">
                <i class="fa fa-plus me-1"></i> Nouvel abonnement
            </a>
        </div>
        <div class="card-body">
            <!-- Filtres -->
            <form action="" method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Rechercher client..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Tous statuts</option>
                        <option value="active" @selected(request('status') === 'active')>Actif</option>
                        <option value="paused" @selected(request('status') === 'paused')>En pause</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Annule</option>
                        <option value="expired" @selected(request('status') === 'expired')>Expire</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="plan_id" class="form-select">
                        <option value="">Tous forfaits</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" @selected(request('plan_id') == $plan->id)>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="fa fa-filter me-1"></i> Filtrer
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.client-subscriptions.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fa fa-times me-1"></i> Reset
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Forfait</th>
                            <th>Periode</th>
                            <th>Interventions</th>
                            <th>Paiement</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($subscriptions as $sub)
                            <tr>
                                <td>
                                    <strong>{{ $sub->client->name ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $sub->client->email ?? '' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $sub->plan->name ?? 'N/A' }}</span>
                                    <br>
                                    <small>{{ $sub->plan->formatted_price ?? '' }}</small>
                                </td>
                                <td>
                                    {{ $sub->current_period_start?->format('d/m/Y') }} - {{ $sub->current_period_end?->format('d/m/Y') }}
                                    <br>
                                    @if($sub->isExpired())
                                        <small class="text-danger">Expire</small>
                                    @else
                                        <small class="text-success">{{ $sub->remaining_days }} jours restants</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $sub->interventions_used }} / {{ $sub->plan->interventions_per_period ?? '?' }}
                                    <br>
                                    <small>{{ number_format($sub->volume_used, 1) }} m³ utilises</small>
                                </td>
                                <td>
                                    @if($sub->payment_status === 'paid')
                                        <span class="badge bg-success">Paye</span>
                                    @else
                                        <span class="badge bg-warning">En attente</span>
                                    @endif
                                    <br>
                                    <small>{{ ucfirst(str_replace('_', ' ', $sub->payment_method ?? '')) }}</small>
                                </td>
                                <td>
                                    @switch($sub->status)
                                        @case('active')
                                            @if($sub->isExpired())
                                                <span class="badge bg-secondary">Expire</span>
                                            @else
                                                <span class="badge bg-success">Actif</span>
                                            @endif
                                            @break
                                        @case('paused')
                                            <span class="badge bg-warning">En pause</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge bg-danger">Annule</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $sub->status }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.client-subscriptions.show', $sub) }}" class="btn btn-sm btn-info" title="Voir">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.client-subscriptions.edit', $sub) }}" class="btn btn-sm btn-primary" title="Modifier">
                                            <i class="fa fa-edit"></i>
                                        </a>

                                        @if($sub->status === 'active' && !$sub->isExpired())
                                            <form action="{{ route('admin.client-subscriptions.pause', $sub) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning" title="Mettre en pause">
                                                    <i class="fa fa-pause"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if($sub->status === 'paused')
                                            <form action="{{ route('admin.client-subscriptions.resume', $sub) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Reprendre">
                                                    <i class="fa fa-play"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if($sub->isExpired() || $sub->status === 'cancelled')
                                            <form action="{{ route('admin.client-subscriptions.renew', $sub) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Renouveler">
                                                    <i class="fa fa-refresh"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if($sub->payment_status !== 'paid')
                                            <form action="{{ route('admin.client-subscriptions.mark-paid', $sub) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Marquer paye">
                                                    <i class="fa fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                    Aucun abonnement trouve.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $subscriptions->links() }}
        </div>
    </div>
</div>
@endsection
