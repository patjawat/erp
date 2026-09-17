<?php

use yii\helpers\Html;

$this->title = 'รอบซัก–อบ';
$canManage = Yii::$app->user->can('laundry.manage');
$canApprove = Yii::$app->user->can('laundry.approve');
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => 'processing']) ?>
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <div><h1 class="h4 fw-bold mb-1"><i class="bi bi-moisture me-2"></i><?= Html::encode($this->title) ?></h1><div class="text-body-secondary">แตะเครื่องเพื่อเริ่มรอบ — ใส่กลุ่มผ้าและน้ำหนัก (กิโลกรัม)</div></div>
    </div>
    <?php foreach (['error' => 'danger', 'success' => 'success'] as $key => $class): ?>
        <?php if (Yii::$app->session->hasFlash($key)): ?><div class="alert alert-<?= $class ?> d-flex align-items-center"><i class="bi bi-<?= $class === 'danger' ? 'exclamation-triangle' : 'check-circle' ?> me-2"></i><?= Html::encode(Yii::$app->session->getFlash($key)) ?></div><?php endif; ?>
    <?php endforeach; ?>

    <?php
    $runningByAsset = [];
    foreach ($batches as $b) { if ($b['status'] === 'RUNNING') { $runningByAsset[$b['asset_id']] = $b; } }
    ?>
    <?php if ($canManage): ?>
        <?php if (!$machines): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-3"><div class="card-body text-center text-body-secondary py-4">
                <i class="bi bi-cpu fs-3 d-block mb-2"></i>ยังไม่มีเครื่อง — เพิ่มที่ <?= Html::a('ตั้งค่า → เครื่องซัก–อบ', ['/laundry/setting/machine'], ['class' => 'fw-semibold']) ?>
            </div></div>
        <?php else: ?>
            <div class="row g-3 mb-3">
                <?php foreach ($machines as $m): $isWash = $m['machine_type'] === 'WASH'; $tone = $isWash ? 'primary' : 'info'; $run = $runningByAsset[$m['asset_id']] ?? null; ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <?php
                        $attrs = [
                            'class' => 'card border-0 shadow-sm rounded-4 h-100 w-100 text-start',
                            'style' => 'background:var(--bs-' . $tone . '-bg-subtle,#f8f9fa);border-top:4px solid var(--bs-' . $tone . ',#0d6efd) !important',
                            'data-asset' => $m['asset_id'], 'data-stage' => $m['machine_type'],
                            'data-name' => ($m['asset_code'] ?: '#' . $m['asset_id']) . ' · ' . ($m['asset_name'] ?: ''),
                            'data-cap' => (float) $m['capacity_kg'],
                        ];
                        if (!$run) { $attrs['type'] = 'button'; $attrs['data-bs-toggle'] = 'modal'; $attrs['data-bs-target'] = '#startModal'; }
                        $tag = $run ? 'div' : 'button';
                        ?>
                        <<?= $tag ?> <?= Html::renderTagAttributes($attrs) ?>>
                            <div class="card-body py-3 text-center">
                                <i class="bi bi-<?= $isWash ? 'droplet' : 'wind' ?> fs-1" style="color:var(--bs-<?= $tone ?>)"></i>
                                <div class="fw-bold mt-1"><?= $isWash ? 'เครื่องซัก' : 'เครื่องอบ' ?></div>
                                <div class="small text-body-secondary text-truncate" title="<?= Html::encode($m['asset_name'] ?? '') ?>"><?= Html::encode($m['asset_code'] ?: '#' . $m['asset_id']) ?></div>
                                <div class="small text-body-secondary">กำลัง <?= number_format((float) $m['capacity_kg'], 0) ?> กก.</div>
                                <?php if ($run): ?>
                                    <span class="badge text-bg-warning mt-2"><i class="bi bi-arrow-repeat me-1"></i>กำลังทำงาน</span>
                                <?php else: ?>
                                    <span class="badge text-bg-success mt-2"><i class="bi bi-play-fill"></i> พร้อมเริ่ม</span>
                                <?php endif; ?>
                            </div>
                        </<?= $tag ?>>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-body px-4 py-3"><h5 class="fw-semibold mb-0">รอบเครื่องล่าสุด</h5></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th class="ps-4">เลขรอบ / เครื่อง</th><th>ขั้นตอน / ประเภทผ้า</th><th>เริ่ม–สิ้นสุด</th><th class="text-end">เข้า (กก.)</th><th class="text-end">ออก (กก.)</th><th>สถานะ / จัดการ</th></tr></thead>
            <tbody>
            <?php foreach ($batches as $batch): ?>
                <tr>
                    <td class="ps-4 fw-semibold"><?= Html::encode($batch['batch_no']) ?><div class="small text-muted"><?= Html::encode($batch['asset_code'] ?: '#' . $batch['asset_id']) ?></div></td>
                    <td><?= $batch['stage'] === 'WASH' ? 'ซัก' : 'อบ' ?> · <?= $batch['linen_class'] === 'INFECTIOUS' ? 'ผ้าติดเชื้อ' : 'ผ้าเปื้อน' ?></td>
                    <td class="small"><?= Html::encode($batch['started_at']) ?><br><?= Html::encode($batch['ended_at'] ?: 'กำลังทำงาน') ?></td>
                    <td class="text-end"><?= number_format((float) $batch['input_kg'], 3) ?></td>
                    <td class="text-end"><?= $batch['output_kg'] === null ? '—' : number_format((float) $batch['output_kg'], 3) ?></td>
                    <td>
                        <?php if ($batch['status'] === 'RUNNING' && $canManage): ?>
                            <?= Html::beginForm(['finish', 'id' => $batch['id']], 'post', ['class' => 'd-flex flex-wrap gap-1 mb-2']) ?>
                                <?= Html::input('number', 'output_kg', '', ['class' => 'form-control form-control-sm w-auto', 'step' => '0.001', 'min' => '0.001', 'placeholder' => 'ผลผลิต กก.', 'required' => true]) ?>
                                <?= Html::submitButton('ปิดรอบ', ['class' => 'btn btn-sm btn-success']) ?>
                            <?= Html::endForm() ?>
                            <?= Html::beginForm(['abort', 'id' => $batch['id']], 'post', ['class' => 'd-flex flex-wrap gap-1']) ?>
                                <?= Html::textInput('reason', '', ['class' => 'form-control form-control-sm w-auto', 'placeholder' => 'เหตุหยุดรอบ', 'required' => true, 'minlength' => 5]) ?>
                                <?= Html::submitButton('หยุดรอบ', ['class' => 'btn btn-sm btn-outline-danger', 'data-confirm' => 'ยืนยันหยุดรอบ? น้ำหนักต้นทางจะยังถูกกันไว้']) ?>
                            <?= Html::endForm() ?>
                        <?php else: ?>
                            <span class="badge <?= $batch['status'] === 'COMPLETED' ? 'bg-success' : ($batch['status'] === 'ABORTED' ? 'bg-danger' : 'bg-warning text-dark') ?>"><?= Html::encode($batch['status']) ?></span>
                            <?php if ($batch['note']): ?><div class="small text-muted"><?= Html::encode($batch['note']) ?></div><?php endif; ?>
                            <?php if ($batch['status'] === 'ABORTED' && $canManage && !isset($recoveryByBatch[$batch['id']])): ?>
                                <?= Html::beginForm(['request-recovery', 'id' => $batch['id']], 'post', ['class' => 'd-flex flex-wrap gap-1 mt-2']) ?>
                                    <?= Html::dropDownList('outcome', 'REPROCESS', ['REPROCESS' => 'นำกลับเข้ารอบ', 'DISCARD' => 'ใช้ต่อไม่ได้'], ['class' => 'form-select form-select-sm w-auto']) ?>
                                    <?= Html::input('number', 'measured_kg', '', ['class' => 'form-control form-control-sm w-auto', 'step' => '0.001', 'min' => 0, 'placeholder' => 'ชั่งใหม่ กก.']) ?>
                                    <?= Html::textInput('evidence', '', ['class' => 'form-control form-control-sm w-auto', 'placeholder' => 'หลักฐาน/สภาพผ้า', 'minlength' => 5, 'required' => true]) ?>
                                    <?= Html::submitButton('ส่งอนุมัติ', ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                <?= Html::endForm() ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$batches): ?><tr><td colspan="6" class="text-center text-muted py-4">ยังไม่มีรอบเครื่อง</td></tr><?php endif; ?>
            </tbody>
        </table></div></div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mt-3">
        <div class="card-header bg-body px-4 py-3"><h5 class="fw-semibold mb-0">ผลตรวจผ้าจากรอบเครื่องที่หยุด</h5></div>
        <div class="px-4 pt-3 small text-muted">การระบุว่าใช้ต่อไม่ได้เป็นผลด้านน้ำหนักรอบเครื่องเท่านั้น ไม่ตัดยอดผ้าเป็นชิ้น หากต้องตัดผ้าให้ทำรายการสูญเสียและอนุมัติในบัญชีผ้าแยกต่างหาก</div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table align-middle mb-0">
            <thead><tr><th class="ps-4">รอบเดิม</th><th>ผลตรวจ</th><th class="text-end">น้ำหนักชั่งใหม่</th><th>หลักฐาน</th><th>สถานะ / ดำเนินการ</th></tr></thead>
            <tbody>
            <?php foreach ($recoveries as $recovery): ?>
                <tr>
                    <td class="ps-4 fw-semibold"><?= Html::encode($recovery['batch_no']) ?> <span class="small text-muted">(<?= number_format((float) $recovery['input_kg'], 3) ?> กก. เดิม)</span></td>
                    <td><?= $recovery['outcome'] === 'REPROCESS' ? 'นำกลับเข้ารอบ' : 'ใช้ต่อไม่ได้' ?></td>
                    <td class="text-end"><?= number_format((float) $recovery['measured_kg'], 3) ?> กก.</td>
                    <td><?= Html::encode($recovery['evidence']) ?></td>
                    <td>
                        <?php if ($recovery['status'] === 'PENDING' && $canApprove && (int) $recovery['created_by'] !== (int) Yii::$app->user->id): ?>
                            <?= Html::beginForm(['approve-recovery', 'id' => $recovery['id']], 'post') ?>
                                <?= Html::submitButton('อนุมัติผลตรวจ', ['class' => 'btn btn-sm btn-outline-success', 'data-confirm' => 'ยืนยันผลตรวจและน้ำหนักชั่งใหม่?']) ?>
                            <?= Html::endForm() ?>
                        <?php else: ?>
                            <span class="badge <?= $recovery['status'] === 'APPROVED' ? 'bg-success' : 'bg-warning text-dark' ?>"><?= Html::encode($recovery['status']) ?></span>
                            <?php if ($recovery['status'] === 'APPROVED' && $recovery['outcome'] === 'REPROCESS' && $canManage): ?>
                                <?= Html::a('เริ่มรอบใหม่', ['new', 'stage' => $recovery['stage'], 'linenClass' => $recovery['linen_class'], 'mode' => 'RECOVERY'], ['class' => 'btn btn-sm btn-outline-primary ms-1']) ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$recoveries): ?><tr><td colspan="5" class="text-center text-muted py-4">ยังไม่มีรอบที่ต้องตรวจผ้ากู้คืน</td></tr><?php endif; ?>
            </tbody>
        </table></div></div>
    </div>
