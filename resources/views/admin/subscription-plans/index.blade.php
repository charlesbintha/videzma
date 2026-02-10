@extends('layouts.master')

@section('title', 'Forfaits')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Forfaits</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Forfaits</li>
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

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Liste des forfaits</h5>
            <a href="{{ route('admin.subscription-plans.create') }}" class="btn btn-primary">
                <i class="fa fa-plus me-1"></i> Nouveau forfait
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Ordre</th>
                            <th>Nom</th>
                            <th>Periodicite</th>
                            <th>Interventions</th>
                            <th>Volume max</th>
                            <th>Prix</th>
                            <th>Remise</th>
                            <th>Abonnes</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($plans as $plan)
                            <tr>
                                <td>{{ $plan->display_order }}</td>
                                <td>
                                    <strong>{{ $plan->name }}</strong>
                                    @if($plan->is_featured)
                                        <span class="badge bg-warning ms-1">Populaire</span>
                                    @endif
                                </td>
                                <td>{{ $plan->periodicity_label }}</td>
                                <td>{{ $plan->interventions_per_period }} / periode</td>
                                <td>{{ $plan->max_volume_per_intervention }} m³</td>
                                <td>{{ $plan->formatted_price }}</td>
                                <td>{{ $plan->discount_percent }}%</td>
                                <td>
                                    <span class="badge bg-info">{{ $plan->subscriptions_count }}</span>
                                </td>
                                <td>
                                    @if($plan->is_active)
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-secondary">Inactif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.subscription-plans.edit', $plan) }}" class="btn btn-sm btn-primary" title="Modifier">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.subscription-plans.toggle', $plan) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $plan->is_active ? 'btn-warning' : 'btn-success' }}" title="{{ $plan->is_active ? 'Desactiver' : 'Activer' }}">
                                                <i class="fa {{ $plan->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.subscription-plans.destroy', $plan) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce forfait?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                    Aucun forfait configure. <a href="{{ route('admin.subscription-plans.create') }}">Creer le premier</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5>Periodicites disponibles</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach(\App\Models\SubscriptionPlan::PERIODICITIES as $key => $label)
                    <div class="col-md-2 text-center mb-3">
                        <div class="p-3 border rounded">
                            <strong>{{ $label }}</strong>
                            <br>
                            <small class="text-muted">{{ \App\Models\SubscriptionPlan::PERIODICITY_DAYS[$key] }} jours</small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
