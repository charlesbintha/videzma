@extends('layouts.master')

@section('title', 'Utilisateurs')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Utilisateurs</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Utilisateurs</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>Liste des utilisateurs</h5>
            <span>Filtrer par role, statut, ou rechercher par nom, email, telephone.</span>
        </div>
        <div class="card-body">
            <form class="row g-3 mb-3" method="get">
                <div class="col-md-5">
                    <input class="form-control" type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher nom, email, telephone">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="role">
                        <option value="">Tous les roles</option>
                        <option value="patient" @selected(request('role') === 'patient')>Patient</option>
                        <option value="doctor" @selected(request('role') === 'doctor')>Medecin</option>
                        <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="pending" @selected(request('status') === 'pending')>En attente</option>
                        <option value="active" @selected(request('status') === 'active')>Actif</option>
                        <option value="suspended" @selected(request('status') === 'suspended')>Suspendu</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary w-100" type="submit">OK</button>
                </div>
            </form>
            @php($roleLabels = ['patient' => 'Patient', 'doctor' => 'Medecin', 'admin' => 'Admin'])
            @php($statusLabels = ['pending' => 'En attente', 'active' => 'Actif', 'suspended' => 'Suspendu'])
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Statut</th>
                            <th>Telephone</th>
                            <th>Langue</th>
                            <th>Cree le</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $roleLabels[$user->role] ?? $user->role }}</td>
                                <td>{{ $statusLabels[$user->status] ?? $user->status }}</td>
                                <td>{{ $user->phone ?? '-' }}</td>
                                <td>{{ $user->locale }}</td>
                                <td>{{ $user->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">Aucun utilisateur trouve.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
