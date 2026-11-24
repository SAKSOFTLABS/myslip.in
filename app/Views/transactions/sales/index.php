<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-text me-2 text-primary"></i> Sales Invoices</h4>
        <p class="text-muted small mb-0">Track all tax invoices, retail bills, payments, and print statuses</p>
    </div>
    <a href="<?= url('/sales/create') ?>" class="btn btn-primary-custom shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> Create Sale <span class="kbd-shortcut ms-1">F2</span>
    </a>
</div>

<div class="card-custom">
    <div class="card-header">
        <form action="<?= url('/sales') ?>" method="GET" class="d-flex flex-wrap gap-2 w-100">
            <div class="input-group input-group-sm" style="max-width: 280px;">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="search" name="search" class="form-control" placeholder="Search invoice #, customer..." value="<?= e($search ?? '') ?>" autofocus>
            </div>

            <div class="input-group input-group-sm" style="max-width: 320px;">
                <span class="input-group-text bg-light">Dates</span>
                <input type="date" name="start_date" class="form-control" value="<?= e($startDate ?? '') ?>">
                <input type="date" name="end_date" class="form-control" value="<?= e($endDate ?? '') ?>">
            </div>

            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            <?php if (!empty($search) || !empty($startDate) || !empty($endDate)): ?>
                <a href="<?= url('/sales') ?>" class="btn btn-sm btn-light border">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Customer Name</th>
                        <th>Payment Mode</th>
                        <th class="text-end">Taxable</th>
                        <?php if (\App\Core\Session::get('gst_enabled', true)): ?>
                            <th class="text-end">Total Tax</th>
                        <?php endif; ?>
                        <th class="text-end">Grand Total</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sales)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-receipt fs-3 d-block mb-1"></i>
                                No sales invoices found. Click <strong>"Create Sale"</strong> to create an invoice.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sales as $s): ?>
                            <tr class="<?= $s['status'] === 'cancelled' ? 'table-danger opacity-75' : '' ?>">
                                <td>
                                    <a href="<?= url('/sales/view/' . $s['id']) ?>" class="fw-bold text-primary text-decoration-none">
                                        <?= e($s['invoice_number']) ?>
                                    </a>
                                </td>
                                <td><?= formatDate($s['invoice_date']) ?></td>
                                <td>
                                    <div class="fw-bold"><?= e($s['customer_name']) ?></div>
                                    <?php if ($s['customer_phone']): ?>
                                        <div class="text-muted small"><?= e($s['customer_phone']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.72rem;">
                                        <?= e($s['payment_mode']) ?>
                                    </span>
                                </td>
                                <td class="text-end"><?= formatCurrency($s['taxable_amount']) ?></td>
                                <?php if (\App\Core\Session::get('gst_enabled', true)): ?>
                                    <td class="text-end"><?= formatCurrency($s['total_tax_amount']) ?></td>
                                <?php endif; ?>
                                <td class="text-end fw-bold"><?= formatCurrency($s['grand_total']) ?></td>
                                <td class="text-center">
                                    <?php if ($s['status'] === 'cancelled'): ?>
                                        <span class="badge bg-danger">Cancelled</span>
                                    <?php else: ?>
                                        <span class="badge bg-<?= $s['payment_status'] === 'paid' ? 'success' : ($s['payment_status'] === 'partial' ? 'warning' : 'danger') ?>-subtle text-<?= $s['payment_status'] === 'paid' ? 'success' : ($s['payment_status'] === 'partial' ? 'warning' : 'danger') ?>">
                                            <?= ucfirst($s['payment_status']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= url('/invoices/print/' . $s['id']) ?>" class="btn btn-outline-dark" title="Print Invoice" target="_blank">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <a href="<?= url('/sales/view/' . $s['id']) ?>" class="btn btn-outline-primary" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
