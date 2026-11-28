<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title><?= isset($pageTitle) ? e($pageTitle) : 'Invoice Print' ?> — MYSLIP.IN</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= asset('css/print.css') ?>?v=<?= time() ?>" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
    @media print {
        /* Force perfect Paper Fit for A4 / A5 / Thermal */
        @page {
            size: <?= (!empty($format) && $format === 'thermal') ? 'auto' : ((!empty($format) && strtolower($format) === 'a5') ? 'A5 portrait' : 'A4 portrait') ?>;
            margin: <?= (!empty($format) && $format === 'thermal') ? '0' : '8mm' ?>;
        }

        html,
        body,
        body.print-preview-body {
            min-width: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            color: #000000 !important;
            font-family: 'Segoe UI', Arial, sans-serif !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            overflow: visible !important;
        }

        .print-preview-wrapper,
        .invoice-scaler-container,
        .invoice-box-wrapper {
            padding: 0 !important;
            margin: 0 !important;
            background: transparent !important;
            display: block !important;
            min-width: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
            height: auto !important;
            overflow: visible !important;
        }

        /* 100% Paper Fit: full printable width & full printable height (280mm) with zero void at bottom */
        .invoice-a4-box,
        .invoice-classic-gst,
        .invoice-a4-fullheight,
        .invoice-creative-emerald,
        .invoice-executive-gold,
        .invoice-minimal,
        .invoice-modern-blue,
        .invoice-wholesale-grid,
        .print-container {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            height: 280mm !important;
            min-height: 280mm !important;
            max-height: 280mm !important;
            margin: 0 !important;
            padding: 0 !important;
            box-sizing: border-box !important;
            background: #ffffff !important;
            box-shadow: none !important;
            border: none !important;
            transform: none !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            page-break-after: avoid !important;
            page-break-inside: avoid !important;
        }

        .classic-table-wrapper,
        .a4-table-wrapper {
            flex-grow: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            min-height: 120mm !important;
        }

        .classic-gst-table,
        .a4-items-table {
            height: 100% !important;
            flex-grow: 1 !important;
        }

        .classic-gst-table tbody tr.spacer-row td,
        .a4-items-table tbody tr.spacer-row td {
            height: 100% !important;
            border-top: none !important;
            border-bottom: 1.5px solid #000000 !important;
            border-left: 1px solid #000000 !important;
            border-right: 1px solid #000000 !important;
            padding: 0 !important;
        }

        .classic-bottom-section,
        .a4-bottom-section {
            flex-shrink: 0 !important;
            margin-top: auto !important;
            page-break-inside: avoid !important;
        }
    }
    </style>
</head>
<body class="print-preview-body">

<div class="no-print bg-dark text-white p-2 p-md-3 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom border-secondary" style="width: 100%; box-sizing: border-box;">
    <div class="d-flex align-items-center gap-2">
        <?php if (!empty($isPublicView)): ?>
            <span class="fw-bold fs-6 text-white d-inline-flex align-items-center">
                <i class="bi bi-receipt-cutoff text-warning me-2"></i> <?= e($company['company_name'] ?? 'MYSLIP') ?> — Invoice #<?= e($sale['invoice_number'] ?? '') ?>
            </span>
        <?php else: ?>
            <a href="<?= url('/sales') ?>" class="btn btn-sm btn-outline-light me-1">
                <i class="bi bi-arrow-left me-1"></i> <span class="d-none d-sm-inline">Back to Invoices</span><span class="d-inline d-sm-none">Back</span>
            </a>
            <span class="fw-semibold text-light small d-none d-md-inline">
                Invoice #<?= e($sale['invoice_number'] ?? '') ?>
            </span>
        <?php endif; ?>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <!-- View Size Toggle for Mobile -->
        <button type="button" class="btn btn-sm btn-outline-light d-md-none fw-semibold" id="zoomToggleBtn" onclick="toggleMobileZoom()" title="Toggle View Size">
            <i class="bi bi-aspect-ratio me-1"></i> <span id="zoomToggleText">100%</span>
        </button>
        <button type="button" class="btn btn-sm btn-light fw-semibold" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> <?= !empty($isPublicView) ? 'Print / PDF' : 'Print Invoice (Ctrl + P)' ?>
        </button>
        <button type="button" id="btnDownloadInvoice" class="btn btn-sm btn-primary fw-semibold" onclick="downloadInvoicePDF()" title="Directly download invoice as PDF">
            <i class="bi bi-download me-1"></i> <span class="d-none d-sm-inline">Download Invoice</span><span class="d-inline d-sm-none">Download</span>
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
    <div id="invoiceScalerContainer" class="invoice-scaler-container">
        <div id="invoiceBoxWrapper" class="invoice-box-wrapper">
            <?= $content ?>
        </div>
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

    // Reset transform on wrapper during PDF capture so canvas captures pristine unscaled A4
    const wrapper = document.getElementById('invoiceBoxWrapper');
    const prevTransform = wrapper ? wrapper.style.transform : '';
    if (wrapper) wrapper.style.transform = 'none';

    const opt = {
        margin:       [0, 0, 0, 0],
        filename:     filename,
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true, letterRendering: true, logging: false },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).save().then(() => {
        if (wrapper) wrapper.style.transform = prevTransform;
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    }).catch(err => {
        console.error('PDF generation error:', err);
        if (wrapper) wrapper.style.transform = prevTransform;
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
        window.print();
    });
}

