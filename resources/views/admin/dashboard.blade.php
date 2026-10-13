@extends('layouts.master')
@section('title', 'Tableau de bord')
@section('main_content')
<div class="vz-dashboard-title"><h1>Vue d’ensemble</h1><span>{{ now()->translatedFormat('d F Y') }}</span></div>
<div class="vz-overview-grid">
    <section class="card vz-activity-card">
        <div class="vz-panel-heading"><h2>Votre activité</h2><i data-feather="layers" aria-hidden="true"></i></div>
        <div class="vz-operation-card">
            <div class="vz-operation-brand"><span>videzma<span class="vz-title-dot">.</span></span><i data-feather="truck" aria-hidden="true"></i></div>
            <p>Demandes à prendre en charge</p>
            <strong>{{ $metrics['requests_pending'] }}</strong>
            <div class="vz-operation-actions"><a class="btn btn-primary" href="{{ route('admin.service-requests.index', ['status' => 'pending']) }}">Traiter les demandes</a><a class="vz-icon-button" href="{{ route('admin.service-requests.index') }}" aria-label="Toutes les demandes"><i data-feather="arrow-up-right" aria-hidden="true"></i></a></div>
        </div>
        <div class="vz-activity-facts">
            <a href="{{ route('admin.drivers.index') }}"><span>Vidangeurs actifs</span><strong>{{ $metrics['drivers_total'] }} <i data-feather="arrow-up-right" aria-hidden="true"></i></strong></a>
            <a href="{{ route('admin.payments.index', ['payment_status' => 'pending']) }}"><span>Paiements en attente</span><strong>{{ $metrics['payments_pending'] }} <i data-feather="arrow-up-right" aria-hidden="true"></i></strong></a>
        </div>
    </section>
        <section class="card vz-revenue">
            <div class="vz-panel-heading"><h2>Total des encaissements</h2><div class="vz-segmented" aria-label="Période affichée"><a href="{{ route('admin.dashboard', ['days' => 7]) }}" @class(['is-active' => $days === 7]) @if($days === 7) aria-current="true" @endif>7 jours</a><a href="{{ route('admin.dashboard', ['days' => 30]) }}" @class(['is-active' => $days === 30]) @if($days === 30) aria-current="true" @endif>30 jours</a></div></div>
            <div class="vz-revenue-total"><span class="vz-currency">FCFA</span> {{ number_format($series->sum('amount'), 0, ',', ' ') }} <span class="vz-soft-tag">{{ $days }} derniers jours</span></div>
            <p class="vz-subtle">Ce mois-ci : <strong>{{ number_format($metrics['revenue_month'], 0, ',', ' ') }} FCFA</strong> encaissés</p>
            @php
                $maximum = max(1, $series->max('amount'));
                $points = $series->map(fn ($point, $index) => (20 + $index * (660 / max(1, $series->count() - 1))).','. (155 - $point['amount'] / $maximum * 125))->implode(' ');
            @endphp
            <figure class="vz-chart">
                <svg viewBox="0 0 700 185" role="img" aria-labelledby="revenue-title revenue-desc">
                    <title id="revenue-title">Encaissements sur {{ $days }} jours</title><desc id="revenue-desc">{{ $series->map(fn ($point) => $point['label'].': '.$point['amount'].' FCFA')->implode('; ') }}</desc>
                    <defs><linearGradient id="revenue-fill" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#38bdf8" stop-opacity=".22"/><stop offset="100%" stop-color="#38bdf8" stop-opacity=".01"/></linearGradient></defs>
                    @foreach([30, 70, 110, 155] as $y)<line x1="20" y1="{{ $y }}" x2="680" y2="{{ $y }}" stroke="#e2e8f0" stroke-dasharray="4 5"/>@endforeach
                    <polygon points="20,155 {{ $points }} 680,155" fill="url(#revenue-fill)"/>
                    <polyline points="{{ $points }}" fill="none" stroke="#2563eb" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
                </svg>
                <div class="vz-chart-dates" aria-hidden="true">
                    @foreach($series as $index => $point)
                        @if($days === 7 || $index % 5 === 0 || $loop->last)<span>{{ $point['label'] }}</span>@endif
                    @endforeach
                </div>
                <figcaption><span class="vz-dot primary"></span> Encaissements confirmés @if($series->sum('amount') === 0) · Aucun encaissement sur cette période @endif</figcaption>
            </figure>
            @if($estimatedRevenue)<p class="vz-subtle">Certains anciens abonnements utilisent un montant reconstitué à vérifier.</p>@endif
        </section>
