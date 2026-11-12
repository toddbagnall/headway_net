(function () {
    'use strict';

    var yearEl = document.getElementById('year');
    if (yearEl) {
        yearEl.textContent = new Date().getFullYear();
    }

    /* Navigation background state */
    var navbar = document.getElementById('navbar');
    var ticking = false;

    function updateNavbar() {
        if (navbar) {
            navbar.classList.toggle('nav-scrolled', window.scrollY > 40);
        }
        ticking = false;
    }

    function onScroll() {
        if (!ticking) {
            ticking = true;
            window.requestAnimationFrame(updateNavbar);
        }
    }

    updateNavbar();
    window.addEventListener('scroll', onScroll, { passive: true });

    /* Mobile menu */
    var menuButton = document.getElementById('menu-btn');
    var mobileMenu = document.getElementById('mobile-menu');
    var iconOpen = document.getElementById('menu-icon-open');
    var iconClose = document.getElementById('menu-icon-close');

    function setMenu(open, returnFocus) {
        if (!menuButton || !mobileMenu) {
            return;
        }
        menuButton.setAttribute('aria-expanded', String(open));
        menuButton.setAttribute('aria-label', open ? menuButton.dataset.labelClose : menuButton.dataset.labelOpen);
        mobileMenu.hidden = !open;
        if (iconOpen) {
            iconOpen.hidden = open;
        }
        if (iconClose) {
            iconClose.hidden = !open;
        }

        if (open) {
            var firstLink = mobileMenu.querySelector('a');
            if (firstLink) {
                firstLink.focus();
            }
        } else if (returnFocus) {
            menuButton.focus();
        }
    }

    if (menuButton && mobileMenu) {
        menuButton.addEventListener('click', function () {
            setMenu(menuButton.getAttribute('aria-expanded') !== 'true', false);
        });

        mobileMenu.addEventListener('click', function (event) {
            if (event.target.closest('a')) {
                setMenu(false, false);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && menuButton.getAttribute('aria-expanded') === 'true') {
                setMenu(false, true);
            }
        });

        var desktop = window.matchMedia('(min-width: 1024px)');
        desktop.addEventListener('change', function (event) {
            if (event.matches) {
                setMenu(false, false);
            }
        });
    }

    /* Contact form */
    var form = document.getElementById('contact-form');
    var status = document.getElementById('form-status');
    var submitButton = form ? form.querySelector('button[type="submit"]') : null;

    if (form && status && submitButton) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            submitButton.disabled = true;
            status.style.color = '#cbd5e1';
            status.textContent = form.dataset.sending || 'Sending…';

            try {
                var response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { Accept: 'application/json' }
                });
                var result = await response.json().catch(function () {
                    return null;
                });

                if (!response.ok || !result || result.ok !== true) {
                    throw new Error('send_failed');
                }

                form.reset();
                status.style.color = '#fbc85a';
                status.textContent = form.dataset.success || 'Thank you.';
            } catch (error) {
                status.style.color = '#fca5a5';
                status.textContent = (form.dataset.error || 'We could not send your message.') + ' ';

                var phone = form.dataset.phone;
                if (phone) {
                    var link = document.createElement('a');
                    link.href = 'tel:' + phone;
                    link.className = 'font-semibold underline';
                    link.textContent = form.dataset.phoneLabel || phone;
                    status.appendChild(link);
                }

                if (form.dataset.errorAfter) {
                    status.appendChild(document.createTextNode(' ' + form.dataset.errorAfter));
                }
            } finally {
                submitButton.disabled = false;
            }
        });
    }
})();
