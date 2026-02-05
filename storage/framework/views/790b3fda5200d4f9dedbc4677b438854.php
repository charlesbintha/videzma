<?php $__env->startSection('title', 'Mes consultations'); ?>

<?php $__env->startSection('main_content'); ?>
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Mes consultations</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('doctor.dashboard')); ?>">Accueil</a></li>
                    <li class="breadcrumb-item active">Consultations</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>Liste des consultations</h5>
            <form method="GET" class="row g-3 mt-2">
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Tous les statuts</option>
                        <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>En attente</option>
                        <option value="assigned" <?php echo e(request('status') === 'assigned' ? 'selected' : ''); ?>>Assigne</option>
                        <option value="accepted" <?php echo e(request('status') === 'accepted' ? 'selected' : ''); ?>>Accepte</option>
                        <option value="rejected" <?php echo e(request('status') === 'rejected' ? 'selected' : ''); ?>>Rejete</option>
                        <option value="closed" <?php echo e(request('status') === 'closed' ? 'selected' : ''); ?>>Cloture</option>
                        <option value="expired" <?php echo e(request('status') === 'expired' ? 'selected' : ''); ?>>Expire</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
                    <a href="<?php echo e(route('doctor.consultations.index')); ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
        <div class="card-body">
            <?php if(session('status')): ?>
                <div class="alert alert-success"><?php echo e(session('status')); ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Motif</th>
                            <th>Statut</th>
                            <th>Demande le</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $consultations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $consultation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($consultation->id); ?></td>
                                <td><?php echo e($consultation->patient?->name ?? '-'); ?></td>
                                <td><?php echo e(Str::limit($consultation->reason, 40)); ?></td>
                                <td>
                                    <?php switch($consultation->status):
                                        case ('pending'): ?>
                                            <span class="badge bg-warning">En attente</span>
                                            <?php break; ?>
                                        <?php case ('assigned'): ?>
                                            <span class="badge bg-info">Assigne</span>
                                            <?php break; ?>
                                        <?php case ('accepted'): ?>
                                            <span class="badge bg-success">Accepte</span>
                                            <?php break; ?>
                                        <?php case ('rejected'): ?>
                                            <span class="badge bg-danger">Rejete</span>
                                            <?php break; ?>
                                        <?php case ('closed'): ?>
                                            <span class="badge bg-secondary">Cloture</span>
                                            <?php break; ?>
                                        <?php case ('expired'): ?>
                                            <span class="badge bg-dark">Expire</span>
                                            <?php break; ?>
                                        <?php default: ?>
                                            <span class="badge bg-light text-dark"><?php echo e($consultation->status); ?></span>
                                    <?php endswitch; ?>
                                </td>
                                <td><?php echo e($consultation->requested_at?->format('d/m/Y H:i') ?? '-'); ?></td>
                                <td>
                                    <a href="<?php echo e(route('doctor.consultations.show', $consultation)); ?>" class="btn btn-sm btn-primary">Voir</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Aucune consultation.</td>
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

<?php echo $__env->make('layouts.doctor', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ORACLE\wadnou\resources\views/doctor/consultations/index.blade.php ENDPATH**/ ?>