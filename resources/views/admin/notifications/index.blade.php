@extends('layouts.master')

@section('title', 'Notifications')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Notifications</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Notifications</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>File d'envoi</h5>
            <span>Suivre l'envoi des notifications.</span>
        </div>
        <div class="card-body">
            <form class="row g-3 mb-3" method="get">
                <div class="col-md-6">
                    <select class="form-select" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="queued" @selected(request('status') === 'queued')>En attente</option>
                        <option value="sent" @selected(request('status') === 'sent')>Envoye</option>
                        <option value="failed" @selected(request('status') === 'failed')>Echec</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">OK</button>
                </div>
            </form>
            @php($statusLabels = ['queued' => 'En attente', 'sent' => 'Envoye', 'failed' => 'Echec'])
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Canal</th>
                            <th>Titre</th>
                            <th>Statut</th>
                            <th>Envoye</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($notifications as $notification)
                            <tr>
                                <td>{{ optional($notification->user)->name ?? '-' }}</td>
                                <td>{{ $notification->channel }}</td>
                                <td>{{ $notification->title }}</td>
                                <td>{{ $statusLabels[$notification->status] ?? $notification->status }}</td>
                                <td>{{ $notification->sent_at ? $notification->sent_at->format('Y-m-d H:i') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">Aucune notification trouvee.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection
