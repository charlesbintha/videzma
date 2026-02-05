@extends('layouts.master')

@section('title', 'Medecins')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Medecins</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Medecins</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-1">Profils medecins</h5>
                    <span>Gerer la verification et les details du profil.</span>
                </div>
                <a class="btn btn-primary" href="{{ route('admin.doctors.create') }}">Nouveau medecin</a>
            </div>
        </div>
        <div class="card-body">
            <form class="row g-3 mb-3" method="get">
                <div class="col-md-6">
                    <select class="form-select" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="pending" @selected(request('status') === 'pending')>En attente</option>
                        <option value="active" @selected(request('status') === 'active')>Actif</option>
                        <option value="suspended" @selected(request('status') === 'suspended')>Suspendu</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">OK</button>
                </div>
            </form>
            @php($statusLabels = ['pending' => 'En attente', 'active' => 'Actif', 'suspended' => 'Suspendu'])
            @php($verificationLabels = ['pending' => 'En attente', 'approved' => 'Approuve', 'rejected' => 'Refuse'])
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Specialite</th>
                            <th>Verification</th>
                            <th>Statut</th>
                            <th>Cree le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($doctors as $doctor)
                            <tr>
                                <td>{{ $doctor->name }}</td>
                                <td>{{ $doctor->email }}</td>
                                <td>{{ optional($doctor->doctorProfile)->specialty ?? '-' }}</td>
                                <td>{{ $verificationLabels[optional($doctor->doctorProfile)->verification_status] ?? (optional($doctor->doctorProfile)->verification_status ?? '-') }}</td>
                                <td>{{ $statusLabels[$doctor->status] ?? $doctor->status }}</td>
                                <td>{{ $doctor->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <a class="btn btn-sm btn-primary" href="{{ route('admin.doctors.show', $doctor) }}">Voir</a>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.doctors.edit', $doctor) }}">Modifier</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">Aucun medecin trouve.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $doctors->links() }}
        </div>
    </div>
</div>
@endsection
