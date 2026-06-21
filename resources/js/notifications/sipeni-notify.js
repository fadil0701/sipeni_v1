/**
 * SIPENI — notifikasi toast & utilitas flash (menggunakan stack #sipeni-toast-stack di layout).
 * Konfirmasi modal global: window.Sipeni.confirm (didefinisikan di app-layout.js).
 */
function onReady(fn) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fn, { once: true });
        return;
    }
    fn();
}

function initSipeniToastApi() {
    window.Sipeni = window.Sipeni || {};

    if (typeof window.Sipeni.toast === 'function') {
        return;
    }

    function ensureStack() {
        var stack = document.getElementById('sipeni-toast-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.id = 'sipeni-toast-stack';
            stack.className = 'sipeni-toast-stack';
            stack.setAttribute('aria-live', 'polite');
            document.body.appendChild(stack);
        }
        return stack;
    }

    function iconSvg(type) {
        if (type === 'success') {
            return '<svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        }
        if (type === 'error') {
            return '<svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
        }
        if (type === 'warning') {
            return '<svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
        }
        return '<svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    }

    window.Sipeni.toast = function (message, type, durationMs) {
        if (!message) return;
        type = type || 'info';
        var duration = typeof durationMs === 'number' ? durationMs : 4500;
        var stack = ensureStack();
        var card = document.createElement('div');
        card.className = 'sipeni-toast sipeni-toast--' + type;
        card.setAttribute('role', 'status');
        card.innerHTML = ''
            + '<div class="sipeni-toast__inner">'
            + '  <div class="sipeni-toast__icon">' + iconSvg(type) + '</div>'
            + '  <div class="sipeni-toast__body">'
            + '    <p class="sipeni-toast__message"></p>'
            + '    <div class="sipeni-toast__bar" aria-hidden="true"><span></span></div>'
            + '  </div>'
            + '  <button type="button" class="sipeni-toast__close" aria-label="Tutup">&times;</button>'
            + '</div>';
        card.querySelector('.sipeni-toast__message').textContent = message;
        var bar = card.querySelector('.sipeni-toast__bar span');
        if (bar) {
            bar.style.animationDuration = duration + 'ms';
        }
        function removeCard() {
            card.classList.add('sipeni-toast--leave');
            setTimeout(function () {
                if (card.parentNode) card.parentNode.removeChild(card);
            }, 280);
        }
        card.querySelector('.sipeni-toast__close').addEventListener('click', removeCard);
        stack.appendChild(card);
        var t = setTimeout(removeCard, duration);
        card.addEventListener('mouseenter', function () { clearTimeout(t); });
        card.addEventListener('mouseleave', function () { t = setTimeout(removeCard, 1200); });
    };
}

function initSipeniFlashDismiss() {
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-dismiss-flash]');
        if (!btn) return;
        var box = btn.closest('[role="status"], [role="alert"]');
        if (box && box.parentNode) {
            box.parentNode.removeChild(box);
        }
    });
}

function initSipeniFlashToastMirror() {
    var body = document.body;
    if (!body || body.dataset.sipeniFlashToast !== '1') return;
    if (typeof window.Sipeni === 'undefined' || typeof window.Sipeni.toast !== 'function') return;

    var el = document.getElementById('sipeni-flash-json');
    if (!el || !el.textContent) return;
    try {
        var payload = JSON.parse(el.textContent);
        if (payload && payload.type && payload.message) {
            window.Sipeni.toast(payload.message, payload.type, payload.durationMs);
        }
    } catch (err) {
        window.__gWarn && window.__gWarn('sipeni-flash-json parse error', err);
    }
}

onReady(function () {
    initSipeniToastApi();
    initSipeniFlashDismiss();
    initSipeniFlashToastMirror();
});
