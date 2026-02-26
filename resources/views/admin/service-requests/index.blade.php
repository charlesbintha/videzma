@extends('layouts.master')

@section('title', 'Demandes de service')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Demandes de service</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Demandes</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>Liste des demandes</h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3 mb-4">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Adresse ou client..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Tous les statuts</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="driver_id" class="form-select">
                        <option value="">Tous les vidangeurs</option>
                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}" @selected(request('driver_id') == $driver->id)>{{ $driver->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="payment_status" class="form-select">
                        <option value="">Tout paiement</option>
                        @foreach ($paymentStatuses as $val => $label)
                            <option value="{{ $val }}" @selected(request('payment_status') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filtrer</button>
                </div>
                @if(request()->hasAny(['search','status','driver_id','payment_status']))
                <div class="col-md-1">
                    <a href="{{ route('admin.service-requests.index') }}" class="btn btn-outline-secondary w-100" title="Réinitialiser">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
                @endif
            </form>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Client</th>
                            <th>Adresse</th>
                            <th>Vidangeur</th>
                            <th>Statut</th>
                            <th>Paiement</th>
                            <th>Prix</th>
                            <th>Date demande</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $request)
                            <tr>
                                <td>{{ $request->id }}</td>
                                <td>{{ $request->client->name ?? '-' }}</td>
                                <td>{{ Str::limit($request->address, 30) }}</td>
                                <td>{{ $request->driver->name ?? '-' }}</td>
                                <td>
                                    @switch($request->status)
                                        @case('pending')
                                            <span class="badge bg-warning">En attente</span>
                                            @break
                                        @case('assigned')
                                            <span class="badge bg-info">Assigne</span>
                                            @break
                                        @case('accepted')
                                            <span class="badge bg-primary">Accepte</span>
                                            @break
                                        @case('in_progress')
                                            <span class="badge bg-secondary">En cours</span>
                                            @break
                                        @case('completed')
                                            <span class="badge bg-success">Termine</span>
                                            @break
                                        @case('rejected')
                                        @case('cancelled')
                                            <span class="badge bg-danger">{{ ucfirst($request->status) }}</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $request->status }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    @if($request->payment_status === 'paid')
                                        <span class="badge bg-success">Payé</span>
                                    @elseif($request->payment_status === 'failed')
                                        <span class="badge bg-danger">Échoué</span>
                                    @else
                                        @if($request->payment_method === 'cash')
                                            <span class="badge bg-secondary">Espèces</span>
                                        @else
                                            <span class="badge bg-warning">En attente</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-nowrap">{{ number_format($request->price_amount ?? 0, 0, ',', ' ') }} F</td>
                                <td>{{ $request->requested_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.service-requests.show', $request) }}" class="btn btn-sm btn-info">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Aucune demande trouvee.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $requests->links() }}
        </div>
    </div>
</div>
@endsection