// Responsive Mobile A4 Auto-Fit Scaling
let isFitScreen = true;

function applyMobileA4Scaling() {
    const scalerContainer = document.getElementById('invoiceScalerContainer');
    const invoiceWrapper = document.getElementById('invoiceBoxWrapper');
    const zoomText = document.getElementById('zoomToggleText');
    if (!scalerContainer || !invoiceWrapper) return;

    const screenWidth = window.innerWidth;

    const invoiceBox = invoiceWrapper.querySelector('.invoice-a4-box, .invoice-classic-gst, .invoice-a4-fullheight, .invoice-creative-emerald, .invoice-executive-gold, .invoice-minimal, .invoice-modern-blue, .invoice-wholesale-grid, .invoice-a5-box, .invoice-retail-a5, .invoice-thermal-box, .print-container');
    if (!invoiceBox) return;

    const isThermal = invoiceBox.classList.contains('invoice-thermal-box') || 
                      invoiceBox.classList.contains('format-thermal') || 
                      invoiceBox.classList.contains('thermal-receipt-58mm') || 
                      invoiceBox.classList.contains('thermal-receipt-80mm');
    const isA5 = invoiceBox.classList.contains('invoice-a5-box') || 
                 invoiceBox.classList.contains('invoice-retail-a5') || 
                 invoiceBox.classList.contains('format-a5');

    const standardWidth = isThermal ? 320 : (isA5 ? 560 : 794);

    if (screenWidth < 820 && !isThermal) {
        if (isFitScreen) {
            const availableWidth = Math.max(screenWidth - 20, 280);
            const scale = availableWidth / standardWidth;

            invoiceWrapper.style.width = standardWidth + 'px';
            invoiceWrapper.style.minWidth = standardWidth + 'px';
            invoiceWrapper.style.transformOrigin = 'top left';
            invoiceWrapper.style.transform = `scale(${scale})`;

            const scaledWidth = Math.round(standardWidth * scale);
            const unscaledHeight = invoiceWrapper.scrollHeight || invoiceBox.scrollHeight || 1123;
            const scaledHeight = Math.ceil(unscaledHeight * scale);

            scalerContainer.style.width = scaledWidth + 'px';
            scalerContainer.style.height = (scaledHeight + 30) + 'px';
            scalerContainer.style.overflow = 'hidden';
            scalerContainer.style.margin = '0 auto';

            if (zoomText) zoomText.textContent = '100%';
        } else {
            invoiceWrapper.style.width = standardWidth + 'px';
            invoiceWrapper.style.minWidth = standardWidth + 'px';
            invoiceWrapper.style.transformOrigin = 'top left';
            invoiceWrapper.style.transform = 'none';

            scalerContainer.style.width = standardWidth + 'px';
            scalerContainer.style.height = 'auto';
            scalerContainer.style.overflow = 'visible';
            scalerContainer.style.margin = '0 auto';

            if (zoomText) zoomText.textContent = 'Fit';
        }
    } else {
        invoiceWrapper.style.transform = 'none';
        invoiceWrapper.style.width = '';
        invoiceWrapper.style.minWidth = '';
        invoiceWrapper.style.transformOrigin = '';

        scalerContainer.style.width = '';
        scalerContainer.style.height = 'auto';
        scalerContainer.style.overflow = 'visible';
        scalerContainer.style.margin = '0 auto';

        if (zoomText) zoomText.textContent = '100%';
    }
}

function toggleMobileZoom() {
    isFitScreen = !isFitScreen;
    applyMobileA4Scaling();
}

window.addEventListener('resize', applyMobileA4Scaling);
window.addEventListener('orientationchange', () => {
    setTimeout(applyMobileA4Scaling, 200);
});
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(applyMobileA4Scaling, 80);
});
window.addEventListener('load', () => {
    setTimeout(applyMobileA4Scaling, 100);
});

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
