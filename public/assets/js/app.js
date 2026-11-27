/**
 * MYSLIP.IN — Core Application JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // 1. Mobile Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.app-sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            sidebar.classList.toggle('show');
        });
    }

    // 2. Global Keyboard Shortcuts
    document.addEventListener('keydown', function(e) {
        // F2 = Quick New Invoice
        if (e.key === 'F2') {
            e.preventDefault();
            window.location.href = window.APP_URL + '/sales/create';
        }
        // F4 = Focus Global / Table Search
        if (e.key === 'F4') {
            const searchInput = document.querySelector('input[type="search"], input[name="search"], #globalSearch');
            if (searchInput) {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
        }
    });

    // 3. Auto-dismiss success/info alerts after 5 seconds (keep error/danger alerts visible)
    const alerts = document.querySelectorAll('.alert-dismissible:not(.alert-danger)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            try {
                const bsAlert = bootstrap.Alert.getInstance(alert) || new bootstrap.Alert(alert);
                bsAlert.close();
            } catch(e) {}
        }, 5000);
    });

    // 4. Initialize dd-mm-yyyy Datepickers across all date inputs
    initGlobalDatepickers();

    document.addEventListener('shown.bs.modal', function(e) {
        initGlobalDatepickers(e.target);
    });
});

/**
 * Global Datepicker Initializer (enforces dd-mm-yyyy visual display across all browsers and locales)
 */
function initGlobalDatepickers(root = document) {
    if (typeof flatpickr !== 'undefined') {
        const dateInputs = root.querySelectorAll('input[type="date"], input.datepicker');
        dateInputs.forEach(function(el) {
            if (el._flatpickr) return;
            flatpickr(el, {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d-m-Y",
                allowInput: true,
                parseDate: function(dateStr, format) {
                    if (!dateStr) return null;
                    const dmy = dateStr.match(/^(\d{1,2})[-\/.](\d{1,2})[-\/.](\d{4})$/);
                    if (dmy) {
                        return new Date(parseInt(dmy[3], 10), parseInt(dmy[2], 10) - 1, parseInt(dmy[1], 10));
                    }
                    const ymd = dateStr.match(/^(\d{4})[-\/.](\d{1,2})[-\/.](\d{1,2})$/);
                    if (ymd) {
                        return new Date(parseInt(ymd[1], 10), parseInt(ymd[2], 10) - 1, parseInt(ymd[3], 10));
                    }
                    const d = new Date(dateStr);
                    return isNaN(d.getTime()) ? null : d;
                },
                onReady: function(selectedDates, dateStr, instance) {
                    if (instance.altInput) {
                        instance.altInput.placeholder = "dd-mm-yyyy";
                        if (el.classList.contains('form-control-sm') || el.closest('.input-group-sm')) {
                            instance.altInput.classList.add('form-control-sm');
                        }
                        if (el.hasAttribute('required')) {
                            instance.altInput.setAttribute('required', 'required');
                        }
                        if (el.hasAttribute('disabled')) {
                            instance.altInput.setAttribute('disabled', 'disabled');
                        }
                        if (el.hasAttribute('readonly')) {
                            instance.altInput.setAttribute('readonly', 'readonly');
                        }

                        instance.altInput.addEventListener('blur', function() {
                            const val = instance.altInput.value.trim();
                            if (val) {
                                instance.setDate(val, true);
                            }
                        });
                    }
                }
            });
        });
    }
}
window.initGlobalDatepickers = initGlobalDatepickers;

// Toast notification helper
function showToast(message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }

    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0 shadow`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');

    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-info-circle me-2"></i> ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    container.appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
    toast.show();
}

// Confirmation helper
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}
