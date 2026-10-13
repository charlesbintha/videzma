<aside class="vz-sidebar" id="navigation" aria-label="Navigation principale">
    <a class="vz-brand" href="{{ route('admin.dashboard') }}">
        <span class="vz-brand-icon"><i data-feather="droplet" aria-hidden="true"></i></span>
        <span>videzma<span class="vz-brand-caption">ESPACE ADMINISTRATION</span></span>
    </a>
    @php
        $groups = [
            'Vue d’ensemble' => [ ['admin.dashboard', 'grid', 'Tableau de bord'], ],
            'Opérations' => [ ['admin.service-requests.index', 'inbox', 'Demandes de service'], ['admin.interventions.index', 'calendar', 'Interventions'], ['admin.locations.index', 'map-pin', 'Localisations'], ],
            'Réseau' => [ ['admin.users.index', 'users', 'Utilisateurs'], ['admin.drivers.index', 'truck', 'Vidangeurs'], ['admin.driver-documents.index', 'file-text', 'Documents vidangeurs'], ],
            'Finances' => [ ['admin.payments.index', 'credit-card', 'Paiements'], ['admin.client-subscriptions.index', 'repeat', 'Abonnements clients'], ['admin.subscription-plans.index', 'package', 'Forfaits'], ],
        ];
    @endphp
    <button type="button" class="vz-icon-button vz-mobile-close" aria-label="Fermer la navigation"><i data-feather="x" aria-hidden="true"></i></button>
    <nav>
        @foreach($groups as $title => $links)
            <p class="vz-nav-heading">{{ $title }}</p>
            @foreach($links as [$route, $icon, $label])
                @php($active = $route === 'admin.dashboard' ? request()->routeIs($route) : request()->routeIs(str_replace('.index', '.*', $route)))
                <a class="vz-nav-link {{ $active ? 'is-active' : '' }}" href="{{ route($route) }}" @if($active) aria-current="page" @endif>
                    <i data-feather="{{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span>
                </a>
            @endforeach
        @endforeach
    </nav>
    <div class="vz-sidebar-bottom">
        <a class="vz-nav-link {{ request()->routeIs('admin.notifications.*') ? 'is-active' : '' }}" href="{{ route('admin.notifications.index') }}"><i data-feather="bell" aria-hidden="true"></i>Notifications</a>
        <form method="post" action="{{ route('logout') }}">@csrf<button class="vz-nav-link" type="submit"><i data-feather="log-out" aria-hidden="true"></i>Déconnexion</button></form>
        <div class="vz-sidebar-note"><span class="vz-dot"></span> Votre espace de pilotage</div>
    </div>
</aside>
<button class="vz-backdrop" aria-label="Fermer la navigation" tabindex="-1"></button>
