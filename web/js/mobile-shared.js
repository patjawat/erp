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
