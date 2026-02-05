<?php $__env->startSection('title', 'Medecins'); ?>

<?php $__env->startSection('main_content'); ?>
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Medecins</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Accueil</a></li>
                    <li class="breadcrumb-item active">Medecins</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <?php if(session('status')): ?>
        <div class="alert alert-success"><?php echo e(session('status')); ?></div>
    <?php endif; ?>
    <div class="card">
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-1">Profils medecins</h5>
                    <span>Gerer la verification et les details du profil.</span>
                </div>
                <a class="btn btn-primary" href="<?php echo e(route('admin.doctors.create')); ?>">Nouveau medecin</a>
            </div>
        </div>
        <div class="card-body">
            <form class="row g-3 mb-3" method="get">
                <div class="col-md-6">
                    <select class="form-select" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="pending" <?php if(request('status') === 'pending'): echo 'selected'; endif; ?>>En attente</option>
                        <option value="active" <?php if(request('status') === 'active'): echo 'selected'; endif; ?>>Actif</option>
                        <option value="suspended" <?php if(request('status') === 'suspended'): echo 'selected'; endif; ?>>Suspendu</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">OK</button>
                </div>
            </form>
            <?php ($statusLabels = ['pending' => 'En attente', 'active' => 'Actif', 'suspended' => 'Suspendu']); ?>
            <?php ($verificationLabels = ['pending' => 'En attente', 'approved' => 'Approuve', 'rejected' => 'Refuse']); ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Specialite</th>
                            <th>Verification</th>
                            <th>Statut</th>
                            <th>Cree le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($doctor->name); ?></td>
                                <td><?php echo e($doctor->email); ?></td>
                                <td><?php echo e(optional($doctor->doctorProfile)->specialty ?? '-'); ?></td>
                                <td><?php echo e($verificationLabels[optional($doctor->doctorProfile)->verification_status] ?? (optional($doctor->doctorProfile)->verification_status ?? '-')); ?></td>
                                <td><?php echo e($statusLabels[$doctor->status] ?? $doctor->status); ?></td>
                                <td><?php echo e($doctor->created_at->format('Y-m-d')); ?></td>
                                <td>
                                    <a class="btn btn-sm btn-primary" href="<?php echo e(route('admin.doctors.show', $doctor)); ?>">Voir</a>
                                    <a class="btn btn-sm btn-outline-primary" href="<?php echo e(route('admin.doctors.edit', $doctor)); ?>">Modifier</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7">Aucun medecin trouve.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php echo e($doctors->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ORACLE\wadnou\resources\views/admin/doctors/index.blade.php ENDPATH**/ ?>