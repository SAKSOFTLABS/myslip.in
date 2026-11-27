<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1024, user-scalable=yes">
    <title><?= isset($pageTitle) ? e($pageTitle) : 'Invoice Print' ?> — MYSLIP.IN</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/print.css') ?>" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body class="print-preview-body">

<div class="no-print bg-dark text-white p-3 d-flex justify-content-between align-items-center" style="min-width: 1024px; box-sizing: border-box;">
    <div>
        <?php if (!empty($isPublicView)): ?>
            <span class="fw-bold fs-6 text-white d-inline-flex align-items-center">
                <i class="bi bi-receipt-cutoff text-warning me-2"></i> <?= e($company['company_name'] ?? 'MYSLIP') ?> — Invoice #<?= e($sale['invoice_number'] ?? '') ?>
            </span>
        <?php else: ?>
            <a href="<?= url('/sales') ?>" class="btn btn-sm btn-outline-light me-1">
                <i class="bi bi-arrow-left me-1"></i> Back to Invoices
            </a>
        <?php endif; ?>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-sm btn-light fw-semibold" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> <?= !empty($isPublicView) ? 'Print / PDF' : 'Print Invoice (Ctrl + P)' ?>
        </button>
        <button type="button" id="btnDownloadInvoice" class="btn btn-sm btn-primary fw-semibold" onclick="downloadInvoicePDF()" title="Directly download invoice as PDF">
            <i class="bi bi-download me-1"></i> Download Invoice
        </button>
        <?php if (empty($isPublicView)): ?>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-success fw-semibold" onclick="shareWhatsApp()" title="Share bill on WhatsApp">
                    <i class="bi bi-whatsapp me-1"></i> Share WhatsApp
                </button>
                <button type="button" class="btn btn-sm btn-success dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" title="WhatsApp Options">
                    <span class="visually-hidden">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" onclick="shareWhatsApp(false)">
                            <i class="bi bi-person-check text-success me-2"></i>Send to Customer <?php if (!empty($customerMobile)): ?>(<span class="fw-semibold"><?= e($customerMobile) ?></span>)<?php else: ?><span class="text-muted small">(No number)</span><?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0)" onclick="openWhatsAppModal()">
                            <i class="bi bi-pencil-square text-primary me-2"></i>Enter Custom Number...
                        </a>
                    </li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="print-preview-wrapper">
    <div class="invoice-scaler-container">
        <?= $content ?>
    </div>
</div>

<?php if (empty($isPublicView)): ?>
<!-- WhatsApp Recipient Number Modal -->
<div class="modal fade no-print" id="whatsappShareModal" tabindex="-1" aria-labelledby="whatsappShareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0 rounded-3">
            <div class="modal-header bg-success text-white py-2 px-3">
                <h6 class="modal-title fw-bold" id="whatsappShareModalLabel">
                    <i class="bi bi-whatsapp me-1"></i> Share Bill via WhatsApp
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="text-muted small mb-2">
                    <?php if (empty($customerMobile)): ?>
                        <div class="alert alert-warning py-1 px-2 small mb-2">
                            <i class="bi bi-exclamation-triangle me-1"></i> Customer <strong><?= e($sale['customer_name'] ?? 'Customer') ?></strong> has no phone number recorded.
                        </div>
                    <?php endif; ?>
                    Please enter the recipient's WhatsApp mobile number to share Invoice <strong><?= e($sale['invoice_number'] ?? '') ?></strong>:
                </div>
                
                <div class="mb-3">
                    <label for="whatsapp_mobile_input" class="form-label fw-bold small text-dark mb-1">
                        WhatsApp Mobile Number <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted fw-bold small">+91</span>
                        <input type="tel" 
                               id="whatsapp_mobile_input" 
                               class="form-control" 
                               placeholder="10-digit mobile number (e.g. 9876543210)" 
                               maxlength="15" 
                               autocomplete="off">
                    </div>
                    <div class="form-text text-muted" style="font-size: 11px;">
                        Enter 10-digit mobile number. Country code +91 will be applied automatically.
                    </div>
                    <div id="whatsapp_modal_error" class="alert alert-danger py-1 px-2 small mt-2 d-none" role="alert"></div>
                </div>

                <div class="bg-light p-2 rounded border small">
                    <div class="fw-semibold text-dark mb-1" style="font-size: 11px;"><i class="bi bi-receipt text-success me-1"></i> Bill Summary:</div>
                    <div class="text-muted" style="font-size: 11px; line-height: 1.4;">
                        <div><strong>Invoice:</strong> <?= e($sale['invoice_number'] ?? '') ?> (<?= formatDate($sale['invoice_date'] ?? date('Y-m-d')) ?>)</div>
                        <div><strong>Customer:</strong> <?= e($sale['customer_name'] ?? 'Customer') ?></div>
                        <div><strong>Grand Total:</strong> <?= formatCurrency($sale['grand_total'] ?? 0) ?></div>
                        <?php if ((float)($sale['balance_amount'] ?? 0) > 0): ?>
                            <div><strong>Balance Due:</strong> <?= formatCurrency($sale['balance_amount']) ?></div>
                        <?php else: ?>
                            <div><strong>Payment Status:</strong> <span class="text-success fw-bold">Fully Paid</span></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0 py-2 px-3">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm px-3 fw-bold" onclick="confirmWhatsAppShare()">
                    <i class="bi bi-whatsapp me-1"></i> Send on WhatsApp
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
const customerDataPhone = <?= json_encode($customerMobile ?? '') ?>;

