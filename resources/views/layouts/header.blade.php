<!-- Page Header Start-->
<div class="page-header">
    <div class="header-wrapper row m-0">
        <form class="form-inline search-full col" action="#" method="get">
            <div class="form-group w-100">
                <div class="Typeahead Typeahead--twitterUsers">
                    <div class="u-posRelative">
                        <input class="demo-input Typeahead-input form-control-plaintext w-100" type="text"
                            placeholder="Rechercher" name="q" title="" autofocus>
                        <div class="spinner-border Typeahead-spinner" role="status"><span
                                class="sr-only">Chargement...</span></div><i class="close-search" data-feather="x"></i>
                    </div>
                    <div class="Typeahead-menu"></div>
                </div>
            </div>
        </form>
        <div class="header-logo-wrapper col-auto p-0">
            <div class="logo-wrapper"><a href="{{ route('admin.dashboard') }}"><img class="img-fluid"
                        src="{{ asset('assets/images/logo/wadnou2.png') }}" alt="Wadnou"></a></div>
            <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="align-center"></i>
            </div>
        </div>
        <div class="nav-right col-xxl-7 col-xl-6 col-md-7 col-8 pull-right right-header p-0 ms-auto">
            <ul class="nav-menus">
                <li>
                    <span class="header-search">
                        <svg>
                            <use href="{{ asset('assets/svg/icon-sprite.svg#search') }}"></use>
                        </svg>
                    </span>
                </li>
                <li class="profile-nav onhover-dropdown pe-0 py-0">
                    @php($currentUser = auth()->user())
                    @php($roleLabels = ['admin' => 'Admin', 'doctor' => 'Medecin', 'patient' => 'Patient'])
                    <div class="d-flex profile-media"><img class="b-r-10"
                            src="{{ asset('assets/images/dashboard/profile.png') }}" alt="">
                        <div class="flex-grow-1"><span>{{ $currentUser?->name ?? 'Invite' }}</span>
                            <p class="mb-0">{{ $currentUser?->role ? ($roleLabels[$currentUser->role] ?? ucfirst($currentUser->role)) : 'Invite' }}
                                <i class="middle fa-solid fa-angle-down"></i></p>
                        </div>
                    </div>
                    <ul class="profile-dropdown onhover-show-div">
                        <li><a href="javascript:void(0)"><i data-feather="user"></i><span>Compte </span></a>
                        </li>
                        <li><a href="javascript:void(0)"><i data-feather="settings"></i><span>Parametres</span></a></li>
                        @auth
                            <li>
                                <form method="post" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="btn btn-link p-0" type="submit">
                                        <i data-feather="log-out"></i><span>Deconnexion</span>
                                    </button>
                                </form>
                            </li>
                        @else
                            <li><a href="{{ route('login') }}"><i data-feather="log-in"> </i><span>Connexion</span></a>
                            </li>
                        @endauth
                    </ul>
                </li>
            </ul>
        </div>
        <script class="result-template" type="text/x-handlebars-template">
            <div class="ProfileCard u-cf">
            <div class="ProfileCard-avatar"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-airplay m-0"><path d="M5 17H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1"></path><polygon points="12 15 17 21 7 21 12 15"></polygon></svg></div>
            <div class="ProfileCard-details">
            <div class="ProfileCard-realName">nom</div>
            </div>
            </div>
          </script>
        <script class="empty-template" type="text/x-handlebars-template"><div class="EmptyMessage">Aucun resultat.</div></script>
    </div>
</div>
<!-- Page Header Ends-->
