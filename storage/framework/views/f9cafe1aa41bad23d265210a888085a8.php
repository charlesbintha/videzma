 <!-- Page Sidebar Start-->
 <div class="sidebar-wrapper" data-sidebar-layout="stroke-svg">
     <div>
        <div class="logo-wrapper"><a href="<?php echo e(route('admin.dashboard')); ?>"><img class="img-fluid for-light"
                    src="<?php echo e(asset('assets/images/logo/wadnou2.png')); ?>" alt="Wadnou"><img class="img-fluid for-dark"
                    src="<?php echo e(asset('assets/images/logo/wadnou2.png')); ?>" alt="Wadnou"></a>
             <div class="back-btn"><i class="fa-solid fa-angle-left"></i></div>
             <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid"> </i></div>
         </div>
        <div class="logo-icon-wrapper"><a href="<?php echo e(route('admin.dashboard')); ?>"><img class="img-fluid"
                    src="<?php echo e(asset('assets/images/logo/wadnou2.png')); ?>" alt="Wadnou"></a></div>
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
                             <h6>Administration</h6>
                         </div>
                     </li>
                     <li class="sidebar-list">
                         <a class="sidebar-link sidebar-title link-nav" href="<?php echo e(route('admin.dashboard')); ?>">
                             <svg class="stroke-icon">
                                 <use href="<?php echo e(asset('assets/svg/icon-sprite.svg#stroke-home')); ?>"></use>
                             </svg>
                             <svg class="fill-icon">
                                 <use href="<?php echo e(asset('assets/svg/icon-sprite.svg#fill-home')); ?>"></use>
                             </svg><span>Tableau de bord</span>
                         </a>
                     </li>
                     <li class="sidebar-list">
                         <a class="sidebar-link sidebar-title link-nav" href="<?php echo e(route('admin.users.index')); ?>">
                             <svg class="stroke-icon">
                                 <use href="<?php echo e(asset('assets/svg/icon-sprite.svg#stroke-user')); ?>"></use>
                             </svg>
                             <svg class="fill-icon">
                                 <use href="<?php echo e(asset('assets/svg/icon-sprite.svg#fill-user')); ?>"></use>
                             </svg><span>Utilisateurs</span>
                         </a>
                     </li>
                     <li class="sidebar-list">
                         <a class="sidebar-link sidebar-title link-nav" href="<?php echo e(route('admin.doctors.index')); ?>">
                             <svg class="stroke-icon">
                                 <use href="<?php echo e(asset('assets/svg/icon-sprite.svg#stroke-contact')); ?>"></use>
                             </svg>
                             <svg class="fill-icon">
                                 <use href="<?php echo e(asset('assets/svg/icon-sprite.svg#fill-contact')); ?>"></use>
                             </svg><span>Medecins</span>
                         </a>
                     </li>
                     <li class="sidebar-list">
                         <a class="sidebar-link sidebar-title link-nav" href="<?php echo e(route('admin.doctor-documents.index')); ?>">
                             <svg class="stroke-icon">
                                 <use href="<?php echo e(asset('assets/svg/icon-sprite.svg#stroke-file')); ?>"></use>
                             </svg>
                             <svg class="fill-icon">
                                 <use href="<?php echo e(asset('assets/svg/icon-sprite.svg#fill-file')); ?>"></use>
                             </svg><span>Documents medecins</span>
                         </a>
                     </li>
                     <li class="sidebar-list">
                         <a class="sidebar-link sidebar-title link-nav" href="<?php echo e(route('admin.consultations.index')); ?>">
                             <svg class="stroke-icon">
                                 <use href="<?php echo e(asset('assets/svg/icon-sprite.svg#stroke-calendar')); ?>"></use>
                             </svg>
                             <svg class="fill-icon">
                                 <use href="<?php echo e(asset('assets/svg/icon-sprite.svg#fill-calender')); ?>"></use>
                             </svg><span>Consultations</span>
                         </a>
                     </li>
                     <li class="sidebar-list">
                         <a class="sidebar-link sidebar-title link-nav" href="<?php echo e(route('admin.appointments.index')); ?>">
                             <svg class="stroke-icon">
                                 <use href="<?php echo e(asset('assets/svg/icon-sprite.svg#stroke-task')); ?>"></use>
                             </svg>
                             <svg class="fill-icon">
                                 <use href="<?php echo e(asset('assets/svg/icon-sprite.svg#fill-task')); ?>"></use>
                             </svg><span>Rendez-vous</span>
                         </a>
                     </li>
                     <li class="sidebar-list">
                         <a class="sidebar-link sidebar-title link-nav" href="<?php echo e(route('admin.sla.index')); ?>">
                             <svg class="stroke-icon">
                                 <use href="<?php echo e(asset('assets/svg/icon-sprite.svg#stroke-report')); ?>"></use>
                             </svg>
                             <svg class="fill-icon">
                                 <use href="<?php echo e(asset('assets/svg/icon-sprite.svg#fill-reports')); ?>"></use>
                             </svg><span>SLA</span>
                         </a>
                     </li>
                     <li class="sidebar-list">
                         <a class="sidebar-link sidebar-title link-nav" href="<?php echo e(route('admin.locations.index')); ?>">
                             <svg class="stroke-icon">
                                 <use href="<?php echo e(asset('assets/svg/icon-sprite.svg#stroke-maps')); ?>"></use>
                             </svg>
                             <svg class="fill-icon">
                                 <use href="<?php echo e(asset('assets/svg/icon-sprite.svg#fill-maps')); ?>"></use>
                             </svg><span>Localisation</span>
                         </a>
                     </li>
                     <li class="sidebar-list">
                         <a class="sidebar-link sidebar-title link-nav" href="<?php echo e(route('admin.notifications.index')); ?>">
                             <svg class="stroke-icon">
                                 <use href="<?php echo e(asset('assets/svg/icon-sprite.svg#stroke-email')); ?>"></use>
                             </svg>
                             <svg class="fill-icon">
                                 <use href="<?php echo e(asset('assets/svg/icon-sprite.svg#fill-email')); ?>"></use>
                             </svg><span>Notifications</span>
                         </a>
                     </li>
                    
                 </ul>
             </div>
             <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
         </nav>
     </div>
 </div>
 <!-- Page Sidebar Ends-->
<?php /**PATH C:\ORACLE\wadnou\resources\views/layouts/sidebar.blade.php ENDPATH**/ ?>