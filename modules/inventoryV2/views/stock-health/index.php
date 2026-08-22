<?php

use app\components\widgets\DataSummaryWidget;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var yii\data\ArrayDataProvider $dataProvider */

$this->title = 'ตรวจสุขภาพสต็อก';
$models = $dataProvider->getModels();

$statusLabels = [
    'critical' => ['วิกฤต', 'danger'],
    'mismatch' => ['ยอดไม่ตรง', 'warning'],
    'review' => ['ควรตรวจสอบ', 'info'],
    'healthy' => ['ปกติ', 'success'],
];
$issueLabels = [
    'duplicate_balance' => 'Balance ซ้ำ',
    'negative_fifo' => 'FIFO ติดลบ',
    'negative_balance' => 'Balance ติดลบ',
    'fifo_over_received' => 'FIFO มากกว่ารับเข้า',
    'orphan_balance' => 'มี Balance แต่ไม่มีต้นทาง',
    'balance_without_fifo' => 'มี Balance แต่ไม่มี Lot จ่าย',
    'balance_fifo_mismatch' => 'Balance ไม่ตรง FIFO',
    'ledger_balance_mismatch' => 'ประวัติไม่ตรง Balance',
    'ledger_unavailable' => 'ไม่มีประวัติที่เข้าเกณฑ์คำนวณ',
    'orphan_allocation' => 'Allocation ชี้ต้นทางที่หาย',
    'missing_allocation' => 'ใบจ่ายไม่มี Allocation',
    'history_only_edit' => 'เคยแก้เฉพาะประวัติ',
];
$repairLabels = [
    'none' => 'ไม่ต้องดำเนินการ',
    'dry_run_sync_fifo' => 'จำลองการปรับ FIFO ให้ตรง Balance',
    'dry_run_sync_balance' => 'จำลองการปรับ Balance ให้ตรงประวัติ',
    'dry_run_sync_ledger' => 'จำลองการปรับประวัติให้ตรง Balance/FIFO',
    'manual_count_required' => 'ต้องตรวจเอกสารหรือตรวจนับจริง',
];
?>

<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>เปรียบเทียบประวัติ, ยอดสรุป และ Lot ที่จ่ายได้ โดยไม่เปลี่ยนข้อมูล<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?><?= $this->render('@app/modules/inventoryV2/views/default/_menu_main', ['active' => 'stock-health']) ?><?php $this->endBlock(); ?>

<div class="alert alert-info d-flex align-items-start gap-2" role="status">
    <i class="bi bi-shield-check mt-1" aria-hidden="true"></i>
    <div><strong>ตรวจสอบก่อนเปลี่ยนข้อมูลทุกครั้ง</strong><div class="small"><?= $canRepair ? 'ระบบอนุญาตให้ซ่อมเฉพาะรายการที่หลักฐานสอดคล้องกัน พร้อมบันทึกผู้ดำเนินการและเหตุผล' : 'บัญชีของคุณดูผล Dry-run ได้ แต่ไม่มีสิทธิ์เปลี่ยนยอดสต็อก' ?></div></div>
</div>

<form method="get" class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-lg-4">
                <label class="form-label" for="health-search">ค้นหา</label>
                <input id="health-search" type="search" name="search" value="<?= Html::encode($filters['search']) ?>" class="form-control" placeholder="รหัส ชื่อวัสดุ หรือ Lot">
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label" for="health-warehouse">คลัง</label>
                <?= Html::dropDownList('warehouse_id', $filters['warehouse_id'] ?: '', $warehouses, ['id' => 'health-warehouse', 'class' => 'form-select']) ?>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="form-label" for="health-status">สถานะ</label>
                <?= Html::dropDownList('status', $filters['status'], [
                    '' => 'ทุกสถานะผิดปกติ', 'critical' => 'วิกฤต', 'mismatch' => 'ยอดไม่ตรง', 'review' => 'ควรตรวจสอบ', 'healthy' => 'ปกติ',
                ], ['id' => 'health-status', 'class' => 'form-select']) ?>
            </div>
            <div class="col-12 col-lg-3 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1" type="submit"><i class="bi bi-search me-1"></i>ตรวจสอบ</button>
                <a class="btn btn-outline-secondary" href="<?= Url::to(['index']) ?>">ล้างตัวกรอง</a>
            </div>
        </div>
        <div class="form-check mt-3">
            <?= Html::checkbox('include_healthy', $filters['include_healthy'], ['id' => 'include-healthy', 'class' => 'form-check-input', 'value' => 1]) ?>
            <label class="form-check-label" for="include-healthy">รวมรายการปกติในผลตรวจ</label>
        </div>
    </div>
