@extends('layouts.master')

@section('title', 'Modifier abonnement')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Modifier l'abonnement</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.client-subscriptions.index') }}">Abonnements</a></li>
                    <li class="breadcrumb-item active">Modifier</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Abonnement de {{ $subscription->client->name ?? 'N/A' }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.client-subscriptions.update', $subscription) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="alert alert-secondary">
                            <strong>Client:</strong> {{ $subscription->client->name ?? 'N/A' }}<br>
                            <strong>Email:</strong> {{ $subscription->client->email ?? 'N/A' }}<br>
                            <strong>Cree le:</strong> {{ $subscription->created_at?->format('d/m/Y H:i') }}
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="plan_id" class="form-label">Forfait *</label>
                                    <select class="form-select @error('plan_id') is-invalid @enderror" id="plan_id" name="plan_id" required>
                                        @foreach($plans as $plan)
                                            <option value="{{ $plan->id }}" @selected(old('plan_id', $subscription->plan_id) == $plan->id)>
                                                {{ $plan->name }} - {{ $plan->formatted_price }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('plan_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Statut *</label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="active" @selected(old('status', $subscription->status) === 'active')>Actif</option>
                                        <option value="paused" @selected(old('status', $subscription->status) === 'paused')>En pause</option>
                                        <option value="cancelled" @selected(old('status', $subscription->status) === 'cancelled')>Annule</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="current_period_end" class="form-label">Fin de periode *</label>
                                    <input type="date" class="form-control @error('current_period_end') is-invalid @enderror" id="current_period_end" name="current_period_end" value="{{ old('current_period_end', $subscription->current_period_end?->format('Y-m-d')) }}" required>
                                    @error('current_period_end')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="interventions_used" class="form-label">Interventions utilisees</label>
                                    <input type="number" class="form-control @error('interventions_used') is-invalid @enderror" id="interventions_used" name="interventions_used" value="{{ old('interventions_used', $subscription->interventions_used) }}" min="0" required>
                                    @error('interventions_used')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Max: {{ $subscription->plan->interventions_per_period ?? '?' }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_status" class="form-label">Statut du paiement *</label>
                                    <select class="form-select @error('payment_status') is-invalid @enderror" id="payment_status" name="payment_status" required>
                                        <option value="pending" @selected(old('payment_status', $subscription->payment_status) === 'pending')>En attente</option>
                                        <option value="paid" @selected(old('payment_status', $subscription->payment_status) === 'paid')>Paye</option>
                                    </select>
                                    @error('payment_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3 pt-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="auto_renew" name="auto_renew" value="1" @checked(old('auto_renew', $subscription->auto_renew))>
                                        <label class="form-check-label" for="auto_renew">
                                            Renouvellement automatique
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.client-subscriptions.index') }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left me-1"></i> Retour
                            </a>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save me-1"></i> Enregistrer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Informations</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Debut periode:</th>
                            <td>{{ $subscription->current_period_start?->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Fin periode:</th>
                            <td>{{ $subscription->current_period_end?->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Jours restants:</th>
                            <td>{{ $subscription->remaining_days }}</td>
                        </tr>
                        <tr>
                            <th>Volume utilise:</th>
                            <td>{{ number_format($subscription->volume_used, 1) }} m³</td>
                        </tr>
                        <tr>
                            <th>Methode paiement:</th>
                            <td>{{ ucfirst(str_replace('_', ' ', $subscription->payment_method ?? '')) }}</td>
                        </tr>
                        @if($subscription->paid_at)
                            <tr>
                                <th>Paye le:</th>
                                <td>{{ $subscription->paid_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endif
                        @if($subscription->paused_at)
                            <tr>
                                <th>En pause depuis:</th>
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

            <div class="card mt-3">
                <div class="card-header bg-danger text-white">
                    <h5>Zone de danger</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">La suppression de cet abonnement est irreversible.</p>
                    <form action="{{ route('admin.client-subscriptions.destroy', $subscription) }}" method="POST" onsubmit="return confirm('Etes-vous sur de vouloir supprimer cet abonnement?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fa fa-trash me-1"></i> Supprimer l'abonnement
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
