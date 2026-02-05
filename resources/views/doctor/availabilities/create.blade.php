@extends('layouts.doctor')

@section('title', 'Nouveau creneau')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Nouveau creneau</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('doctor.availabilities.index') }}">Disponibilites</a></li>
                    <li class="breadcrumb-item active">Nouveau</li>
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
                    <h5>Ajouter un creneau de disponibilite</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            @foreach($errors->all() as $error)
                                <p class="mb-0">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('doctor.availabilities.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Debut</label>
                                <input type="datetime-local" name="starts_at" class="form-control" required min="{{ now()->addMinutes(15)->format('Y-m-d\TH:i') }}" value="{{ old('starts_at') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fin</label>
                                <input type="datetime-local" name="ends_at" class="form-control" required value="{{ old('ends_at') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Note (optionnel)</label>
                            <textarea name="note" class="form-control" rows="3" placeholder="Informations supplementaires...">{{ old('note') }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Creer</button>
                            <a href="{{ route('doctor.availabilities.index') }}" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
