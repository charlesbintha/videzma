@extends('layouts.master')

@section('title', 'Nouvel abonnement')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Nouvel abonnement</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.client-subscriptions.index') }}">Abonnements</a></li>
                    <li class="breadcrumb-item active">Nouveau</li>
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
                    <h5>Creer un abonnement client</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.client-subscriptions.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="client_id" class="form-label">Client *</label>
                            <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id" required>
                                <option value="">Selectionner un client...</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>
                                        {{ $client->name }} ({{ $client->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="plan_id" class="form-label">Forfait *</label>
                            <select class="form-select @error('plan_id') is-invalid @enderror" id="plan_id" name="plan_id" required>
                                <option value="">Selectionner un forfait...</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id) data-price="{{ $plan->price }}" data-interventions="{{ $plan->interventions_per_period }}" data-periodicity="{{ $plan->periodicity_label }}">
                                        {{ $plan->name }} - {{ $plan->formatted_price }} ({{ $plan->periodicity_label }})
                                    </option>
                                @endforeach
                            </select>
                            @error('plan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="plan-details" class="alert alert-info d-none mb-3">
                            <strong>Details du forfait:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Periodicite: <span id="plan-periodicity"></span></li>
                                <li>Interventions par periode: <span id="plan-interventions"></span></li>
                                <li>Prix: <span id="plan-price"></span> FCFA</li>
                            </ul>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">Methode de paiement *</label>
                                    <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" required>
                                        <option value="">Selectionner...</option>
                                        <option value="cash" @selected(old('payment_method') === 'cash')>Especes</option>
                                        <option value="mobile_money" @selected(old('payment_method') === 'mobile_money')>Mobile Money</option>
                                        <option value="card" @selected(old('payment_method') === 'card')>Carte bancaire</option>
                                        <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Virement bancaire</option>
                                    </select>
                                    @error('payment_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_status" class="form-label">Statut du paiement *</label>
                                    <select class="form-select @error('payment_status') is-invalid @enderror" id="payment_status" name="payment_status" required>
                                        <option value="pending" @selected(old('payment_status') === 'pending')>En attente</option>
                                        <option value="paid" @selected(old('payment_status') === 'paid')>Paye</option>
                                    </select>
                                    @error('payment_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="auto_renew" name="auto_renew" value="1" @checked(old('auto_renew'))>
                            <label class="form-check-label" for="auto_renew">
                                Renouvellement automatique
                            </label>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.client-subscriptions.index') }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left me-1"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i> Creer l'abonnement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Forfaits disponibles</h5>
                </div>
                <div class="card-body">
                    @foreach($plans as $plan)
                        <div class="border rounded p-3 mb-3 {{ $plan->is_featured ? 'border-warning' : '' }}">
                            @if($plan->is_featured)
                                <span class="badge bg-warning float-end">Populaire</span>
                            @endif
                            <h6>{{ $plan->name }}</h6>
                            <p class="text-muted small mb-2">{{ $plan->description }}</p>
                            <ul class="list-unstyled small">
                                <li><i class="fa fa-check text-success me-1"></i> {{ $plan->interventions_per_period }} interventions / {{ $plan->periodicity_label }}</li>
                                <li><i class="fa fa-check text-success me-1"></i> {{ $plan->max_volume_per_intervention }} m³ max / intervention</li>
                                <li><i class="fa fa-check text-success me-1"></i> {{ $plan->discount_percent }}% de remise</li>
                            </ul>
                            <strong class="text-primary">{{ $plan->formatted_price }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('plan_id').addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        const details = document.getElementById('plan-details');

        if (this.value) {
            details.classList.remove('d-none');
            document.getElementById('plan-periodicity').textContent = option.dataset.periodicity;
            document.getElementById('plan-interventions').textContent = option.dataset.interventions;
            document.getElementById('plan-price').textContent = new Intl.NumberFormat('fr-FR').format(option.dataset.price);
        } else {
            details.classList.add('d-none');
        }
    });
</script>
@endpush
@endsection
