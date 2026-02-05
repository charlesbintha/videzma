@extends('layouts.doctor')

@section('title', 'Modifier creneau')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Modifier creneau</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('doctor.availabilities.index') }}">Disponibilites</a></li>
                    <li class="breadcrumb-item active">Modifier</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5>Modifier le creneau de disponibilite</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <p class="mb-0">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('doctor.availabilities.update', $availability) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Debut</label>
                                <input type="datetime-local" name="starts_at" class="form-control" required value="{{ old('starts_at', $availability->starts_at->format('Y-m-d\TH:i')) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fin</label>
                                <input type="datetime-local" name="ends_at" class="form-control" required value="{{ old('ends_at', $availability->ends_at->format('Y-m-d\TH:i')) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Note (optionnel)</label>
                            <textarea name="note" class="form-control" rows="3" placeholder="Informations supplementaires...">{{ old('note', $availability->note) }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                            <a href="{{ route('doctor.availabilities.index') }}" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
