<?php $__env->startSection('title', 'Demandes en attente'); ?>

<?php $__env->startSection('main_content'); ?>
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Demandes en attente</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('doctor.dashboard')); ?>">Accueil</a></li>
                    <li class="breadcrumb-item active">Demandes en attente</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>Demandes necessitant votre attention</h5>
        </div>
        <div class="card-body">
            <?php if(session('status')): ?>
                <div class="alert alert-success"><?php echo e(session('status')); ?></div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <p class="mb-0"><?php echo e($error); ?></p>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Telephone</th>
                            <th>Motif</th>
                            <th>Adresse</th>
                            <th>Echeance SLA</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $consultations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $consultation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="<?php echo e($consultation->sla_due_at && $consultation->sla_due_at->isPast() ? 'table-danger' : ''); ?>">
                                <td><?php echo e($consultation->id); ?></td>
                                <td><?php echo e($consultation->patient?->name ?? '-'); ?></td>
                                <td><?php echo e($consultation->patient?->phone ?? '-'); ?></td>
                                <td><?php echo e(Str::limit($consultation->reason, 30)); ?></td>
                                <td><?php echo e($consultation->location?->address ? Str::limit($consultation->location->address, 30) : '-'); ?></td>
                                <td>
                                    <?php if($consultation->sla_due_at): ?>
                                        <span class="<?php echo e($consultation->sla_due_at->isPast() ? 'text-danger fw-bold' : ($consultation->sla_due_at->diffInMinutes(now()) < 30 ? 'text-warning fw-bold' : '')); ?>">
                                            <?php echo e($consultation->sla_due_at->format('d/m H:i')); ?>

                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo e(route('doctor.consultations.show', $consultation)); ?>" class="btn btn-sm btn-primary">Traiter</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">Aucune demande en attente.</td>
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

<?php echo $__env->make('layouts.doctor', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ORACLE\wadnou\resources\views/doctor/consultations/pending.blade.php ENDPATH**/ ?>