<!-- footer start-->
@switch(Route::currentRouteName())

@case('footer_dark')
    <footer class="footer footer-dark">
    @break

@case('footer_fixed')
    <footer class="footer footer-fix">
    @break

@default
     <footer class="footer">
@endswitch
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 footer-copyright text-center">
                <p class="mb-0">Droits <span class="year-update"> </span> Administration Wadnou</p>
            </div>
        </div>
    </div>
</footer>
