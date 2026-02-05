<?php $__env->startSection('title', 'Details consultation'); ?>

<?php $__env->startSection('main_content'); ?>
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Details consultation</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.consultations.index')); ?>">Consultations</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php if(session('status')): ?>
        <div class="alert alert-success"><?php echo e(session('status')); ?></div>
    <?php endif; ?>

    <?php ($statusLabels = ['pending' => 'En attente', 'assigned' => 'Assignee', 'accepted' => 'Acceptee', 'rejected' => 'Refusee', 'canceled' => 'Annulee', 'closed' => 'Cloturee', 'expired' => 'Expiree']); ?>
    <?php ($appointmentStatusLabels = ['scheduled' => 'Planifie', 'in_progress' => 'En cours', 'completed' => 'Termine', 'canceled' => 'Annule']); ?>

    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Demande</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">ID</dt>
                        <dd class="col-sm-8">#<?php echo e($consultation->id); ?></dd>
                        <dt class="col-sm-4">Patient</dt>
                        <dd class="col-sm-8"><?php echo e($consultation->patient?->name ?? '-'); ?></dd>
                        <dt class="col-sm-4">Medecin</dt>
                        <dd class="col-sm-8"><?php echo e($consultation->doctor?->name ?? '-'); ?></dd>
                        <dt class="col-sm-4">Statut</dt>
                        <dd class="col-sm-8"><?php echo e($statusLabels[$consultation->status] ?? $consultation->status); ?></dd>
                        <dt class="col-sm-4">Motif</dt>
                        <dd class="col-sm-8"><?php echo e($consultation->reason ?? '-'); ?></dd>
                        <dt class="col-sm-4">Demande le</dt>
                        <dd class="col-sm-8"><?php echo e($consultation->requested_at->format('Y-m-d H:i')); ?></dd>
                        <dt class="col-sm-4">Echeance SLA</dt>
                        <dd class="col-sm-8"><?php echo e($consultation->sla_due_at ? $consultation->sla_due_at->format('Y-m-d H:i') : '-'); ?></dd>
                        <dt class="col-sm-4">Localisation</dt>
                        <dd class="col-sm-8">
                            <?php if($consultation->location): ?>
                                <?php echo e($consultation->location->latitude); ?>, <?php echo e($consultation->location->longitude); ?>

                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </dd>
                    </dl>
                    <div class="mt-3">
                        <strong>Notes</strong>
                        <p class="mb-0"><?php echo e($consultation->notes ?? '-'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Mise a jour</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?php echo e(route('admin.consultations.update', $consultation)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('patch'); ?>
                        <div class="mb-3">
                            <label class="form-label" for="doctor_id">Assigner un medecin</label>
                            <select class="form-select" id="doctor_id" name="doctor_id">
                                <option value="">Non assigne</option>
                                <?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($doctor->id); ?>" <?php if(old('doctor_id', $consultation->doctor_id) == $doctor->id): echo 'selected'; endif; ?>><?php echo e($doctor->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['doctor_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-danger"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="status">Statut</label>
                            <select class="form-select" id="status" name="status" required>
                                <?php $__currentLoopData = ['pending', 'assigned', 'accepted', 'rejected', 'canceled', 'closed', 'expired']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($status); ?>" <?php if(old('status', $consultation->status) === $status): echo 'selected'; endif; ?>>
                                        <?php echo e($statusLabels[$status] ?? $status); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-danger"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="sla_due_at">Echeance SLA</label>
                            <input class="form-control" id="sla_due_at" name="sla_due_at" type="datetime-local" value="<?php echo e(old('sla_due_at', $consultation->sla_due_at ? $consultation->sla_due_at->format('Y-m-d\TH:i') : '')); ?>">
                            <?php $__errorArgs = ['sla_due_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-danger"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="notes">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?php echo e(old('notes', $consultation->notes)); ?></textarea>
                            <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-danger"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <button class="btn btn-primary" type="submit">Enregistrer</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Rendez-vous</h5>
                </div>
                <div class="card-body">
                    <?php if($consultation->appointment): ?>
                        <p>ID rendez-vous : #<?php echo e($consultation->appointment->id); ?></p>
                        <p>Planifie : <?php echo e($consultation->appointment->scheduled_at->format('Y-m-d H:i')); ?></p>
                        <p>Statut : <?php echo e($appointmentStatusLabels[$consultation->appointment->status] ?? $consultation->appointment->status); ?></p>
                        <a class="btn btn-outline-primary" href="<?php echo e(route('admin.appointments.show', $consultation->appointment)); ?>">Voir rendez-vous</a>
                    <?php else: ?>
                        <form method="post" action="<?php echo e(route('admin.consultations.appointments.store', $consultation)); ?>">
                            <?php echo csrf_field(); ?>
                            <div class="mb-3">
                                <label class="form-label" for="scheduled_at">Planifier a</label>
                                <input class="form-control" id="scheduled_at" name="scheduled_at" type="datetime-local" value="<?php echo e(old('scheduled_at')); ?>" required>
                                <?php $__errorArgs = ['scheduled_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-danger"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <button class="btn btn-outline-primary" type="submit">Creer rendez-vous</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ORACLE\wadnou\resources\views/admin/consultations/show.blade.php ENDPATH**/ ?>