</form>

<div class="d-flex flex-wrap gap-2 align-items-center mb-3" aria-label="สรุปผลตรวจ">
    <span class="badge bg-danger-subtle text-danger-emphasis">วิกฤต <?= number_format($summary['critical']) ?></span>
    <span class="badge bg-warning-subtle text-warning-emphasis">ยอดไม่ตรง <?= number_format($summary['mismatch']) ?></span>
    <span class="badge bg-info-subtle text-info-emphasis">ควรตรวจสอบ <?= number_format($summary['review']) ?></span>
    <?php if ($filters['include_healthy']): ?><span class="badge bg-success-subtle text-success-emphasis">ปกติ <?= number_format($summary['healthy']) ?></span><?php endif; ?>
    <span class="small text-body-secondary ms-lg-auto">ประมวลผล <?= Html::encode($generatedAt) ?></span>
    <a class="btn btn-sm btn-outline-success" href="<?= Url::to(array_merge(['export'], Yii::$app->request->getQueryParams())) ?>"><i class="bi bi-file-earmark-spreadsheet me-1"></i>ส่งออก CSV</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="d-none d-lg-block table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-secondary">
                    <tr>
                        <th>วัสดุ / คลัง</th><th>Lot</th><th class="text-end">ประวัติ</th><th class="text-end">Balance</th><th class="text-end">FIFO</th><th>ผลตรวจ</th><th class="text-center">รายละเอียด</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($models as $row): $badge = $statusLabels[$row['status']]; ?>
                    <tr>
                        <td><div class="fw-semibold"><?= Html::encode($row['item_code'] . ' · ' . $row['item_name']) ?></div><div class="small text-body-secondary"><?= Html::encode($row['warehouse_name']) ?></div></td>
                        <td class="font-monospace"><?= Html::encode($row['lot_number']) ?></td>
                        <td class="text-end font-monospace"><?= $row['ledger_qty'] === null ? '—' : number_format($row['ledger_qty'], 2) ?></td>
                        <td class="text-end font-monospace"><?= number_format($row['balance_qty'], 2) ?></td>
                        <td class="text-end font-monospace"><?= number_format($row['fifo_qty'], 2) ?></td>
                        <td><span class="badge bg-<?= $badge[1] ?>-subtle text-<?= $badge[1] ?>-emphasis"><?= $badge[0] ?></span><div class="small text-body-secondary mt-1"><?= Html::encode(implode(', ', array_map(fn($v) => $issueLabels[$v] ?? $v, $row['issues']))) ?></div></td>
                        <td class="text-center">
                            <?php if ($row['status'] === 'healthy'): ?>
                                <span class="small text-body-secondary"><i class="bi bi-check-circle me-1"></i>ไม่ต้องดำเนินการ</span>
                            <?php else: ?>
                                <button type="button" class="btn btn-sm btn-outline-primary health-detail" data-row="<?= Html::encode(json_encode($row, JSON_UNESCAPED_UNICODE)) ?>"><i class="bi bi-clipboard-data me-1"></i>ตรวจและเลือกวิธีแก้</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($models)): ?><tr><td colspan="7" class="text-center py-5"><div class="fw-semibold">ไม่พบรายการตามเงื่อนไข</div><div class="small text-body-secondary mt-1">ลองเปลี่ยนคลัง สถานะ หรือคำค้นหา</div></td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

        <ul class="list-group list-group-flush d-lg-none" role="list">
            <?php foreach ($models as $row): $badge = $statusLabels[$row['status']]; ?>
                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between gap-2"><strong><?= Html::encode($row['item_code']) ?></strong><span class="badge bg-<?= $badge[1] ?>-subtle text-<?= $badge[1] ?>-emphasis"><?= $badge[0] ?></span></div>
                    <div><?= Html::encode($row['item_name']) ?></div>
                    <div class="small text-body-secondary"><?= Html::encode($row['warehouse_name'] . ' · Lot ' . $row['lot_number']) ?></div>
                    <dl class="row small mt-2 mb-2"><dt class="col-4">ประวัติ</dt><dd class="col-8 text-end mb-1"><?= $row['ledger_qty'] === null ? '—' : number_format($row['ledger_qty'], 2) ?></dd><dt class="col-4">Balance</dt><dd class="col-8 text-end mb-1"><?= number_format($row['balance_qty'], 2) ?></dd><dt class="col-4">FIFO</dt><dd class="col-8 text-end mb-0"><?= number_format($row['fifo_qty'], 2) ?></dd></dl>
                    <?php if ($row['status'] === 'healthy'): ?>
                        <div class="small text-success"><i class="bi bi-check-circle me-1"></i>ไม่ต้องดำเนินการ</div>
                    <?php else: ?>
                        <button type="button" class="btn btn-outline-primary w-100 health-detail" data-row="<?= Html::encode(json_encode($row, JSON_UNESCAPED_UNICODE)) ?>">ตรวจและเลือกวิธีแก้</button>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="card-footer bg-body">
        <?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?>
    </div>
