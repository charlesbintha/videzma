(() => {
    if (window.feather) window.feather.replace({ 'stroke-width': 1.6 });
    const toggle = document.querySelector('.vz-menu-toggle');
    const sidebar = document.querySelector('.vz-sidebar');
    const mobile = window.matchMedia('(max-width: 991px)');
    const workspace = document.querySelector('.vz-workspace');
    const setOpen = (open) => {
        if (workspace) workspace.inert = open;
        if (sidebar) sidebar.inert = mobile.matches && !open;
        document.body.classList.toggle('vz-nav-open', open);
        toggle?.setAttribute('aria-expanded', String(open));
        toggle?.setAttribute('aria-label', open ? 'Fermer la navigation' : 'Ouvrir la navigation');
        if (open) sidebar?.querySelector('a')?.focus();
    };
    if (sidebar) sidebar.inert = mobile.matches;
    mobile.addEventListener('change', () => setOpen(false));
    toggle?.addEventListener('click', () => setOpen(!document.body.classList.contains('vz-nav-open')));
    document.querySelector('.vz-mobile-close')?.addEventListener('click', () => { setOpen(false); toggle?.focus(); });
    document.querySelector('.vz-backdrop')?.addEventListener('click', () => { setOpen(false); toggle?.focus(); });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Tab' && document.body.classList.contains('vz-nav-open')) {
            const links = sidebar.querySelectorAll('a, button');
            const first = links[0], last = links[links.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        }
        if (event.key === 'Escape' && document.body.classList.contains('vz-nav-open')) { setOpen(false); toggle?.focus(); }
    });
})();
