function onReady(fn) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fn, { once: true });
        return;
    }
    fn();
}

function isAuthPage() {
    return !document.body || !document.body.hasAttribute('data-features');
}

function initLayoutGlobals() {
    window.__layoutEnabled = function (name) {
        if (isAuthPage()) return false;
        var raw = (document.body && document.body.dataset.features) || '';
        var parts = raw.split(/\s+/).filter(Boolean);
        if (!parts.length) return false;
        return parts.indexOf(name) !== -1;
    };
    window.__appDebug = false;
    window.__gLog = function () {};
    window.__gWarn = function () {
        if (window.__appDebug && typeof console !== 'undefined' && console.warn) {
            console.warn.apply(console, arguments);
        }
    };
    window.__tableMode = (document.body && document.body.dataset.tableMode) || 'all';
}

function initLoadingAndConfirm() {
    var loadGlobal = typeof window.__layoutEnabled === 'function' && window.__layoutEnabled('global-loading');
    var formConfirm = typeof window.__layoutEnabled === 'function' && window.__layoutEnabled('form-confirm');
    if (!loadGlobal && !formConfirm) return;

    var overlay = document.getElementById('global-loading-overlay');
    var bar = document.getElementById('global-loading-bar');
    var pendingProcess = 0;

    function showLoading() {
        if (overlay) {
            overlay.classList.add('is-active');
            overlay.setAttribute('aria-busy', 'true');
            overlay.setAttribute('aria-hidden', 'false');
        }
        if (bar) {
            bar.style.width = '35%';
            setTimeout(function () {
                if (pendingProcess > 0) bar.style.width = '75%';
            }, 180);
        }
    }

    function hideLoading() {
        if (bar) {
            bar.style.width = '100%';
            setTimeout(function () { bar.style.width = '0'; }, 180);
        }
        if (overlay) {
            overlay.classList.remove('is-active');
            overlay.setAttribute('aria-busy', 'false');
            overlay.setAttribute('aria-hidden', 'true');
        }
    }

    function setSubmitButtonLoading(button) {
        if (!button || button.dataset.loadingActive === '1') return;
        button.dataset.loadingActive = '1';
        button.dataset.originalHtml = button.innerHTML;
        button.disabled = true;
        button.classList.add('opacity-70', 'cursor-not-allowed');
        button.innerHTML = '<span class="inline-flex items-center gap-2"><svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" class="opacity-20" stroke="currentColor" stroke-width="4"></circle><path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="4" stroke-linecap="round"></path></svg>Memproses...</span>';
    }

    function resolveFormMethod(form) {
        var spoofMethodInput = form.querySelector('input[name="_method"]');
        if (spoofMethodInput && spoofMethodInput.value) return spoofMethodInput.value.toUpperCase();
        return (form.getAttribute('method') || 'GET').toUpperCase();
    }

    function getConfirmationMessage(form) {
        if (form.dataset.confirm === 'off') return null;
        if (form.dataset.confirm) return form.dataset.confirm;
        var method = resolveFormMethod(form);
        var action = (form.getAttribute('action') || '').toLowerCase();
        if (action.includes('/logout')) return null;
        if (method === 'DELETE') return 'Apakah Anda yakin data ini akan dihapus?';
        if (method === 'PUT' || method === 'PATCH') return 'Apakah Anda yakin perubahan data ini akan disimpan?';
        return null;
    }

    function ensureConfirmModal() {
        var modal = document.getElementById('global-confirm-modal');
        if (modal) {
            return modal;
        }

        /* Fallback lama (layout tanpa komponen Blade) — akan dihapus setelah semua layout memuat x-ui.confirm-modal */
        var wrapper = document.createElement('div');
        wrapper.id = 'global-confirm-modal';
        wrapper.className = 'sipeni-confirm-modal';
        wrapper.setAttribute('aria-hidden', 'true');
        wrapper.innerHTML = ''
            + '<div data-confirm-backdrop class="sipeni-confirm-modal__backdrop"></div>'
            + '<div class="sipeni-confirm-modal__dialog" data-confirm-dialog tabindex="-1">'
            + '  <div class="sipeni-confirm-modal__body">'
            + '    <div class="sipeni-confirm-modal__icon" aria-hidden="true">'
            + '      <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">'
            + '        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 19h14.14c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.2 16c-.77 1.33.19 3 1.73 3z"></path>'
            + '      </svg>'
            + '    </div>'
            + '    <div class="sipeni-confirm-modal__content">'
            + '      <h3 id="global-confirm-title" class="sipeni-confirm-modal__title">Konfirmasi Aksi</h3>'
            + '      <p data-confirm-message class="sipeni-confirm-modal__message">Apakah Anda yakin ingin melanjutkan?</p>'
            + '    </div>'
            + '  </div>'
            + '  <div class="sipeni-confirm-modal__actions" data-confirm-actions>'
            + '    <button type="button" data-confirm-cancel class="btn-secondary-ui sipeni-confirm-modal__btn">Batal</button>'
            + '    <button type="button" data-confirm-ok class="btn-primary-ui sipeni-confirm-modal__btn">Ya, Lanjutkan</button>'
            + '  </div>'
            + '</div>';
        document.body.appendChild(wrapper);
        return wrapper;
    }

    function openConfirmModal(modal) {
        if (!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeConfirmModal(modal) {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    }

    function showConfirmDialog(message, onConfirm, onCancel) {
        var modal = ensureConfirmModal();
        if (!modal) {
            if (typeof onConfirm === 'function' && window.confirm(message || 'Apakah Anda yakin ingin melanjutkan?')) {
                onConfirm();
            } else if (typeof onCancel === 'function') {
                onCancel();
            }
            return;
        }

        var messageEl = modal.querySelector('[data-confirm-message]');
        var okBtn = modal.querySelector('[data-confirm-ok]');
        var cancelBtn = modal.querySelector('[data-confirm-cancel]');
        var backdrop = modal.querySelector('[data-confirm-backdrop]');
        var dialog = modal.querySelector('[data-confirm-dialog]');

        if (messageEl) messageEl.textContent = message || 'Apakah Anda yakin ingin melanjutkan?';
        openConfirmModal(modal);
        // Jangan fokuskan Batal/Ya sebagai default — fokus ke dialog saja (tanpa ring tombol).
        if (dialog) {
            if (!dialog.hasAttribute('tabindex')) dialog.setAttribute('tabindex', '-1');
            dialog.focus({ preventScroll: true });
        } else if (document.activeElement && typeof document.activeElement.blur === 'function') {
            document.activeElement.blur();
        }

        function cleanup() {
            closeConfirmModal(modal);
            if (okBtn) okBtn.removeEventListener('click', handleConfirm);
            if (cancelBtn) cancelBtn.removeEventListener('click', handleCancel);
            if (backdrop) backdrop.removeEventListener('click', handleCancel);
            document.removeEventListener('keydown', handleKeydown);
        }

        function handleConfirm() {
            cleanup();
            if (typeof onConfirm === 'function') onConfirm();
        }

        function handleCancel() {
            cleanup();
            if (typeof onCancel === 'function') onCancel();
        }

        function handleKeydown(event) {
            if (event.key === 'Escape') handleCancel();
            if (event.key === 'Enter') handleConfirm();
        }

        if (okBtn) okBtn.addEventListener('click', handleConfirm);
        if (cancelBtn) cancelBtn.addEventListener('click', handleCancel);
        if (backdrop) backdrop.addEventListener('click', handleCancel);
        document.addEventListener('keydown', handleKeydown);
    }

    function extractConfirmMessage(handlerCode) {
        if (!handlerCode) return null;
        var match = handlerCode.match(/confirm\s*\(\s*(['"`])([\s\S]*?)\1\s*\)/i);
        return match ? match[2] : null;
    }

    function neutralizeConfirm(handlerCode) {
        if (!handlerCode) return '';
        return handlerCode
            .replace(/return\s+confirm\s*\(\s*(['"`])[\s\S]*?\1\s*\)\s*;?/gi, 'return true;')
            .replace(/confirm\s*\(\s*(['"`])[\s\S]*?\1\s*\)\s*;?/gi, '')
            .trim();
    }

    function migrateLegacyInlineConfirm() {
        var forms = document.querySelectorAll('form[onsubmit], form [onclick], a[onclick], button[onclick], input[type="submit"][onclick]');
        forms.forEach(function (el) {
            if (el.dataset.legacyConfirmHandled === '1') return;

            if (el.tagName === 'FORM') {
                var onSubmitRaw = el.getAttribute('onsubmit') || '';
                var submitMessage = extractConfirmMessage(onSubmitRaw);
                if (submitMessage) {
                    el.dataset.confirm = submitMessage;
                    var cleanedSubmit = neutralizeConfirm(onSubmitRaw);
                    if (cleanedSubmit) {
                        el.setAttribute('onsubmit', cleanedSubmit);
                    } else {
                        el.removeAttribute('onsubmit');
                    }
                }
                el.dataset.legacyConfirmHandled = '1';
                return;
            }

            var raw = el.getAttribute('onclick') || '';
            var message = extractConfirmMessage(raw);
            if (!message) {
                el.dataset.legacyConfirmHandled = '1';
                return;
            }

            var cleaned = neutralizeConfirm(raw);
            if (cleaned) {
                el.setAttribute('onclick', cleaned);
            } else {
                el.removeAttribute('onclick');
            }

            el.addEventListener('click', function (event) {
                event.preventDefault();
                var targetForm = el.closest('form');
                showConfirmDialog(message, function () {
                    if (targetForm) {
                        targetForm.dataset.confirmApproved = '1';
                        targetForm.requestSubmit(el.type === 'submit' ? el : undefined);
                    } else if (el.tagName === 'A' && el.href) {
                        window.location.href = el.href;
                    }
                });
            });

            el.dataset.legacyConfirmHandled = '1';
        });
    }

    migrateLegacyInlineConfirm();

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement)) return;

        if (form.dataset.confirmApproved === '1') {
            form.dataset.confirmApproved = '0';
        } else if (formConfirm) {
            var confirmationMessage = getConfirmationMessage(form);
            if (confirmationMessage) {
                event.preventDefault();
                showConfirmDialog(confirmationMessage, function () {
                    form.dataset.confirmApproved = '1';
                    form.requestSubmit(event.submitter || undefined);
                });
                return;
            }
        }

        if (loadGlobal) {
            var submitter = event.submitter || form.querySelector('button[type="submit"], input[type="submit"]');
            setSubmitButtonLoading(submitter);
            pendingProcess++;
            showLoading();
        }
    }, true);

    if (loadGlobal) {
        var nativeFetch = window.fetch;
        window.fetch = function () {
            var input = arguments[0];
            var init = arguments[1] || {};
            var silent = false;

            try {
                if (init && init.sipeniSilent === true) {
                    silent = true;
                } else if (init && init.headers) {
                    if (typeof Headers !== 'undefined' && init.headers instanceof Headers) {
                        silent = init.headers.get('X-Sipeni-Silent') === '1';
                    } else if (typeof init.headers === 'object') {
                        silent = String(init.headers['X-Sipeni-Silent'] || init.headers['x-sipeni-silent'] || '') === '1';
                    }
                } else if (typeof Request !== 'undefined' && input instanceof Request) {
                    silent = input.headers.get('X-Sipeni-Silent') === '1';
                }
            } catch (e) {
                silent = false;
            }

            if (silent) {
                return nativeFetch.apply(window, arguments);
            }

            pendingProcess++;
            showLoading();
            return nativeFetch.apply(window, arguments).finally(function () {
                pendingProcess = Math.max(0, pendingProcess - 1);
                if (pendingProcess === 0) hideLoading();
            });
        };

        window.addEventListener('pageshow', function () {
            pendingProcess = 0;
            hideLoading();
        });
    }

    window.Sipeni = window.Sipeni || {};
    window.Sipeni.confirm = function (message) {
        return new Promise(function (resolve) {
            showConfirmDialog(
                message || 'Apakah Anda yakin ingin melanjutkan?',
                function () { resolve(true); },
                function () { resolve(false); }
            );
        });
    };
}

function initNavigationHelpers() {
    if (isAuthPage()) return;
    window.toggleSubmenu = function (id) {
        var submenu = document.getElementById(id + '-submenu');
        var arrow = document.getElementById(id + '-arrow');
        if (!submenu) return;

        var willOpen = submenu.classList.contains('hidden');

        document.querySelectorAll('#app-sidebar nav [id$="-submenu"]').forEach(function (el) {
            if (el.id === id + '-submenu') return;
            el.classList.add('hidden');
            var otherId = el.id.replace(/-submenu$/, '');
            var otherArrow = document.getElementById(otherId + '-arrow');
            if (otherArrow) otherArrow.classList.remove('rotate-90');
        });

        if (willOpen) {
            submenu.classList.remove('hidden');
            if (arrow) arrow.classList.add('rotate-90');
        } else {
            submenu.classList.add('hidden');
            if (arrow) arrow.classList.remove('rotate-90');
        }
    };

    normalizeSidebarAccordionOnLoad();

    window.toggleUserMenu = function () {
        var menu = document.getElementById('user-dropdown-menu');
        var arrow = document.getElementById('user-menu-arrow');
        if (menu) menu.classList.toggle('hidden');
        if (arrow) arrow.classList.toggle('rotate-180');
    };

    document.addEventListener('click', function (event) {
        var userMenu = document.getElementById('user-dropdown-menu');
        var userButton = document.getElementById('user-menu-button');
        if (userMenu && userButton && !userMenu.contains(event.target) && !userButton.contains(event.target)) {
            userMenu.classList.add('hidden');
            var arrow = document.getElementById('user-menu-arrow');
            if (arrow) arrow.classList.remove('rotate-180');
        }
    });

    initMobileSidebar();
}

function normalizeSidebarAccordionOnLoad() {
    var sidebar = document.getElementById('app-sidebar');
    if (!sidebar) return;

    var openSubmenus = Array.from(sidebar.querySelectorAll('[id$="-submenu"]')).filter(function (el) {
        return !el.classList.contains('hidden');
    });
    if (openSubmenus.length <= 1) return;

    var keep = openSubmenus.find(function (sub) {
        return sub.querySelector('a.bg-blue-600');
    }) || openSubmenus[0];

    openSubmenus.forEach(function (sub) {
        if (sub === keep) return;
        sub.classList.add('hidden');
        var groupId = sub.id.replace(/-submenu$/, '');
        var arrow = document.getElementById(groupId + '-arrow');
        if (arrow) arrow.classList.remove('rotate-90');
    });

    var keepId = keep.id.replace(/-submenu$/, '');
    var keepArrow = document.getElementById(keepId + '-arrow');
    if (keepArrow) keepArrow.classList.add('rotate-90');
}

function initMobileSidebar() {
    var sidebar = document.getElementById('app-sidebar');
    var backdrop = document.getElementById('sidebar-backdrop');
    var toggleBtn = document.getElementById('sidebar-toggle');
    var closeBtn = document.getElementById('sidebar-close');
    if (!sidebar || !backdrop) return;

    var mqDesktop = window.matchMedia('(min-width: 1024px)');

    function setSidebarOpen(open) {
        var isOpen = !!open && !mqDesktop.matches;
        document.body.classList.toggle('sidebar-open', isOpen);
        sidebar.classList.toggle('translate-x-0', isOpen);
        sidebar.classList.toggle('-translate-x-full', !isOpen && !mqDesktop.matches);
        backdrop.classList.toggle('hidden', !isOpen);
        backdrop.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        if (toggleBtn) {
            toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }
    }

    window.closeMobileSidebar = function () {
        setSidebarOpen(false);
    };

    window.openMobileSidebar = function () {
        setSidebarOpen(true);
    };

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            var willOpen = !document.body.classList.contains('sidebar-open');
            setSidebarOpen(willOpen);
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            setSidebarOpen(false);
        });
    }

    backdrop.addEventListener('click', function () {
        setSidebarOpen(false);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            setSidebarOpen(false);
        }
    });

    sidebar.querySelectorAll('a[href]').forEach(function (link) {
        link.addEventListener('click', function () {
            if (!mqDesktop.matches) {
                setSidebarOpen(false);
            }
        });
    });

    mqDesktop.addEventListener('change', function () {
        setSidebarOpen(false);
    });
}

function ensureChoicesLoaded() {
    if (typeof window.__layoutEnabled === 'function' && !window.__layoutEnabled('choices-init')) return;
    if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.select2 === 'function') {
        window.__gLog && window.__gLog('Select2 loaded successfully');
        window.choicesLoaded = true;
        return;
    }
    window.__gWarn && window.__gWarn('Select2 not loaded, trying fallback...');

    function loadSelect2Script() {
        var select2Script = document.createElement('script');
        select2Script.src = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js';
        select2Script.onload = function () {
            window.choicesLoaded = true;
            window.__gLog && window.__gLog('Select2 loaded from fallback CDN');
        };
        select2Script.onerror = function () {
            console.error('Select2 failed to load from CDN');
        };
        document.head.appendChild(select2Script);
    }

    if (window.jQuery) {
        loadSelect2Script();
        return;
    }

    var jqueryScript = document.createElement('script');
    jqueryScript.src = 'https://code.jquery.com/jquery-3.7.1.min.js';
    jqueryScript.onload = function () {
        loadSelect2Script();
    };
    jqueryScript.onerror = function () {
        console.error('jQuery failed to load from CDN');
    };
    document.head.appendChild(jqueryScript);
}

function initChoicesHelpers() {
    window.sipeniSelect2BaseOptions = function () {
        return {
            width: '100%',
            minimumResultsForSearch: 0,
            containerCssClass: 'sipeni-s2',
            dropdownCssClass: 'sipeni-s2-dropdown',
            selectionCssClass: 'sipeni-s2-selection',
            language: {
                noResults: function () {
                    return 'Tidak ada hasil';
                },
                searching: function () {
                    return 'Mencari...';
                },
                inputTooShort: function (args) {
                    return 'Ketik minimal ' + args.minimum + ' karakter';
                },
            },
        };
    };

    window.initChoicesForSelect = function (selectElement, minOptions) {
        var minOpts = typeof minOptions === 'number' ? minOptions : 2;
        if (!selectElement || selectElement.tagName !== 'SELECT') return null;
        if (!(window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.select2 === 'function')) return null;
        if (selectElement.choicesInstance) return selectElement.choicesInstance;
        if (selectElement.dataset.sipeniSelect2Init === '1') return selectElement.choicesInstance;
        if (window.jQuery && window.jQuery(selectElement).hasClass('select2-hidden-accessible')) {
            selectElement.dataset.sipeniSelect2Init = '1';
            return selectElement.choicesInstance || null;
        }

        var optionCount = Array.from(selectElement.options).filter(function (opt) { return opt.value !== ''; }).length;
        var isDataBarangOrSatuan = selectElement.classList.contains('select-data-barang')
            || selectElement.classList.contains('select-satuan')
            || selectElement.id === 'id_data_barang'
            || selectElement.id === 'id_satuan';

        if (!(optionCount > minOpts || (isDataBarangOrSatuan && optionCount > 0))) return null;

        try {
            var $ = window.jQuery;
            var placeholderOption = selectElement.querySelector('option[value=""]');
            var placeholderText = ((placeholderOption && placeholderOption.textContent) || 'Pilih...').trim();
            var allowClear = !!placeholderOption;

            // Pattern requested user: class marker for single placeholder select2.
            selectElement.classList.add('js-example-placeholder-single');

            var select2Options = Object.assign({}, window.sipeniSelect2BaseOptions(), {
                placeholder: placeholderText || 'Pilih...',
                allowClear: allowClear,
            });

            $(selectElement).select2(select2Options);

            var choicesInstance = {
                destroy: function () {
                    try {
                        $(selectElement).select2('destroy');
                    } catch (e) {
                        // noop
                    }
                },
                setChoiceByValue: function (value) {
                    $(selectElement).val(value).trigger('change');
                }
            };

            selectElement.choicesInstance = choicesInstance;
            selectElement.dataset.sipeniSelect2Init = '1';
            return choicesInstance;
        } catch (error) {
            console.error('Error initializing Select2 for select:', selectElement.id || 'unnamed select', error);
            return null;
        }
    };

    function normalizeSelectOptions(select) {
        Array.from(select.options).forEach(function (option) {
            var rawText = option.textContent || option.innerText || option.getAttribute('label') || option.value || '';
            option.textContent = String(rawText).replace(/\s+/g, ' ').trim();
        });
    }

    function countNonEmptyOptions(select) {
        return Array.from(select.options).filter(function (opt) { return opt.value !== ''; }).length;
    }

    function shouldEnhanceSelect(select) {
        if (!select || select.tagName !== 'SELECT' || select.multiple) return false;
        if (select.getAttribute('data-searchable') === 'false') return false;
        if (select.choicesInstance || select.dataset.sipeniSelect2Init === '1') return false;
        if (window.jQuery && window.jQuery(select).hasClass('select2-hidden-accessible')) return false;

        var forceSearch = select.getAttribute('data-searchable') === 'true'
            || select.classList.contains('select-searchable')
            || select.classList.contains('select-data-barang')
            || select.classList.contains('select-satuan');

        var optionCount = countNonEmptyOptions(select);
        if (optionCount === 0 && !select.value) return false;

        return forceSearch || optionCount > 5;
    }

    function initializeSearchableSelects() {
        if (typeof window.__layoutEnabled === 'function' && !window.__layoutEnabled('choices-init')) return;
        if (!(window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.select2 === 'function')) {
            if (!window.choicesRetryCount) window.choicesRetryCount = 0;
            if (window.choicesRetryCount < 10) {
                window.choicesRetryCount++;
                setTimeout(initializeSearchableSelects, 100);
            }
            return;
        }
        window.choicesRetryCount = 0;

        var searchableFieldIds = ['id_data_barang', 'id_item', 'id_subjenis_barang', 'id_satuan', 'id_kategori_barang', 'id_jenis_barang', 'id_kode_barang', 'id_aset', 'id_ruangan', 'id_pegawai', 'id_penanggung_jawab', 'id_sub_kegiatan', 'id_program', 'id_kegiatan'];
        var seen = typeof WeakSet !== 'undefined' ? new WeakSet() : null;

        function tryInit(select, minOpts) {
            if (!select) return;
            if (seen && seen.has(select)) return;
            if (seen) seen.add(select);
            normalizeSelectOptions(select);
            var min = typeof minOpts === 'number' ? minOpts : 5;
            var isSpecial = select.classList.contains('select-data-barang')
                || select.classList.contains('select-satuan')
                || searchableFieldIds.indexOf(select.id) !== -1;
            if (isSpecial) min = 0;
            var inst = window.initChoicesForSelect(select, min);
            if (inst) select.dataset.sipeniSelect2Init = '1';
        }

        searchableFieldIds.forEach(function (fieldId) {
            tryInit(document.getElementById(fieldId), 0);
        });

        document.querySelectorAll('select[data-searchable="true"], select.select-searchable, select.select-data-barang, select.select-satuan, form select:not([multiple])').forEach(function (select) {
            if (!shouldEnhanceSelect(select)) return;
            tryInit(select, select.classList.contains('select-data-barang') || select.classList.contains('select-satuan') ? 0 : 5);
        });
    }

    function scheduleSearchableSelectInit() {
        if (typeof window.__layoutEnabled === 'function' && !window.__layoutEnabled('choices-init')) return;
        if (window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.select2 === 'function') {
            window.requestAnimationFrame(function () { initializeSearchableSelects(); });
            return;
        }
        if (!window.choicesRetryCount) window.choicesRetryCount = 0;
        if (window.choicesRetryCount < 30) {
            window.choicesRetryCount++;
            setTimeout(scheduleSearchableSelectInit, 100);
        }
    }

    if (typeof window.__layoutEnabled !== 'function' || window.__layoutEnabled('choices-init')) {
        scheduleSearchableSelectInit();
    }
}

function initTableEnhancer() {
    if (typeof window.__layoutEnabled === 'function' && !window.__layoutEnabled('table-enhance')) return;

    function parseCellValue(value) {
        var text = (value || '').trim();
        if (text === '') return '';
        var normalizedNumber = text.replace(/\./g, '').replace(',', '.').replace(/[^\d.-]/g, '');
        if (normalizedNumber !== '' && !Number.isNaN(Number(normalizedNumber))) return Number(normalizedNumber);
        var date = Date.parse(text);
        if (!Number.isNaN(date)) return date;
        return text.toLowerCase();
    }
    function debounce(fn, ms) {
        var t;
        return function () {
            var ctx = this;
            var args = arguments;
            clearTimeout(t);
            t = setTimeout(function () { fn.apply(ctx, args); }, ms);
        };
    }
    function enhanceTable(table) {
        if (table.dataset.enhanced === '1' || table.classList.contains('table-no-enhance')) return;
        var tbody = table.tBodies && table.tBodies[0];
        if (!tbody) return;
        var allRows = Array.from(tbody.rows);
        if (!allRows.length) return;
        var headers = table.tHead ? Array.from(table.tHead.rows[0].cells) : [];
        var hasNumberHeader = headers.length > 0 && /^no$/i.test((headers[0].textContent || '').trim());

        var toolbar = document.createElement('div');
        toolbar.className = 'mb-3 flex flex-wrap items-center gap-2';
        toolbar.innerHTML = '<input type="text" data-table-search placeholder="Cari data pada tabel..." class="w-full sm:w-64 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"><select data-table-filter-column class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"><option value="all">Semua Kolom</option></select><input type="text" data-table-filter-value placeholder="Filter kolom..." class="w-full sm:w-56 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">';
        table.parentNode.insertBefore(toolbar, table);
        var searchInput = toolbar.querySelector('[data-table-search]');
        var filterColumn = toolbar.querySelector('[data-table-filter-column]');
        var filterValue = toolbar.querySelector('[data-table-filter-value]');

        function getVisibleRows() { return allRows.filter(function (row) { return row.style.display !== 'none'; }); }
        function applyRowNumbers() {
            var visibleRows = getVisibleRows();
            if (hasNumberHeader) {
                var baseAttr = table.getAttribute('data-pagination-base');
                var base = baseAttr !== null && baseAttr !== '' && !Number.isNaN(parseInt(baseAttr, 10)) ? parseInt(baseAttr, 10) : null;
                visibleRows.forEach(function (row, index) { if (row.cells[0]) row.cells[0].textContent = String(base !== null ? base + index : index + 1); });
                return;
            }
            visibleRows.forEach(function (row, index) {
                var numberCell = row.querySelector('td[data-auto-row-number="1"]');
                if (!numberCell) {
                    numberCell = document.createElement('td');
                    numberCell.setAttribute('data-auto-row-number', '1');
                    numberCell.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-900';
                    row.insertBefore(numberCell, row.firstChild);
                }
                numberCell.textContent = String(index + 1);
            });
        }

        if (!hasNumberHeader && table.tHead && table.tHead.rows[0]) {
            var th = document.createElement('th');
            th.textContent = 'No';
            th.className = 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider no-sort';
            table.tHead.rows[0].insertBefore(th, table.tHead.rows[0].firstChild);
        }

        table.classList.add('w-full', 'text-sm');
        if (!table.classList.contains('table-fixed')) {
            table.classList.add('table-auto');
        }
        if (table.tHead) table.tHead.classList.add('bg-gray-50');
        allRows.forEach(function (row) {
            row.classList.add('hover:bg-gray-50', 'transition-colors');
            Array.from(row.cells || []).forEach(function (cell) {
                // Kompres tinggi row global untuk tabel index/list
                cell.classList.remove('px-6', 'py-4', 'py-3');
                cell.classList.add('px-3', 'py-2', 'align-middle');
            });
        });

        if (table.tHead && table.tHead.rows && table.tHead.rows[0]) {
            Array.from(table.tHead.rows[0].cells || []).forEach(function (th) {
                th.classList.remove('px-6', 'py-3');
                th.classList.add('px-3', 'py-2', 'align-middle');
            });
        }

        var sortCol = -1;
        var sortDir = 'asc';
        headers.forEach(function (th, idx) {
            var headerText = (th.textContent || '').trim().toLowerCase();
            if (headerText === 'no') {
                th.style.width = '1%';
                th.style.whiteSpace = 'nowrap';
                th.classList.add('text-center');
                Array.from(tbody.rows || []).forEach(function (row) {
                    var td = row.cells[idx];
                    if (!td) return;
                    td.style.width = '1%';
                    td.style.whiteSpace = 'nowrap';
                    td.classList.add('text-center', 'px-2');
                });
            }
            if (headerText === 'aksi' || headerText === 'action' || headerText === 'opsi') {
                th.classList.add('no-sort');
                th.style.width = '1%';
                th.style.whiteSpace = 'nowrap';
                var bodyRows = Array.from(tbody.rows || []);
                bodyRows.forEach(function (row) {
                    var td = row.cells[idx];
                    if (!td) return;
                    td.style.width = '1%';
                    td.style.whiteSpace = 'nowrap';
                    td.classList.remove('px-6', 'py-4', 'py-3');
                    td.classList.add('px-2', 'py-1.5', 'align-middle');
                });
            }
            if (filterColumn && !th.classList.contains('no-sort')) {
                var option = document.createElement('option');
                option.value = String(idx);
                option.textContent = th.textContent.trim();
                filterColumn.appendChild(option);
            }
            if (th.classList.contains('no-sort')) return;
            th.style.cursor = 'pointer';
            if (!th.dataset.baseLabel) th.dataset.baseLabel = th.textContent.trim();
            th.addEventListener('click', function () {
                if (sortCol === idx) sortDir = sortDir === 'asc' ? 'desc' : 'asc';
                else { sortCol = idx; sortDir = 'asc'; }
                headers.forEach(function (h) { if (h.dataset.baseLabel) h.textContent = h.dataset.baseLabel; });
                th.textContent = th.dataset.baseLabel + (sortDir === 'asc' ? ' ↑' : ' ↓');
                allRows.sort(function (a, b) {
                    var av = parseCellValue(a.cells[idx] ? a.cells[idx].innerText : '');
                    var bv = parseCellValue(b.cells[idx] ? b.cells[idx].innerText : '');
                    if (av === bv) return 0;
                    if (sortDir === 'asc') return av > bv ? 1 : -1;
                    return av < bv ? 1 : -1;
                });
                allRows.forEach(function (r) { tbody.appendChild(r); });
                applyVisibleRows();
            });
        });

        var firstSortableIndex = headers.findIndex(function (th) { return !th.classList.contains('no-sort'); });
        if (firstSortableIndex >= 0) {
            sortCol = firstSortableIndex;
            sortDir = 'asc';
            var activeHeader = headers[firstSortableIndex];
            headers.forEach(function (h) { if (h.dataset.baseLabel) h.textContent = h.dataset.baseLabel; });
            if (activeHeader.dataset.baseLabel) activeHeader.textContent = activeHeader.dataset.baseLabel + ' ↑';
            allRows.sort(function (a, b) {
                var av = parseCellValue(a.cells[firstSortableIndex] ? a.cells[firstSortableIndex].innerText : '');
                var bv = parseCellValue(b.cells[firstSortableIndex] ? b.cells[firstSortableIndex].innerText : '');
                if (av === bv) return 0;
                return av > bv ? 1 : -1;
            });
            allRows.forEach(function (r) { tbody.appendChild(r); });
        }

        function applyVisibleRows() {
            var searchTerm = ((searchInput && searchInput.value) || '').trim().toLowerCase();
            var filterTerm = ((filterValue && filterValue.value) || '').trim().toLowerCase();
            var selectedColumn = (filterColumn && filterColumn.value) || 'all';
            allRows.forEach(function (row) {
                var rowText = row.innerText.toLowerCase();
                var isSearchMatch = searchTerm === '' || rowText.includes(searchTerm);
                var isFilterMatch = true;
                if (filterTerm !== '') {
                    if (selectedColumn === 'all') isFilterMatch = rowText.includes(filterTerm);
                    else {
                        var colIndex = parseInt(selectedColumn, 10);
                        var cellText = row.cells[colIndex] ? row.cells[colIndex].innerText.toLowerCase() : '';
                        isFilterMatch = cellText.includes(filterTerm);
                    }
                }
                row.style.display = isSearchMatch && isFilterMatch ? '' : 'none';
            });
            applyRowNumbers();
        }

        var debouncedApply = debounce(applyVisibleRows, 200);
        if (searchInput) searchInput.addEventListener('input', debouncedApply);
        if (filterColumn) filterColumn.addEventListener('change', applyVisibleRows);
        if (filterValue) filterValue.addEventListener('input', debouncedApply);
        applyVisibleRows();
        table.dataset.enhanced = '1';
    }

    var mode = window.__tableMode || 'all';
    var selector = mode === 'explicit' ? 'table[data-table-enhance="1"]' : 'table';
    document.querySelectorAll(selector).forEach(enhanceTable);
}

function initActionIcons() {
    if (typeof window.__layoutEnabled === 'function' && !window.__layoutEnabled('action-icons')) return;
    var ACTION_ICON_MAP = {
        detail: '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z"></path></svg>',
        lihat: '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z"></path></svg>',
        edit: '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>',
        hapus: '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-1 12a2 2 0 01-2 2H8a2 2 0 01-2-2L5 7"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11v6M14 11v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"></path></svg>',
        delete: '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-1 12a2 2 0 01-2 2H8a2 2 0 01-2-2L5 7"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11v6M14 11v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"></path></svg>'
    };
    function normalizeActionText(text) { return (text || '').replace(/\s+/g, ' ').trim().toLowerCase(); }
    function toIconActionElement(el, actionKey, label) {
        if (!el || el.dataset.iconifiedAction === '1' || el.querySelector('svg')) return;
        el.dataset.iconifiedAction = '1';
        el.setAttribute('title', label);
        el.setAttribute('aria-label', label);
        el.className = 'inline-flex items-center justify-center h-7 w-7 rounded-full border transition-colors duration-150';
        if (actionKey === 'detail' || actionKey === 'lihat') el.classList.add('border-blue-200', 'bg-blue-50', 'text-blue-600', 'hover:bg-blue-100');
        else if (actionKey === 'edit') el.classList.add('border-indigo-200', 'bg-indigo-50', 'text-indigo-600', 'hover:bg-indigo-100');
        else el.classList.add('border-red-200', 'bg-red-50', 'text-red-600', 'hover:bg-red-100');
        el.innerHTML = ACTION_ICON_MAP[actionKey] + '<span class="sr-only">' + label + '</span>';
    }
    document.querySelectorAll('table td a, table td button').forEach(function (el) {
        if (el.closest('[data-skip-action-icons]')) return;
        var actionText = normalizeActionText(el.textContent);
        if (!actionText) return;
        if (actionText === 'detail' || actionText === 'lihat') toIconActionElement(el, actionText, actionText === 'detail' ? 'Detail' : 'Lihat');
        else if (actionText === 'edit') toIconActionElement(el, 'edit', 'Edit');
        else if (actionText === 'hapus' || actionText === 'delete') toIconActionElement(el, actionText, actionText === 'hapus' ? 'Hapus' : 'Delete');
    });
}

function initFilterFormConsistency() {
    if (isAuthPage()) return;
    var forms = document.querySelectorAll('main form[method="GET"], main form[method="get"]');
    forms.forEach(function (form) {
        if (form.closest('[data-index-filter-toolbar="1"]')) return;
        if (form.dataset.filterUi === 'off') return;
        if (form.classList.contains('grid')) return;

        var hasSearchOrFilterField = form.querySelector('input[name*="search"], select[name], input[type="date"], input[type="text"]');
        var submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
        if (!hasSearchOrFilterField || !submitButton) return;

        // Keep existing layout intent, only standardize spacing and field styles.
        form.classList.add('flex', 'flex-wrap', 'items-end', 'gap-3');
        form.dataset.filterUiNormalized = '1';

        form.querySelectorAll('label').forEach(function (label) {
            label.classList.add('block', 'text-sm', 'font-medium', 'text-gray-700', 'mb-1');
        });

        form.querySelectorAll('input[type="text"], input[type="search"], input[type="date"], select').forEach(function (field) {
            if (field.classList.contains('choices__input') || field.classList.contains('select2-hidden-accessible')) return;
            field.classList.add(
                'block',
                'w-full',
                'rounded-md',
                'border',
                'border-gray-300',
                'py-2',
                'px-3',
                'text-sm',
                'shadow-sm',
                'focus:border-blue-500',
                'focus:outline-none',
                'focus:ring-2',
                'focus:ring-blue-500'
            );
        });

        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (button) {
            button.classList.add(
                'inline-flex',
                'items-center',
                'justify-center',
                'rounded-md',
                'border',
                'border-transparent',
                'bg-blue-600',
                'px-4',
                'py-2',
                'text-sm',
                'font-medium',
                'text-white',
                'shadow-sm',
                'hover:bg-blue-700',
                'focus:outline-none',
                'focus:ring-2',
                'focus:ring-blue-500',
                'focus:ring-offset-2'
            );
        });
    });
}

function initGlobalPaginationSizeSelector() {
    if (isAuthPage()) return;
    var paginations = document.querySelectorAll('main nav[role="navigation"]');
    if (!paginations.length) return;

    paginations.forEach(function (pagination) {
        if (pagination.dataset.perPageEnhanced === '1') return;

        var wrapper = document.createElement('div');
        wrapper.className = 'mb-3 flex items-center justify-end gap-2 text-sm text-gray-700';
        wrapper.innerHTML = ''
            + '<label for="global-per-page-selector" class="text-sm font-medium text-gray-700">Rows:</label>'
            + '<select id="global-per-page-selector" class="rounded-md border border-gray-300 bg-white px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">'
            + '  <option value="10">10</option>'
            + '  <option value="20">20</option>'
            + '  <option value="50">50</option>'
            + '  <option value="100">100</option>'
            + '</select>';

        var selector = wrapper.querySelector('select');
        var currentPerPage = (new URLSearchParams(window.location.search).get('per_page') || '10').trim();
        if (!['10', '20', '50', '100'].includes(currentPerPage)) currentPerPage = '10';
        selector.value = currentPerPage;

        selector.addEventListener('change', function () {
            var params = new URLSearchParams(window.location.search);
            params.set('per_page', selector.value);
            params.delete('page');
            window.location.search = params.toString();
        });

        pagination.parentNode.insertBefore(wrapper, pagination);
        pagination.dataset.perPageEnhanced = '1';
    });
}

onReady(function () {
    initLayoutGlobals();
    ensureChoicesLoaded();
    initLoadingAndConfirm();
    initNavigationHelpers();
    initChoicesHelpers();
    initTableEnhancer();
    initFilterFormConsistency();
    initGlobalPaginationSizeSelector();

    var runDeferredUi = function () {
        initActionIcons();
    };
    if (typeof window.requestIdleCallback === 'function') {
        window.requestIdleCallback(runDeferredUi, { timeout: 1500 });
    } else {
        setTimeout(runDeferredUi, 200);
    }
});