</div>

<?php if ($canManage): ?>
<div class="modal fade" id="startModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <?= Html::beginForm(['start'], 'post') ?>
            <?= Html::hiddenInput('asset_id', '', ['id' => 'start_asset']) ?>
            <?= Html::hiddenInput('stage', '', ['id' => 'start_stage']) ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-play-circle me-2"></i>เริ่มรอบ — <span id="start_machine" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">กลุ่มผ้า</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="linen_class" id="lc_soiled" value="SOILED" checked>
                            <label class="btn btn-outline-secondary" for="lc_soiled"><i class="bi bi-droplet-half me-1"></i>ผ้าเปื้อน</label>
                            <input type="radio" class="btn-check" name="linen_class" id="lc_inf" value="INFECTIOUS">
                            <label class="btn btn-outline-danger" for="lc_inf"><i class="bi bi-exclamation-triangle me-1"></i>ผ้าติดเชื้อ</label>
                        </div>
                    </div>
                    <div class="col-7">
                        <label class="form-label">น้ำหนักเข้าเครื่อง (กก.)</label>
                        <?= Html::input('number', 'input_kg', '', ['id' => 'start_kg', 'class' => 'form-control form-control-lg', 'min' => '0.001', 'step' => '0.001', 'inputmode' => 'decimal', 'required' => true]) ?>
                        <div class="form-text" id="start_caphint"></div>
                    </div>
                    <div class="col-5">
                        <label class="form-label">โปรแกรม</label>
                        <?= Html::textInput('program', '', ['class' => 'form-control', 'maxlength' => 100, 'placeholder' => '(ถ้ามี)']) ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <?= Html::submitButton('<i class="bi bi-play-fill me-1"></i>เริ่มรอบ', ['class' => 'btn btn-primary']) ?>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>
<?php
$this->registerJs(<<<'JS'
var startModal = document.getElementById('startModal');
if (startModal) {
  startModal.addEventListener('show.bs.modal', function (e) {
    var c = e.relatedTarget; if (!c) return;
    document.getElementById('start_asset').value = c.getAttribute('data-asset') || '';
    document.getElementById('start_stage').value = c.getAttribute('data-stage') || '';
    document.getElementById('start_machine').textContent = c.getAttribute('data-name') || '';
    var cap = c.getAttribute('data-cap') || '';
    var kg = document.getElementById('start_kg');
    kg.value = ''; kg.setAttribute('max', cap);
    document.getElementById('start_caphint').textContent = cap ? ('ไม่เกินกำลังเครื่อง ' + cap + ' กก.') : '';
  });
}
JS);
?>
<?php endif; ?>
