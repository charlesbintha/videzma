@extends('layouts.doctor')

@section('title', 'Mes disponibilites')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Mes disponibilites</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('doctor.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Disponibilites</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5>Creneaux de disponibilite</h5>
                <a href="{{ route('doctor.availabilities.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Nouveau creneau
                </a>
            </div>
        </div>
        <div class="card-body">
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

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Debut</th>
                            <th>Fin</th>
                            <th>Note</th>
                            <th>Statut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($availabilities as $availability)
                            <tr>
                                <td>{{ $availability->id }}</td>
                                <td>{{ $availability->starts_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $availability->ends_at->format('d/m/Y H:i') }}</td>
                                <td>{{ Str::limit($availability->note, 30) ?? '-' }}</td>
                                <td>
                                    @if($availability->is_booked)
                                        <span class="badge bg-warning">Reserve</span>
                                    @else
                                        <span class="badge bg-success">Disponible</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$availability->is_booked)
                                        <a href="{{ route('doctor.availabilities.edit', $availability) }}" class="btn btn-sm btn-outline-primary">Modifier</a>
                                        <form action="{{ route('doctor.availabilities.destroy', $availability) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Supprimer ce creneau ?')">Supprimer</button>
                                        </form>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Aucun creneau de disponibilite.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $availabilities->links() }}
        </div>
    </div>
</div>
@endsection
