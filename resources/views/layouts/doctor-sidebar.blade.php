 <!-- Page Sidebar Start-->
 <div class="sidebar-wrapper" data-sidebar-layout="stroke-svg">
     <div>
        <div class="logo-wrapper"><a href="{{ route('doctor.dashboard') }}"><img class="img-fluid for-light"
                    src="{{ asset('assets/images/logo/wadnou2.png') }}" alt="Wadnou"><img class="img-fluid for-dark"
                    src="{{ asset('assets/images/logo/wadnou2.png') }}" alt="Wadnou"></a>
             <div class="back-btn"><i class="fa-solid fa-angle-left"></i></div>
             <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid"> </i></div>
         </div>
        <div class="logo-icon-wrapper"><a href="{{ route('doctor.dashboard') }}"><img class="img-fluid"
                    src="{{ asset('assets/images/logo/wadnou2.png') }}" alt="Wadnou"></a></div>
         <nav class="sidebar-main">
             <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
             <div id="sidebar-menu">
                 <ul class="sidebar-links" id="simple-bar">
                     <li class="back-btn">
                         <div class="mobile-back text-end"><span>Retour</span><i class="fa-solid fa-angle-right ps-2"
                                 aria-hidden="true"></i></div>
                     </li>
                     <li class="sidebar-main-title">
                         <div>
                             <h6>Espace Medecin</h6>
                         </div>
                     </li>
                     <li class="sidebar-list">
                         <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}" href="{{ route('doctor.dashboard') }}">
                             <svg class="stroke-icon">
                                 <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                             </svg>
                             <svg class="fill-icon">
                                 <use href="{{ asset('assets/svg/icon-sprite.svg#fill-home') }}"></use>
                             </svg><span>Tableau de bord</span>
                         </a>
                     </li>
                     <li class="sidebar-list">
                         <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('doctor.consultations.pending') ? 'active' : '' }}" href="{{ route('doctor.consultations.pending') }}">
                             <svg class="stroke-icon">
                                 <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-alert') }}"></use>
                             </svg>
                             <svg class="fill-icon">
                                 <use href="{{ asset('assets/svg/icon-sprite.svg#fill-alert') }}"></use>
                             </svg><span>Demandes en attente</span>
                         </a>
                     </li>
                     <li class="sidebar-list">
                         <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('doctor.consultations.*') && !request()->routeIs('doctor.consultations.pending') ? 'active' : '' }}" href="{{ route('doctor.consultations.index') }}">
                             <svg class="stroke-icon">
                                 <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-calendar') }}"></use>
                             </svg>
                             <svg class="fill-icon">
                                 <use href="{{ asset('assets/svg/icon-sprite.svg#fill-calender') }}"></use>
                             </svg><span>Mes consultations</span>
                         </a>
                     </li>
                     <li class="sidebar-list">
                         <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('doctor.appointments.*') ? 'active' : '' }}" href="{{ route('doctor.appointments.index') }}">
                             <svg class="stroke-icon">
                                 <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-task') }}"></use>
                             </svg>
                             <svg class="fill-icon">
                                 <use href="{{ asset('assets/svg/icon-sprite.svg#fill-task') }}"></use>
                             </svg><span>Mes rendez-vous</span>
                         </a>
                     </li>
                     <li class="sidebar-list">
                         <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('doctor.availabilities.*') ? 'active' : '' }}" href="{{ route('doctor.availabilities.index') }}">
                             <svg class="stroke-icon">
                                 <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-time') }}"></use>
                             </svg>
                             <svg class="fill-icon">
                                 <use href="{{ asset('assets/svg/icon-sprite.svg#fill-time') }}"></use>
                             </svg><span>Disponibilites</span>
                         </a>
                     </li>
                     <li class="sidebar-list">
                         <a class="sidebar-link sidebar-title link-nav {{ request()->routeIs('doctor.profile.*') ? 'active' : '' }}" href="{{ route('doctor.profile.show') }}">
                             <svg class="stroke-icon">
                                 <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-user') }}"></use>
                             </svg>
                             <svg class="fill-icon">
                                 <use href="{{ asset('assets/svg/icon-sprite.svg#fill-user') }}"></use>
                             </svg><span>Mon profil</span>
                         </a>
                     </li>
                 </ul>
             </div>
             <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
         </nav>
     </div>
 </div>
 <!-- Page Sidebar Ends-->
