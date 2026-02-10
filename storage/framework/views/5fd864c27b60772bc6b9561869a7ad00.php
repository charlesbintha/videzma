<?php $__env->startSection('title', 'Tableau de bord'); ?>

<?php $__env->startSection('main_content'); ?>
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Tableau de bord</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Accueil</a></li>
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
                        <h3 class="mb-0"><?php echo e($metrics['users_total']); ?></h3>
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
                        <h3 class="mb-0"><?php echo e($metrics['clients_total']); ?></h3>
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
                        <h3 class="mb-0"><?php echo e($metrics['drivers_total']); ?></h3>
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
                        <h3 class="mb-0"><?php echo e($metrics['driver_docs_pending']); ?></h3>
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
                        <h3 class="mb-0"><?php echo e($metrics['requests_pending']); ?></h3>
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
                        <h3 class="mb-0"><?php echo e($metrics['interventions_today']); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u528935801/domains/emmaluxury.store/public_html/videzma/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>