<!DOCTYPE html>
<html lang="en">
  <head>
    <?php echo $__env->make('layouts.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('layouts.css', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  </head>
  <body>
    <div class="container-fluid p-0">
      <?php echo $__env->yieldContent('content'); ?>
    </div>
    <?php echo $__env->make('layouts.scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  </body>
</html>
<?php /**PATH C:\ORACLE\wadnou\resources\views/layouts/auth.blade.php ENDPATH**/ ?>