</div>
<div class="vz-dashboard-grid">
    <div class="vz-main-column">
        <section class="card vz-shortcuts"><div class="vz-panel-heading"><h2>Accès rapides</h2><span class="vz-subtle">L’essentiel à portée de main</span></div><div class="vz-shortcut-grid">
            @foreach([['inbox','Demandes','admin.service-requests.index'],['truck','Vidangeurs','admin.drivers.index'],['map-pin','Carte','admin.locations.index'],['credit-card','Paiements','admin.payments.index'],['repeat','Abonnements','admin.client-subscriptions.index'],['file-text','Documents','admin.driver-documents.index']] as [$icon,$label,$route])
                <a href="{{ route($route) }}"><i data-feather="{{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span></a>
            @endforeach
        </div></section>
        <section class="card vz-recent"><div class="vz-panel-heading"><div><h2>Dernières demandes</h2><p class="vz-subtle mb-0">Gardez le fil de votre activité.</p></div><a class="vz-text-link" href="{{ route('admin.service-requests.index') }}">Tout voir <i data-feather="arrow-up-right" aria-hidden="true"></i></a></div>
            <div class="table-responsive"><table class="table"><thead><tr><th>Client / Adresse</th><th>Statut</th><th>Montant</th><th><span class="visually-hidden">Consulter</span></th></tr></thead><tbody>
                @forelse($recentRequests as $service)
                    <tr><td><div class="vz-person"><span class="vz-avatar small">{{ mb_substr($service->client?->name ?? 'C', 0, 1) }}</span><div><strong>{{ $service->client?->name ?? 'Client' }}</strong><small>{{ Str::limit($service->address, 38) }}</small></div></div></td><td><span class="vz-status {{ $service->status }}">{{ \App\Models\ServiceRequest::STATUS_LABELS[$service->status] ?? $service->status }}</span></td><td class="text-nowrap fw-semibold">{{ $service->formatted_price }}</td><td><a class="vz-icon-button" href="{{ route('admin.service-requests.show', $service) }}" aria-label="Voir la demande {{ $service->id }}"><i data-feather="arrow-up-right" aria-hidden="true"></i></a></td></tr>
                @empty<tr><td colspan="4"><div class="vz-empty"><i data-feather="inbox" aria-hidden="true"></i><strong>Tout commence par une demande</strong><span>Les nouvelles demandes de vos clients apparaîtront ici.</span></div></td></tr>@endforelse
            </tbody></table></div>
        </section>
    </div>
    <div class="vz-side-column">
        <section class="card vz-distribution"><div class="vz-panel-heading"><h2>Suivi des demandes</h2><i data-feather="pie-chart" aria-hidden="true"></i></div>
            @php($completion = $statusCounts->sum() ? round(($statusCounts['completed'] ?? 0) / $statusCounts->sum() * 100) : 0)
            <div class="vz-distribution-total"><span class="vz-currency">{{ $statusCounts->sum() }}</span> demandes <span>au total</span></div>
            <div class="vz-gauge">
                <svg viewBox="0 0 240 130" role="img" aria-label="{{ $completion }} pour cent des demandes terminées"><path d="M 25 115 A 95 95 0 0 1 215 115" fill="none" stroke="#dbeafe" stroke-width="27"/><path d="M 25 115 A 95 95 0 0 1 215 115" fill="none" stroke="#2563eb" stroke-width="27" pathLength="100" stroke-dasharray="{{ $completion }} 100"/></svg>
                <div><strong>{{ $completion }}%</strong><span>Terminées</span></div>
            </div>
            <ul class="vz-status-list">@foreach(['pending' => 'En attente', 'assigned' => 'Assignées', 'accepted' => 'Acceptées', 'in_progress' => 'En cours', 'completed' => 'Terminées'] as $status => $label)<li><span><span class="vz-status-dot {{ $status }}"></span>{{ $label }}</span><strong>{{ $statusCounts[$status] ?? 0 }}</strong></li>@endforeach<li><span><span class="vz-status-dot canceled"></span>Annulées / refusées</span><strong>{{ ($statusCounts['canceled'] ?? 0) + ($statusCounts['cancelled'] ?? 0) + ($statusCounts['rejected'] ?? 0) }}</strong></li></ul>
        </section>
        <section class="card vz-schedule"><div class="vz-panel-heading"><h2>Aujourd’hui</h2><span class="vz-soft-tag">{{ now()->format('d/m') }}</span></div>
            @forelse($todayInterventions as $intervention)<a class="vz-schedule-item" href="{{ route('admin.interventions.show', $intervention) }}"><time>{{ $intervention->scheduled_at->format('H:i') }}</time><span><strong>{{ $intervention->serviceRequest?->client?->name ?? 'Intervention' }}</strong><small>{{ Str::limit($intervention->serviceRequest?->address, 28) }}</small></span><i data-feather="chevron-right" aria-hidden="true"></i></a>@empty<div class="vz-empty compact"><i data-feather="calendar" aria-hidden="true"></i><span>Aucune intervention prévue aujourd’hui.</span></div>@endforelse
            <a class="btn btn-outline-secondary w-100" href="{{ route('admin.interventions.index') }}">Voir les interventions <i data-feather="arrow-right" aria-hidden="true"></i></a>
        </section>
        <a class="vz-attention" href="{{ route('admin.driver-documents.index', ['status' => 'pending']) }}"><span class="vz-attention-icon"><i data-feather="shield" aria-hidden="true"></i></span><strong>{{ $metrics['driver_docs_pending'] }} document(s) à vérifier</strong><p>Validez les documents de vos vidangeurs pour compléter votre réseau.</p><span>Consulter les documents <i data-feather="arrow-up-right" aria-hidden="true"></i></span></a>
    </div>
</div>
@endsection
