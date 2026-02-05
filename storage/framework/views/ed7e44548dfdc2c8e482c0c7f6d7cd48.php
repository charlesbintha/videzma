<?php $__env->startSection('title', 'Details du medecin'); ?>

<?php $__env->startSection('main_content'); ?>
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Details du medecin</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.doctors.index')); ?>">Medecins</a></li>
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

    <div class="row">
        <div class="col-xl-6">
            <?php ($statusLabels = ['pending' => 'En attente', 'active' => 'Actif', 'suspended' => 'Suspendu']); ?>
            <?php ($verificationLabels = ['pending' => 'En attente', 'approved' => 'Approuve', 'rejected' => 'Refuse']); ?>
            <div class="card">
                <div class="card-header">
                    <h5>Profil</h5>
                    <div class="card-header-right">
                        <a class="btn btn-sm btn-primary" href="<?php echo e(route('admin.doctors.edit', $doctor)); ?>">Modifier</a>
                        <form class="d-inline" method="post" action="<?php echo e(route('admin.doctors.destroy', $doctor)); ?>">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('delete'); ?>
                            <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Nom</dt>
                        <dd class="col-sm-8"><?php echo e($doctor->name); ?></dd>
                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8"><?php echo e($doctor->email); ?></dd>
                        <dt class="col-sm-4">Telephone</dt>
                        <dd class="col-sm-8"><?php echo e($doctor->phone ?? '-'); ?></dd>
                        <dt class="col-sm-4">Statut</dt>
                        <dd class="col-sm-8"><?php echo e($statusLabels[$doctor->status] ?? $doctor->status); ?></dd>
                        <dt class="col-sm-4">Langue</dt>
                        <dd class="col-sm-8"><?php echo e($doctor->locale); ?></dd>
                        <dt class="col-sm-4">Specialite</dt>
                        <dd class="col-sm-8"><?php echo e($doctor->doctorProfile?->specialty ?? '-'); ?></dd>
                        <dt class="col-sm-4">Licence</dt>
                        <dd class="col-sm-8"><?php echo e($doctor->doctorProfile?->license_number ?? '-'); ?></dd>
                        <dt class="col-sm-4">Verification</dt>
                        <dd class="col-sm-8"><?php echo e($verificationLabels[$doctor->doctorProfile?->verification_status] ?? ($doctor->doctorProfile?->verification_status ?? '-')); ?></dd>
                        <dt class="col-sm-4">Verifie le</dt>
                        <dd class="col-sm-8"><?php echo e($doctor->doctorProfile?->verified_at ? $doctor->doctorProfile->verified_at->format('Y-m-d') : '-'); ?></dd>
                    </dl>
                    <div class="mt-3">
                        <strong>Bio</strong>
                        <p class="mb-0"><?php echo e($doctor->doctorProfile?->bio ?? '-'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Documents</h5>
                </div>
                <div class="card-body">
                    <form class="row g-3 mb-4" method="post" action="<?php echo e(route('admin.doctors.documents.store', $doctor)); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="col-md-4">
                            <label class="form-label" for="type">Type</label>
                            <input class="form-control" id="type" name="type" value="<?php echo e(old('type')); ?>" placeholder="licence, diplome, certificat" required>
                            <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-danger"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label" for="document">Fichier</label>
                            <input class="form-control" id="document" name="document" type="file" required>
                            <?php $__errorArgs = ['document'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-danger"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="notes">Notes</label>
                            <input class="form-control" id="notes" name="notes" value="<?php echo e(old('notes')); ?>">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-outline-primary" type="submit">Televerser</button>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Statut</th>
                                    <th>Fichier</th>
                                    <th>Verifie par</th>
                                    <th>Verifie le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $doctor->doctorDocuments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($document->type); ?></td>
                                        <td><?php echo e($verificationLabels[$document->status] ?? $document->status); ?></td>
                                        <td>
                                            <?php if($document->file_path): ?>
                                                <a href="<?php echo e(route('admin.doctor-documents.download', $document)); ?>">Telecharger</a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($document->reviewer?->name ?? '-'); ?></td>
                                        <td><?php echo e($document->reviewed_at ? $document->reviewed_at->format('Y-m-d') : '-'); ?></td>
                                        <td>
                                            <a class="btn btn-sm btn-outline-primary" href="<?php echo e(route('admin.doctor-documents.show', $document)); ?>">Verifier</a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="6">Aucun document televerse.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ORACLE\wadnou\resources\views/admin/doctors/show.blade.php ENDPATH**/ ?>