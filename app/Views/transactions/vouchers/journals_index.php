<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-journal-check me-2 text-primary"></i> Journal Vouchers</h4>
        <p class="text-muted small mb-0">Double-entry adjusting vouchers, depreciation, transfers, and corrections</p>
    </div>
    <a href="<?= url('/vouchers/journals/create') ?>" class="btn btn-primary-custom shadow-sm">
        <i class="bi bi-plus-lg me-1"></i> New Journal Entry
    </a>
</div>

<div class="card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom table-hover mb-0">
                <thead>
                    <tr>
                        <th>Journal #</th>
                        <th>Date</th>
                        <th>Narration / Particulars</th>
                        <th class="text-end">Total Debit (Dr)</th>
                        <th class="text-end">Total Credit (Cr)</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($journals)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted small">No journal vouchers created yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($journals as $j): ?>
                            <tr>
                                <td><strong><?= e($j['journal_number']) ?></strong></td>
                                <td><?= formatDate($j['journal_date']) ?></td>
                                <td><?= e($j['narration']) ?></td>
                                <td class="text-end fw-bold"><?= formatCurrency($j['total_debit']) ?></td>
                                <td class="text-end fw-bold"><?= formatCurrency($j['total_credit']) ?></td>
                                <td class="text-center"><span class="badge bg-success-subtle text-success">Balanced</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
