@extends('layouts.master')

@section('title', 'Modifier forfait')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Modifier forfait</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.subscription-plans.index') }}">Forfaits</a></li>
                    <li class="breadcrumb-item active">{{ $plan->name }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>Modifier: {{ $plan->name }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.subscription-plans.update', $plan) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom du forfait *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $plan->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="periodicity" class="form-label">Periodicite *</label>
                            <select class="form-select @error('periodicity') is-invalid @enderror" id="periodicity" name="periodicity" required>
                                @foreach($periodicities as $key => $label)
                                    <option value="{{ $key }}" @selected(old('periodicity', $plan->periodicity) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('periodicity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="2">{{ old('description', $plan->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="interventions_per_period" class="form-label">Interventions par periode *</label>
                            <input type="number" class="form-control @error('interventions_per_period') is-invalid @enderror" id="interventions_per_period" name="interventions_per_period" value="{{ old('interventions_per_period', $plan->interventions_per_period) }}" min="1" max="50" required>
                            @error('interventions_per_period')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="max_volume_per_intervention" class="form-label">Volume max par intervention (m³) *</label>
                            <input type="number" step="0.5" class="form-control @error('max_volume_per_intervention') is-invalid @enderror" id="max_volume_per_intervention" name="max_volume_per_intervention" value="{{ old('max_volume_per_intervention', $plan->max_volume_per_intervention) }}" min="1" max="100" required>
                            @error('max_volume_per_intervention')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="extra_volume_price" class="form-label">Prix m³ supplementaire (FCFA) *</label>
                            <input type="number" class="form-control @error('extra_volume_price') is-invalid @enderror" id="extra_volume_price" name="extra_volume_price" value="{{ old('extra_volume_price', $plan->extra_volume_price) }}" min="0" required>
                            @error('extra_volume_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="price" class="form-label">Prix du forfait (FCFA) *</label>
                            <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $plan->price) }}" min="1000" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="discount_percent" class="form-label">Remise (%) *</label>
                            <input type="number" class="form-control @error('discount_percent') is-invalid @enderror" id="discount_percent" name="discount_percent" value="{{ old('discount_percent', $plan->discount_percent) }}" min="0" max="100" required>
                            @error('discount_percent')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="display_order" class="form-label">Ordre d'affichage *</label>
                            <input type="number" class="form-control @error('display_order') is-invalid @enderror" id="display_order" name="display_order" value="{{ old('display_order', $plan->display_order) }}" min="0" required>
                            @error('display_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $plan->is_active))>
                            <label class="form-check-label" for="is_active">
                                Forfait actif (visible aux clients)
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" @checked(old('is_featured', $plan->is_featured))>
                            <label class="form-check-label" for="is_featured">
                                Forfait populaire (mis en avant)
                            </label>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.subscription-plans.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Retour
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
