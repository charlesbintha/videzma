<?php $__env->startSection('title', 'Documents vidangeurs'); ?>

<?php $__env->startSection('main_content'); ?>
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Documents vidangeurs</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Accueil</a></li>
                    <li class="breadcrumb-item active">Documents</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>Liste des documents</h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3 mb-4">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="pending" <?php if(request('status') === 'pending'): echo 'selected'; endif; ?>>En attente</option>
                        <option value="approved" <?php if(request('status') === 'approved'): echo 'selected'; endif; ?>>Approuve</option>
                        <option value="rejected" <?php if(request('status') === 'rejected'): echo 'selected'; endif; ?>>Rejete</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">Tous les types</option>
                        <option value="license" <?php if(request('type') === 'license'): echo 'selected'; endif; ?>>Permis</option>
                        <option value="vehicle_registration" <?php if(request('type') === 'vehicle_registration'): echo 'selected'; endif; ?>>Carte grise</option>
                        <option value="insurance" <?php if(request('type') === 'insurance'): echo 'selected'; endif; ?>>Assurance</option>
                        <option value="certificate" <?php if(request('type') === 'certificate'): echo 'selected'; endif; ?>>Certificat</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filtrer</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Vidangeur</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Date soumission</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($document->driver->name ?? '-'); ?></td>
                                <td><?php echo e(ucfirst(str_replace('_', ' ', $document->type))); ?></td>
                                <td>
                                    <?php if($document->status === 'approved'): ?>
                                        <span class="badge bg-success">Approuve</span>
                                    <?php elseif($document->status === 'rejected'): ?>
                                        <span class="badge bg-danger">Rejete</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">En attente</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($document->created_at->format('d/m/Y H:i')); ?></td>
                                <td>
                                    <a href="<?php echo e(route('admin.driver-documents.show', $document)); ?>" class="btn btn-sm btn-info">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="<?php echo e(route('admin.driver-documents.download', $document)); ?>" class="btn btn-sm btn-secondary">
                                        <i class="fa fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center">Aucun document trouve.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php echo e($documents->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u528935801/domains/emmaluxury.store/public_html/videzma/resources/views/admin/driver-documents/index.blade.php ENDPATH**/ ?>