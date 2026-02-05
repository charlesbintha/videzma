@extends('layouts.master')

@section('title', 'Documents medecins')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Documents medecins</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Documents medecins</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>File d'attente de verification</h5>
            <span>Verifier les documents transmis par les medecins.</span>
        </div>
        <div class="card-body">
            <form class="row g-3 mb-3" method="get">
                <div class="col-md-6">
                    <select class="form-select" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="pending" @selected(request('status') === 'pending')>En attente</option>
                        <option value="approved" @selected(request('status') === 'approved')>Approuve</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Refuse</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">OK</button>
                </div>
            </form>
            @php($statusLabels = ['pending' => 'En attente', 'approved' => 'Approuve', 'rejected' => 'Refuse'])
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Medecin</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Verifie par</th>
                            <th>Verifie le</th>
                            <th>Soumis le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($documents as $document)
                            <tr>
                                <td>
                                    @if ($document->doctor)
                                        <a href="{{ route('admin.doctors.show', $document->doctor) }}">{{ $document->doctor->name }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $document->type }}</td>
                                <td>{{ $statusLabels[$document->status] ?? $document->status }}</td>
                                <td>{{ optional($document->reviewer)->name ?? '-' }}</td>
                                <td>{{ $document->reviewed_at ? $document->reviewed_at->format('Y-m-d') : '-' }}</td>
                                <td>{{ $document->created_at->format('Y-m-d') }}</td>
                                <td>
                                    @if ($document->file_path)
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.doctor-documents.download', $document) }}">Telecharger</a>
                                    @endif
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.doctor-documents.show', $document) }}">Verifier</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">Aucun document trouve.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $documents->links() }}
        </div>
    </div>
</div>
@endsection
