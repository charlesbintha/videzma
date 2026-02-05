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
                <div class="card-body">
                    <h6 class="mb-1">Utilisateurs</h6>
                    <h3 class="mb-0"><?php echo e($metrics['users_total']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-1">Patients</h6>
                    <h3 class="mb-0"><?php echo e($metrics['patients_total']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-1">Medecins</h6>
                    <h3 class="mb-0"><?php echo e($metrics['doctors_total']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-1">Documents medecins en attente</h6>
                    <h3 class="mb-0"><?php echo e($metrics['doctor_docs_pending']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-1">Consultations en attente</h6>
                    <h3 class="mb-0"><?php echo e($metrics['consultations_pending']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-1">Rendez-vous aujourd'hui</h6>
                    <h3 class="mb-0"><?php echo e($metrics['appointments_today']); ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ORACLE\wadnou\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>