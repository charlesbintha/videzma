<?php $__env->startSection('title', 'Rendez-vous'); ?>

<?php $__env->startSection('main_content'); ?>
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Rendez-vous</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Accueil</a></li>
                    <li class="breadcrumb-item active">Rendez-vous</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>Rendez-vous</h5>
            <span>Suivre la planification, le statut, et la cloture.</span>
        </div>
        <div class="card-body">
            <form class="row g-3 mb-3" method="get">
                <div class="col-md-6">
                    <select class="form-select" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="scheduled" <?php if(request('status') === 'scheduled'): echo 'selected'; endif; ?>>Planifie</option>
                        <option value="in_progress" <?php if(request('status') === 'in_progress'): echo 'selected'; endif; ?>>En cours</option>
                        <option value="completed" <?php if(request('status') === 'completed'): echo 'selected'; endif; ?>>Termine</option>
                        <option value="canceled" <?php if(request('status') === 'canceled'): echo 'selected'; endif; ?>>Annule</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">OK</button>
                </div>
            </form>
            <?php ($statusLabels = ['scheduled' => 'Planifie', 'in_progress' => 'En cours', 'completed' => 'Termine', 'canceled' => 'Annule']); ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Consultation</th>
                            <th>Patient</th>
                            <th>Medecin</th>
                            <th>Planifie</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $appointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $appointment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>#<?php echo e($appointment->id); ?></td>
                                <td>#<?php echo e($appointment->consultation_request_id); ?></td>
                                <td><?php echo e(optional(optional($appointment->consultationRequest)->patient)->name ?? '-'); ?></td>
                                <td><?php echo e(optional(optional($appointment->consultationRequest)->doctor)->name ?? '-'); ?></td>
                                <td><?php echo e($appointment->scheduled_at->format('Y-m-d H:i')); ?></td>
                                <td><?php echo e($statusLabels[$appointment->status] ?? $appointment->status); ?></td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary" href="<?php echo e(route('admin.appointments.show', $appointment)); ?>">Voir</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7">Aucun rendez-vous trouve.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php echo e($appointments->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ORACLE\wadnou\resources\views/admin/appointments/index.blade.php ENDPATH**/ ?>