function cleanDigits(phone) {
    if (!phone) return '';
    return phone.toString().replace(/\D/g, '');
}

function formatWhatsAppNumber(phone) {
    let digits = cleanDigits(phone);
    if (!digits) return '';

    // If 10 digits (India), prepend 91
    if (digits.length === 10) {
        return '91' + digits;
    }
    // If 11 digits starting with 0, replace 0 with 91
    if (digits.length === 11 && digits.startsWith('0')) {
        return '91' + digits.substring(1);
    }
    // If already 11+ digits (has country code)
    if (digits.length >= 11) {
        return digits;
    }
    return digits;
}

function shareWhatsApp(forceModal = false) {
    const formatted = formatWhatsAppNumber(customerDataPhone);

    // If customer has a valid phone number (at least 10 digits) and modal not forced:
    if (!forceModal && formatted && formatted.length >= 10) {
        // Direct share using customer data!
        sendToWhatsApp(formatted);
    } else {
        // Customer has no phone number or forced modal: open the popup!
        openWhatsAppModal(customerDataPhone ? cleanDigits(customerDataPhone) : '');
    }
}

function openWhatsAppModal(prefill = '') {
    const input = document.getElementById('whatsapp_mobile_input');
    const errorDiv = document.getElementById('whatsapp_modal_error');
    
    if (errorDiv) {
        errorDiv.classList.add('d-none');
        errorDiv.textContent = '';
    }

    if (input) {
        let val = cleanDigits(prefill);
        // If 12 digits starting with 91, strip 91 since +91 addon is displayed
        if (val.length === 12 && val.startsWith('91')) {
            val = val.substring(2);
        }
        input.value = val;
    }

    const modalEl = document.getElementById('whatsappShareModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
        setTimeout(() => {
            if (input) {
                input.focus();
                input.select();
            }
        }, 400);
    }
}

function confirmWhatsAppShare() {
    const input = document.getElementById('whatsapp_mobile_input');
    const errorDiv = document.getElementById('whatsapp_modal_error');
    const entered = input ? input.value.trim() : '';
    const formatted = formatWhatsAppNumber(entered);

    if (!formatted || formatted.length < 10) {
        if (errorDiv) {
            errorDiv.textContent = 'Please enter a valid 10-digit mobile number.';
            errorDiv.classList.remove('d-none');
        }
        if (input) input.focus();
        return;
    }

    const modalEl = document.getElementById('whatsappShareModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }

    sendToWhatsApp(formatted);
}

function buildWhatsAppMessage() {
    const companyName = <?= json_encode($company['company_name'] ?? 'MYSLIP') ?>;
    const customerName = <?= json_encode($sale['customer_name'] ?? 'Customer') ?>;
    const invNumber = <?= json_encode($sale['invoice_number'] ?? '') ?>;
    const invDate = <?= json_encode(formatDate($sale['invoice_date'] ?? date('Y-m-d'))) ?>;
    const grandTotal = <?= json_encode(formatCurrency($sale['grand_total'] ?? 0)) ?>;
    const paidAmount = <?= json_encode(formatCurrency($sale['paid_amount'] ?? 0)) ?>;
    const balanceDue = <?= (float)($sale['balance_amount'] ?? 0) ?>;
    const publicUrl = <?= json_encode($publicInvoiceUrl ?? '') ?>;

    let fullUrl = publicUrl ? publicUrl.trim() : '';
    if (fullUrl && !fullUrl.startsWith('http://') && !fullUrl.startsWith('https://')) {
        fullUrl = window.location.origin + (fullUrl.startsWith('/') ? '' : '/') + fullUrl;
    }

    let msg = `Hello ${customerName},\n\n`;
    msg += `Thank you for your business with *${companyName}*!\n`;
    msg += `Here are your invoice details:\n\n`;
    msg += `• *Invoice No:* ${invNumber}\n`;
    msg += `• *Date:* ${invDate}\n`;
    msg += `• *Grand Total:* ${grandTotal}\n`;
    msg += `• *Paid Amount:* ${paidAmount}\n`;
    if (balanceDue > 0) {
        msg += `• *Balance Due:* ₹${balanceDue.toFixed(2)}\n`;
    } else {
        msg += `• *Payment Status:* Fully Paid\n`;
    }
    if (fullUrl) {
        msg += `\n*View / Print Bill Online:*\n${fullUrl}\n`;
    }
    msg += `\nBest regards,\n*${companyName}*`;
    return msg;
}

function sendToWhatsApp(phone) {
    const message = buildWhatsAppMessage();
    const whatsappUrl = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank');
}

function downloadInvoicePDF() {
    const element = document.querySelector('.invoice-a4-box, .print-container');
    if (!element) return;

    const invoiceNumber = <?= json_encode($sale['invoice_number'] ?? 'invoice') ?>;
    const filename = `${invoiceNumber}.pdf`;

    const btn = document.getElementById('btnDownloadInvoice');
    const originalHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Downloading...';
    }

    if (typeof html2pdf === 'undefined') {
        window.print();
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
        return;
    }

    const opt = {
        margin:       [0, 0, 0, 0],
        filename:     filename,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true, letterRendering: true, logging: false },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).save().then(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }).catch(err => {
        console.error('PDF generation error:', err);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
        window.print();
    });
}

// Support Enter key press inside modal input
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('whatsapp_mobile_input');
    if (input) {
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                confirmWhatsAppShare();
            }
        });
    }
});
</script>

</body>
</html>
