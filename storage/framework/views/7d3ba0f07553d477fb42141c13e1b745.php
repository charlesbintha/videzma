<?php $__env->startSection('title', 'Consultations'); ?>

<?php $__env->startSection('main_content'); ?>
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Consultations</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Accueil</a></li>
                    <li class="breadcrumb-item active">Consultations</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>Demandes de consultation</h5>
            <span>Suivre l'assignation, le statut, et les delais SLA.</span>
        </div>
        <div class="card-body">
            <form class="row g-3 mb-3" method="get">
                <div class="col-md-6">
                    <select class="form-select" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="pending" <?php if(request('status') === 'pending'): echo 'selected'; endif; ?>>En attente</option>
                        <option value="assigned" <?php if(request('status') === 'assigned'): echo 'selected'; endif; ?>>Assignee</option>
                        <option value="accepted" <?php if(request('status') === 'accepted'): echo 'selected'; endif; ?>>Acceptee</option>
                        <option value="rejected" <?php if(request('status') === 'rejected'): echo 'selected'; endif; ?>>Refusee</option>
                        <option value="canceled" <?php if(request('status') === 'canceled'): echo 'selected'; endif; ?>>Annulee</option>
                        <option value="closed" <?php if(request('status') === 'closed'): echo 'selected'; endif; ?>>Cloturee</option>
                        <option value="expired" <?php if(request('status') === 'expired'): echo 'selected'; endif; ?>>Expiree</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">OK</button>
                </div>
            </form>
            <?php ($statusLabels = ['pending' => 'En attente', 'assigned' => 'Assignee', 'accepted' => 'Acceptee', 'rejected' => 'Refusee', 'canceled' => 'Annulee', 'closed' => 'Cloturee', 'expired' => 'Expiree']); ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient</th>
                            <th>Medecin</th>
                            <th>Statut</th>
                            <th>Demande le</th>
                            <th>Echeance SLA</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $consultations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $consultation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>#<?php echo e($consultation->id); ?></td>
                                <td><?php echo e(optional($consultation->patient)->name ?? '-'); ?></td>
                                <td><?php echo e(optional($consultation->doctor)->name ?? '-'); ?></td>
                                <td><?php echo e($statusLabels[$consultation->status] ?? $consultation->status); ?></td>
                                <td><?php echo e($consultation->requested_at->format('Y-m-d H:i')); ?></td>
                                <td><?php echo e($consultation->sla_due_at ? $consultation->sla_due_at->format('Y-m-d H:i') : '-'); ?></td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary" href="<?php echo e(route('admin.consultations.show', $consultation)); ?>">Voir</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7">Aucune demande trouvee.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php echo e($consultations->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ORACLE\wadnou\resources\views/admin/consultations/index.blade.php ENDPATH**/ ?>