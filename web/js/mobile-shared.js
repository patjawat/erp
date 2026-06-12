/*!
 * Mobile module shared runtime
 *
 *  - mobileConfirm(form, message): SweetAlert-based form-submit confirmation
 *    Falls back to native confirm() if Swal not loaded.
 *  - Telegram MiniApp auto-login: runs once per session via localStorage flag
 *  - Lucide aria-hidden post-process: marks decorative icons (paired with text) as aria-hidden="true"
 */
(function () {
    'use strict';

    // ---- mobileConfirm: SweetAlert-based form confirmation ------------------
    // Usage in views:
    //   form.addEventListener('submit', function (e) {
    //       if (!window.mobileConfirm(this, 'ยืนยันบันทึก?')) e.preventDefault();
    //   });
    window.mobileConfirm = function (form, message) {
        if (form.dataset.confirmed === '1') {
            form.dataset.confirmed = '';
            return true;
        }
        if (typeof Swal === 'undefined') {
            return window.confirm(message);
        }
        Swal.fire({
            title: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            reverseButtons: true,
        }).then(function (r) {
            if (r.isConfirmed) {
                form.dataset.confirmed = '1';
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }
        });
        return false;
    };

    // ---- Lucide icon aria-hidden post-process -------------------------------
    // Lucide replaces <i data-lucide="X"> with <svg class="lucide lucide-X">. After
    // that runs (from _footer.php), mark SVGs that sit next to visible text as
    // decorative so screen readers don't announce the icon name.
    function markDecorativeIcons() {
        var selector = 'button > svg.lucide, a > svg.lucide, h1 > svg.lucide, '
            + 'h2 > svg.lucide, h3 > svg.lucide, h4 > svg.lucide, h5 > svg.lucide, '
            + 'h6 > svg.lucide, span > svg.lucide, p > svg.lucide, label > svg.lucide';
        var nodes = document.querySelectorAll(selector);
        for (var i = 0; i < nodes.length; i++) {
            var svg = nodes[i];
            if (svg.hasAttribute('aria-hidden')) continue;
            var parent = svg.parentElement;
            if (!parent) continue;
            // If parent has any non-empty text content, the icon is decorative
            var text = (parent.textContent || '').trim();
            if (text.length > 0) {
                svg.setAttribute('aria-hidden', 'true');
                svg.setAttribute('focusable', 'false');
            }
        }
    }
    // Run after _footer.php's lucide.createIcons() — give it one frame.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            requestAnimationFrame(markDecorativeIcons);
        });
    } else {
        requestAnimationFrame(markDecorativeIcons);
    }
    window.mobileMarkDecorativeIcons = markDecorativeIcons;

    // ---- App shell offset --------------------------------------------------
    // Pages render <div class="app-shell"> at the top (fixed Hero + optional
    // Stats overlay). This observer measures its height and writes it to
    // --shell-h on the document root so a sibling <div class="app-scroll">
    // gets matching top padding (defined in _head.php). Each page can render
    // its own shell without duplicating the measurement code.
    function initAppShell() {
        var shell = document.querySelector('.app-shell');
        if (!shell) return;
        function sync() {
            var h = shell.offsetHeight;
            if (h > 0) document.documentElement.style.setProperty('--shell-h', h + 'px');
        }
        sync();
        if (typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(sync).observe(shell);
        } else {
            window.addEventListener('resize', sync);
            window.addEventListener('orientationchange', sync);
        }
        // Re-measure after icons settle (lucide swaps <i> for <svg>).
        if (document.readyState !== 'complete') {
            window.addEventListener('load', function () { requestAnimationFrame(sync); });
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAppShell);
    } else {
        initAppShell();
    }
    window.mobileInitAppShell = initAppShell;

    // ---- Navigation loading feedback ---------------------------------------
    // Problem: after a tap on a link there is a perceptible gap (200-800ms)
    // before the new page paints. Without visual feedback the app feels
    // frozen and users re-tap, which can double-submit forms or open the
    // wrong screen.
    //
    // Solution — two layers of feedback:
    //   1) Top progress bar (#mobile-nav-progress) — paints instantly via
    //      a GPU transform animation. Gives immediate tap acknowledgement.
    //   2) Full-screen overlay (#mobile-loading-overlay) — appears 250ms
    //      after the tap if the page hasn't started rendering. Signals
    //      "still working" for slower navigations.
    //
    // Both clear once the next page finishes loading or the bfcache
    // restores a previous page.

    var navLoaderTimer = null;

    function ensureNavProgress() {
        var bar = document.getElementById('mobile-nav-progress');
        if (bar) return bar;
        bar = document.createElement('div');
        bar.id = 'mobile-nav-progress';
        bar.setAttribute('aria-hidden', 'true');
        (document.body || document.documentElement).appendChild(bar);
        return bar;
    }

    function showNavProgress() {
        var bar = ensureNavProgress();
        bar.classList.remove('is-done');
        // Force reflow so the animation restarts even on rapid taps
        void bar.offsetWidth;
        bar.classList.add('is-loading');
    }
    function hideNavProgress() {
        var bar = document.getElementById('mobile-nav-progress');
        if (!bar) return;
        if (bar.classList.contains('is-loading')) {
            bar.classList.remove('is-loading');
            bar.classList.add('is-done');
        }
    }

    function getLoadingOverlay() {
        return document.getElementById('mobile-loading-overlay');
    }

    window.showMobileLoader = function (message) {
        var overlay = getLoadingOverlay();
        if (!overlay) return;
        overlay.classList.remove('mobile-loading-hidden');
        if (message) {
            var t = overlay.querySelector('.mobile-loading-text');
            if (t) t.textContent = message;
        }
    };
    window.hideMobileLoader = function () {
        clearTimeout(navLoaderTimer);
        navLoaderTimer = null;
        hideNavProgress();
        var overlay = getLoadingOverlay();
        if (overlay) overlay.classList.add('mobile-loading-hidden');
    };
    window.showMobileNavLoading = function (message) {
        showNavProgress();
        clearTimeout(navLoaderTimer);
        navLoaderTimer = setTimeout(function () {
            window.showMobileLoader(message);
        }, 250);
    };

    // Auto-hide loaders once the page is fully loaded.
    function hideOnReady() {
        var run = function () {
            requestAnimationFrame(function () {
                requestAnimationFrame(window.hideMobileLoader);
            });
        };
        if (document.readyState === 'complete') {
            run();
        } else {
            window.addEventListener('load', run);
        }
    }
    hideOnReady();

    // Back/forward bfcache restore: page is instantly visible, no work to do
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) window.hideMobileLoader();
    });

    // Internal-link click → show loading before browser starts navigation.
    // Filters out everything that shouldn't trigger nav feedback.
    document.addEventListener('click', function (e) {
        var link = e.target && e.target.closest ? e.target.closest('a[href]') : null;
        if (!link) return;
        if (e.defaultPrevented) return;
        // Modifier-click opens a new tab/window in the browser; let it pass
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        if (e.button !== 0) return;
        if (link.target && link.target !== '_self') return;
        if (link.hasAttribute('download')) return;
        // Bootstrap toggles open modals / offcanvas; they don't navigate
        if (link.dataset.bsToggle) return;
        if (link.dataset.bsDismiss) return;
        // Project-specific AJAX modal trigger
        if (link.classList.contains('open-modal')) return;
        // Opt-out attribute for any link that shouldn't show the loader
        if (link.dataset.noLoader === 'true') return;

        var href = link.getAttribute('href');
        if (!href || href === '#') return;
        if (href.charAt(0) === '#') return;
        if (/^(javascript:|mailto:|tel:|sms:|whatsapp:)/i.test(href)) return;

        try {
            var url = new URL(link.href, location.href);
            if (url.origin !== location.origin) return;
            // Same-page hash change: browser stays on this page
            if (url.pathname === location.pathname &&
                url.search === location.search &&
                url.hash !== location.hash) return;
        } catch (err) {
            return;
        }

        window.showMobileNavLoading();
    }, true);

    // Form submit → show loader for non-AJAX forms only. Forms using
    // handleFormSubmit (Yii pattern: id matches mobile-*-form) already
    // surface their own Swal-based loading state and should be skipped to
    // avoid duplicate UI.
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (e.defaultPrevented) return;
        if (form.dataset.ajax === 'true') return;
        if (form.dataset.noLoader === 'true') return;
        if (form.id && /^mobile-.+-form$/.test(form.id)) return;
        try {
            var action = form.getAttribute('action') || location.href;
            var url = new URL(action, location.href);
            if (url.origin !== location.origin) return;
        } catch (err) {
            return;
        }
        window.showMobileNavLoading('กำลังบันทึก...');
    }, true);

    // ---- Telegram MiniApp auto-login ---------------------------------------
    // Only fires inside a Telegram WebApp environment. Idempotent: localStorage
    // flag prevents re-firing across navigations within the same session.
    function telegramAutoLogin() {
        if (window.__tg_login_done) return;
        window.__tg_login_done = true;

        if (localStorage.getItem('tg_logged_in') === '1') return;

        var tg = window.Telegram && window.Telegram.WebApp;
        if (!tg) return;

        tg.ready();
        tg.expand();

        var user = tg.initDataUnsafe && tg.initDataUnsafe.user;
        if (!user || !user.id) return;

        if (typeof jQuery === 'undefined' || typeof yii === 'undefined') return;

        jQuery.ajax({
            url: '/mobile/auth/telegram-login',
            type: 'POST',
            dataType: 'json',
            data: {
                telegram_id: user.id,
                initData: tg.initData || '',
            },
            headers: {
                'X-CSRF-Token': yii.getCsrfToken(),
            },
            success: function (res) {
                if (res && res.success) {
                    localStorage.setItem('tg_logged_in', '1');
                    setTimeout(function () {
                        window.location.href = res.redirect || '/mobile/default/index';
                    }, 200);
                } else {
                    localStorage.removeItem('tg_logged_in');
                    setTimeout(function () {
                        window.location.href = '/mobile/auth/login';
                    }, 200);
                }
            },
        });
    }
    // Only run on the home page where Telegram MiniApp lands.
    if (document.body && document.body.dataset.mobilePage === 'home') {
        telegramAutoLogin();
    }
})();
