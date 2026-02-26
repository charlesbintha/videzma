@extends('layouts.master')

@section('title', 'Tableau de bord')

@section('main_content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Tableau de bord</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Tableau de bord</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-4 col-sm-6">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fa fa-users text-primary"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 text-muted">Utilisateurs</h6>
                        <h3 class="mb-0">{{ $metrics['users_total'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fa fa-user text-info"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 text-muted">Clients</h6>
                        <h3 class="mb-0">{{ $metrics['clients_total'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fa fa-truck text-success"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 text-muted">Vidangeurs</h6>
                        <h3 class="mb-0">{{ $metrics['drivers_total'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fa fa-file-alt text-warning"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 text-muted">Documents en attente</h6>
                        <h3 class="mb-0">{{ $metrics['driver_docs_pending'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fa fa-clock text-danger"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 text-muted">Demandes en attente</h6>
                        <h3 class="mb-0">{{ $metrics['requests_pending'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fa fa-calendar-check text-primary"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 text-muted">Interventions aujourd'hui</h6>
                        <h3 class="mb-0">{{ $metrics['interventions_today'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Séparateur financier --}}
    <div class="row">
        <div class="col-12">
            <h5 class="mt-3 mb-3 text-muted fw-semibold">
                <i class="fa fa-chart-line me-2"></i>Finances
            </h5>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-4 col-sm-6">
            <div class="card border-start border-success border-3">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fa fa-coins text-success"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 text-muted">Revenus aujourd'hui</h6>
                        <h3 class="mb-0 text-success">{{ number_format($metrics['revenue_today'], 0, ',', ' ') }} <small class="fs-6">FCFA</small></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card border-start border-primary border-3">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fa fa-chart-bar text-primary"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 text-muted">Revenus ce mois</h6>
                        <h3 class="mb-0 text-primary">{{ number_format($metrics['revenue_month'], 0, ',', ' ') }} <small class="fs-6">FCFA</small></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <a href="{{ route('admin.payments.index', ['payment_status' => 'pending']) }}" class="text-decoration-none">
                <div class="card border-start border-warning border-3">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fa fa-clock text-warning"></i>
                        </div>
                        <div>
                            <h6 class="mb-1 text-muted">Paiements en attente</h6>
                            <h3 class="mb-0 text-warning">{{ $metrics['payments_pending'] }}</h3>
                            @if($metrics['payments_pending_amount'] > 0)
                                <small class="text-muted">{{ number_format($metrics['payments_pending_amount'], 0, ',', ' ') }} FCFA</small>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