</div>

<div class="modal fade" id="healthDetailModal" tabindex="-1" aria-labelledby="healthDetailTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h2 class="modal-title fs-5" id="healthDetailTitle">ผลจำลองการตรวจสอบ</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button></div>
        <div class="modal-body" id="healthDetailBody" aria-live="polite"></div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button><button type="button" class="btn btn-danger d-none" id="healthRepairButton" disabled>ยืนยันซ่อมยอด</button></div>
    </div></div>
</div>

<?php
$repairJson = json_encode($repairLabels, JSON_UNESCAPED_UNICODE);
$issueJson = json_encode($issueLabels, JSON_UNESCAPED_UNICODE);
$dryRunUrl = json_encode(Url::to(['dry-run']));
$repairUrl = json_encode(Url::to(['repair']));
$csrfParam = json_encode(Yii::$app->request->csrfParam);
$csrfToken = json_encode(Yii::$app->request->csrfToken);
$this->registerJs(<<<JS
const healthRepairLabels = $repairJson;
const healthIssueLabels = $issueJson;
const healthDryRunUrl = $dryRunUrl;
const healthRepairUrl = $repairUrl;
const healthCsrfParam = $csrfParam;
const healthCsrfToken = $csrfToken;
const healthEscape = value => String(value == null ? '' : value).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
const healthPost = async (url, values) => {
    const body = new URLSearchParams({...values, [healthCsrfParam]: healthCsrfToken});
    const response = await fetch(url, {method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest'}, body});
    const data = await response.json();
    if (!response.ok || data.success === false) throw new Error(data.message || 'ไม่สามารถดำเนินการได้');
    return data;
};
let activeRepair = null;
const renderHealthDryRun = (row, payload, response, body, repairButton) => {
    const result = response.result || {};
    const operations = result.plan?.operations || [];
    const operationRows = operations.map(op => `<tr><td>\${op.target === 'fifo' ? 'FIFO ที่จ่ายได้' : (op.target === 'ledger' ? 'ประวัติการเคลื่อนไหว' : 'Balance')}</td><td class="font-monospace">\${healthEscape(op.lot_number)}</td><td class="text-end">\${Number(op.before).toLocaleString()}</td><td class="text-end fw-semibold">\${Number(op.after).toLocaleString()}</td><td class="text-end">\${Number(op.delta).toLocaleString()}</td></tr>`).join('');
    const canEnterPhysicalCount = !result.allowed && payload.scope === 'item' && result.evidence
        && result.evidence.ledger_qty !== null
        && (Math.abs(Number(result.evidence.ledger_qty) - Number(result.evidence.balance_qty)) > 0.0001
            || Math.abs(Number(result.evidence.balance_qty) - Number(result.evidence.fifo_qty)) > 0.0001);
    body.innerHTML = `
        <div class="alert alert-info"><strong>ผล Dry-run</strong><div class="small">ขั้นตอนนี้ยังไม่เปลี่ยนข้อมูลในฐานข้อมูล</div></div>
        <h3 class="fs-6">\${healthEscape(row.item_code)} · \${healthEscape(row.item_name)}</h3>
        <p class="text-body-secondary">\${healthEscape(row.warehouse_name)} · \${payload.scope === 'item' ? 'รวมทุก Lot' : 'Lot ' + healthEscape(row.lot_number)}</p>
        \${payload.scope !== row.scope ? '<div class="alert alert-secondary py-2"><i class="bi bi-arrow-repeat me-1"></i>ระบบเปลี่ยนจาก Lot เป็น “รวมทุก Lot” ให้อัตโนมัติ เพราะปัญหานี้อยู่ที่ยอดประวัติรวม</div>' : ''}
        \${result.allowed ? `<div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>ข้อมูลที่จะซ่อม</th><th>Lot</th><th class="text-end">ก่อน</th><th class="text-end">หลัง</th><th class="text-end">ผลต่าง</th></tr></thead><tbody>\${operationRows}</tbody></table></div>` : `<div class="alert alert-warning"><strong>ต้องใช้ยอดตรวจนับจริง</strong><div class="small mt-1">\${healthEscape(result.message)}</div></div>`}
        \${canEnterPhysicalCount ? `<div class="card border-primary"><div class="card-body"><label for="healthPhysicalQty" class="form-label fw-semibold">ยอดตรวจนับจริง (รวมทุก Lot)</label><div class="input-group"><input id="healthPhysicalQty" type="number" min="0" step="any" class="form-control" value="\${Number(result.evidence.balance_qty)}"><button id="healthPhysicalDryRun" type="button" class="btn btn-primary">คำนวณวิธีแก้จากยอดนี้</button></div><div class="form-text">กรอกจำนวนที่เจ้าหน้าที่ตรวจนับได้จริง ระบบจะยังไม่แก้ข้อมูลจนกว่าจะผ่าน Dry-run และยืนยันอีกครั้ง</div></div></div>` : ''}
        \${result.allowed && response.can_repair ? '<div class="mt-3"><label for="healthRepairReason" class="form-label fw-semibold">เหตุผลและหลักฐานประกอบ</label><textarea id="healthRepairReason" class="form-control" rows="3" minlength="10" placeholder="เช่น ตรวจนับจริงได้ 3 หน่วย และตรวจสอบยอดราย Lot แล้ว..." aria-describedby="healthRepairHelp"></textarea><div id="healthRepairHelp" class="form-text">อย่างน้อย 10 ตัวอักษร ข้อมูลนี้จะถูกบันทึกในประวัติการซ่อม</div><div class="invalid-feedback">กรุณาระบุเหตุผลอย่างน้อย 10 ตัวอักษร</div></div>' : ''}`;
    if (canEnterPhysicalCount) {
        document.getElementById('healthPhysicalDryRun').addEventListener('click', async function () {
            const input = document.getElementById('healthPhysicalQty');
            const qty = Number(input.value);
            if (!Number.isFinite(qty) || qty < 0) { input.classList.add('is-invalid'); input.focus(); return; }
            this.disabled = true; this.textContent = 'กำลังคำนวณ...';
            const countedPayload = {...payload, physical_qty: qty};
            try {
                const countedResponse = await healthPost(healthDryRunUrl, countedPayload);
                renderHealthDryRun(row, countedPayload, countedResponse, body, repairButton);
            } catch (error) {
                this.disabled = false; this.textContent = 'คำนวณวิธีแก้จากยอดนี้';
                input.insertAdjacentHTML('afterend', `<div class="text-danger small mt-2">\${healthEscape(error.message)}</div>`);
            }
        });
    }
    if (result.allowed && response.can_repair) {
        activeRepair = {...payload, fingerprint: result.plan.fingerprint};
        repairButton.classList.remove('d-none');
        const reason = document.getElementById('healthRepairReason');
        reason.addEventListener('input', () => { repairButton.disabled = reason.value.trim().length < 10; reason.classList.toggle('is-invalid', reason.value.length > 0 && reason.value.trim().length < 10); });
    }
};
document.querySelectorAll('.health-detail').forEach(function (button) {
    button.addEventListener('click', async function () {
        const row = JSON.parse(this.dataset.row || '{}');
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('healthDetailModal'));
        const body = document.getElementById('healthDetailBody');
        const repairButton = document.getElementById('healthRepairButton');
        activeRepair = null;
        repairButton.classList.add('d-none'); repairButton.disabled = true;
        body.innerHTML = '<div class="placeholder-glow" aria-label="กำลังคำนวณ Dry-run"><span class="placeholder col-7 mb-3"></span><span class="placeholder col-12 mb-2"></span><span class="placeholder col-10"></span></div>';
        modal.show();
        try {
            const hasHistoryOnlyEdit = Array.isArray(row.issues) && row.issues.includes('history_only_edit');
            // ปัญหาประวัติเป็นยอดรวมระดับสินค้า: หากกดมาจาก Lot ให้พาไปแผนรวมทุก Lot อัตโนมัติ
            const effectiveScope = row.scope === 'lot' && hasHistoryOnlyEdit ? 'item' : row.scope;
            let payload = {warehouse_id: row.warehouse_id, item_code: row.item_code, scope: effectiveScope, lot_number: effectiveScope === 'item' ? '' : row.lot_number};
            let response = await healthPost(healthDryRunUrl, payload);
            if (row.scope === 'lot' && !(response.result || {}).allowed) {
                payload = {warehouse_id: row.warehouse_id, item_code: row.item_code, scope: 'item', lot_number: ''};
                response = await healthPost(healthDryRunUrl, payload);
            }
            renderHealthDryRun(row, payload, response, body, repairButton);
        } catch (error) {
            body.innerHTML = `<div class="alert alert-danger mb-0"><strong>ไม่สามารถคำนวณ Dry-run</strong><div class="small mt-1">\${healthEscape(error.message)}</div></div>`;
        }
    });
});
document.getElementById('healthRepairButton').addEventListener('click', async function () {
    if (!activeRepair) return;
    const reason = document.getElementById('healthRepairReason');
    if (!reason || reason.value.trim().length < 10) { reason?.classList.add('is-invalid'); reason?.focus(); return; }
    this.disabled = true; this.textContent = 'กำลังตรวจและซ่อม...';
    try {
        const response = await healthPost(healthRepairUrl, {...activeRepair, reason: reason.value.trim()});
        document.getElementById('healthDetailBody').innerHTML = `<div class="alert alert-success mb-0"><strong>ซ่อมและตรวจสอบยอดสำเร็จ</strong><div class="small mt-1">เลขที่บันทึกการตรวจสอบ #\${Number(response.audit_id)}</div></div>`;
        this.classList.add('d-none'); activeRepair = null;
        setTimeout(() => window.location.reload(), 1200);
    } catch (error) {
        this.disabled = false; this.textContent = 'ยืนยันซ่อมยอด';
        const errorBox = document.createElement('div'); errorBox.className = 'alert alert-danger mt-3 mb-0'; errorBox.textContent = error.message;
        document.getElementById('healthDetailBody').prepend(errorBox);
    }
});
JS);
?>
