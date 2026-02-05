<!-- footer start-->
<?php switch(Route::currentRouteName()):

case ('footer_dark'): ?>
    <footer class="footer footer-dark">
    <?php break; ?>

<?php case ('footer_fixed'): ?>
    <footer class="footer footer-fix">
    <?php break; ?>

<?php default: ?>
     <footer class="footer">
<?php endswitch; ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 footer-copyright text-center">
                <p class="mb-0">Droits <span class="year-update"> </span> Administration Wadnou</p>
            </div>
        </div>
    </div>
</footer>
<?php /**PATH C:\ORACLE\wadnou\resources\views/layouts/footer.blade.php ENDPATH**/ ?>