@extends('layouts.master')

@section('title', 'Audit')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Audit</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Audit</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>Journal d'audit</h5>
            <span>Suivre les actions par utilisateur, entite et date.</span>
        </div>
        <div class="card-body">
            @php($actionLabels = ['status_change' => 'Changement de statut', 'document_review' => 'Verification document', 'sla_expired' => 'Expiration SLA'])
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Acteur</th>
                            <th>Action</th>
                            <th>Cible</th>
                            <th>Cree le</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($actions as $action)
                            <tr>
                                <td>{{ optional($action->actor)->name ?? 'Systeme' }}</td>
                                <td>{{ $actionLabels[$action->action] ?? $action->action }}</td>
                                <td>{{ $action->target_type }} #{{ $action->target_id }}</td>
                                <td>{{ $action->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">Aucune action d'audit.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $actions->links() }}
        </div>
    </div>
</div>
@endsection
