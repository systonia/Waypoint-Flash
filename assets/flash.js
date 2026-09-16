// waypoint-flash: dismiss on click, auto-hide, focus an error after a swap.
// Everything here is an enhancement: without it the dismiss form posts to the
// server and messages simply stay until the next page.
(() => {
    const arm = () => {
        for (const el of document.querySelectorAll('.wp-flash[data-timeout]:not([data-armed])')) {
            el.setAttribute('data-armed', '1');
            const ms = Number(el.getAttribute('data-timeout'));
            if (ms > 0) setTimeout(() => el.remove(), ms);
        }
        const error = document.querySelector('.wp-flashes [role="alert"]');
        if (error) {
            error.tabIndex = -1;
            error.focus();
        }
    };

    Waypoint.directive('wp-dismiss', 'click', (el, event) => {
        event.preventDefault();
        el.closest('.wp-flash')?.remove();
    });

    Waypoint.onNavigated(arm);
    arm();
})();
