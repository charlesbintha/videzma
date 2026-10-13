<header class="vz-header">
    <div class="d-flex align-items-center gap-3">
        <button class="vz-icon-button vz-menu-toggle" aria-label="Ouvrir la navigation" aria-controls="navigation" aria-expanded="false"><i data-feather="menu" aria-hidden="true"></i></button>
        <div><span class="vz-eyebrow">Heureux de vous retrouver</span><div class="vz-greeting">Bonjour, {{ auth()->user()?->name ?? 'Administrateur' }} <span class="vz-admin-tag">Admin</span></div></div>
    </div>
    <div class="vz-header-actions">
        <form class="vz-search" action="{{ route('admin.service-requests.index') }}" method="get" role="search"><i data-feather="search" aria-hidden="true"></i><input type="search" name="search" aria-label="Rechercher une demande par client ou adresse" placeholder="Rechercher une demande…"><button type="submit" aria-label="Rechercher"><i data-feather="arrow-right" aria-hidden="true"></i></button></form>
        <a class="vz-icon-button" href="{{ route('admin.notifications.index') }}" aria-label="Notifications"><i data-feather="bell" aria-hidden="true"></i></a>
        <span class="vz-avatar" aria-label="{{ auth()->user()?->name }}">{{ mb_strtoupper(mb_substr(auth()->user()?->name ?? 'A', 0, 1)) }}</span>
    </div>
</header>
