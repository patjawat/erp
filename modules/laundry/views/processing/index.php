<?php

use app\components\ThaiDateHelper;
use yii\helpers\Html;

/** @var string $stage */
/** @var array $machines */
/** @var array $batchesByMachine  asset_id => rounds[] */
/** @var array $runByAsset         asset_id => running round */
/** @var array $recoveries */
/** @var array $recoveryByBatch    aborted_batch_id => recovery */
$isWash = ($stage ?? 'WASH') === 'WASH';
$pageIcon = $isWash ? 'bi-droplet-half' : 'bi-wind';
$pageTone = $isWash ? 'primary' : 'warning';
$this->title = $isWash ? 'ซักผ้า' : 'อบผ้า';
$canManage = Yii::$app->user->can('laundry.manage');
$canApprove = Yii::$app->user->can('laundry.approve');
$fmtTime = static function ($dt) {
    return $dt ? ThaiDateHelper::formatThaiDate($dt) . ' ' . date('H:i', strtotime($dt)) : '—';
};
$classLabel = static fn($c) => $c === 'INFECTIOUS' ? 'ผ้าติดเชื้อ' : 'ผ้าเปื้อน';
$pendingRecoveries = array_filter($recoveries, static fn($r) => $r['status'] === 'PENDING');
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => $isWash ? 'wash' : 'dry']) ?>
    <div class="mb-3">
        <h1 class="h4 fw-bold mb-1"><i class="bi <?= $pageIcon ?> me-2 text-<?= $pageTone ?>"></i><?= Html::encode($this->title) ?></h1>
        <div class="text-body-secondary">แตะ<?= $isWash ? 'เครื่องซัก' : 'เครื่องอบ' ?>เพื่อเริ่มรอบ — แต่ละเครื่องแสดงสถานะและรอบล่าสุดในบล็อกเดียวกัน</div>
    </div>
    <?php foreach (['error' => 'danger', 'success' => 'success'] as $key => $class): ?>
        <?php if (Yii::$app->session->hasFlash($key)): ?><div class="alert alert-<?= $class ?> d-flex align-items-center"><i class="bi bi-<?= $class === 'danger' ? 'exclamation-triangle' : 'check-circle' ?> me-2"></i><?= Html::encode(Yii::$app->session->getFlash($key)) ?></div><?php endif; ?>
    <?php endforeach; ?>

    <?php if (!$machines): ?>
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body text-center text-body-secondary py-5">
            <i class="bi bi-cpu fs-1 d-block mb-2"></i>ยังไม่มี<?= $isWash ? 'เครื่องซัก' : 'เครื่องอบ' ?> — เพิ่มที่ <?= Html::a('ตั้งค่า → เครื่องซัก–อบ', ['/laundry/setting/machine'], ['class' => 'fw-semibold']) ?>
        </div></div>
    <?php else: ?>
        <div class="row g-3">
        <?php foreach ($machines as $idx => $m): $aid = $m['asset_id']; $run = $runByAsset[$aid] ?? null; $rounds = $batchesByMachine[$aid] ?? []; ?>
            <div class="col-12 col-xl-6">
                <div class="card border-0 shadow-sm rounded-4 h-100" style="border-top:4px solid var(--bs-<?= $pageTone ?>) !important">
                    <div class="card-body">
                        <!-- หัวเครื่อง -->
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-<?= $pageTone ?> text-white flex-shrink-0" style="width:60px;height:60px">
                                <i class="bi <?= $pageIcon ?>" style="font-size:2rem"></i>
                            </span>
                            <div class="flex-grow-1" style="min-width:0">
                                <div class="fw-bold fs-5"><?= $isWash ? 'เครื่องซัก' : 'เครื่องอบ' ?> <?= $idx + 1 ?></div>
                                <div class="small text-body-secondary text-truncate"><?= Html::encode($m['asset_code'] ?: '#' . $aid) ?> · กำลัง <?= number_format((float) $m['capacity_kg'], 0) ?> กก.</div>
                            </div>
                            <?php if (!$run && $canManage): ?>
                                <button type="button" class="btn btn-<?= $pageTone ?> btn-lg" data-bs-toggle="modal" data-bs-target="#startModal"
                                    data-asset="<?= $aid ?>" data-stage="<?= $stage ?>" data-cap="<?= (float) $m['capacity_kg'] ?>"
                                    data-name="<?= Html::encode(($isWash ? 'เครื่องซัก' : 'เครื่องอบ') . ' ' . ($idx + 1)) ?>">
                                    <i class="bi bi-play-fill me-1"></i>เริ่มรอบ
                                </button>
                            <?php elseif (!$run): ?>
                                <span class="badge text-bg-success">พร้อม</span>
                            <?php endif; ?>
                        </div>

                        <!-- รอบที่กำลังทำงาน -->
                        <?php if ($run): ?>
                            <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                <div>
                                    <span class="badge text-bg-warning me-1"><i class="bi bi-arrow-repeat me-1"></i>กำลังทำงาน</span>
                                    <?= Html::encode($classLabel($run['linen_class'])) ?> · เข้า <?= number_format((float) $run['input_kg'], 1) ?> กก. · เริ่ม <?= date('H:i', strtotime($run['started_at'])) ?>
                                </div>
                                <?php if ($canManage): ?>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?= Html::beginForm(['finish', 'id' => $run['id']], 'post', ['class' => 'd-flex gap-1']) ?>
                                            <?= Html::input('number', 'output_kg', '', ['class' => 'form-control form-control-sm', 'style' => 'width:110px', 'step' => '0.001', 'min' => '0.001', 'placeholder' => 'ออก กก.', 'required' => true]) ?>
                                            <?= Html::submitButton('<i class="bi bi-check-lg"></i> ปิดรอบ', ['class' => 'btn btn-sm btn-success']) ?>
                                        <?= Html::endForm() ?>
                                        <?= Html::beginForm(['abort', 'id' => $run['id']], 'post', ['class' => 'd-flex gap-1']) ?>
                                            <?= Html::textInput('reason', '', ['class' => 'form-control form-control-sm', 'style' => 'width:120px', 'placeholder' => 'เหตุหยุด', 'minlength' => 5, 'required' => true]) ?>
                                            <?= Html::submitButton('<i class="bi bi-stop-fill"></i>', ['class' => 'btn btn-sm btn-outline-danger', 'data-confirm' => 'ยืนยันหยุดรอบ?']) ?>
                                        <?= Html::endForm() ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- รอบล่าสุดของเครื่องนี้ -->
                        <?php $history = array_values(array_filter($rounds, static fn($b) => $b['status'] !== 'RUNNING')); $shown = array_slice($history, 0, 6); ?>
                        <div class="small text-body-secondary fw-semibold mb-1">รอบล่าสุด</div>
                        <?php if (!$shown): ?>
                            <div class="text-body-secondary small py-2"><i class="bi bi-inbox me-1"></i>ยังไม่มีรอบ</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <tbody>
                                    <?php foreach ($shown as $b): ?>
                                        <tr>
                                            <td class="text-body-secondary" style="white-space:nowrap"><?= Html::encode($classLabel($b['linen_class'])) ?><div class="small"><?= date('d/m H:i', strtotime($b['started_at'])) ?><?= $b['ended_at'] ? '–' . date('H:i', strtotime($b['ended_at'])) : '' ?></div></td>
                                            <td class="text-end" style="white-space:nowrap"><?= number_format((float) $b['input_kg'], 1) ?><?php if ($b['output_kg'] !== null): ?> → <span class="fw-semibold"><?= number_format((float) $b['output_kg'], 1) ?></span><?php endif; ?> <span class="text-body-secondary">กก.</span></td>
                                            <td class="text-end">
                                                <?php if ($b['status'] === 'COMPLETED'): ?><span class="badge text-bg-success">เสร็จ</span>
                                                <?php elseif ($b['status'] === 'ABORTED'): ?><span class="badge text-bg-danger">หยุด</span>
                                                <?php else: ?><span class="badge text-bg-secondary"><?= Html::encode($b['status']) ?></span><?php endif; ?>
                                                <?php if ($b['status'] === 'ABORTED' && $canManage && !isset($recoveryByBatch[$b['id']])): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-primary ms-1" data-bs-toggle="collapse" data-bs-target="#rec<?= $b['id'] ?>">ผลตรวจ</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php if ($b['status'] === 'ABORTED' && $canManage && !isset($recoveryByBatch[$b['id']])): ?>
                                            <tr class="collapse" id="rec<?= $b['id'] ?>"><td colspan="3">
                                                <?= Html::beginForm(['request-recovery', 'id' => $b['id']], 'post', ['class' => 'd-flex flex-wrap gap-1 py-1']) ?>
                                                    <?= Html::dropDownList('outcome', 'REPROCESS', ['REPROCESS' => 'นำกลับเข้ารอบ', 'DISCARD' => 'ใช้ต่อไม่ได้'], ['class' => 'form-select form-select-sm w-auto']) ?>
                                                    <?= Html::input('number', 'measured_kg', '', ['class' => 'form-control form-control-sm w-auto', 'step' => '0.001', 'min' => 0, 'placeholder' => 'ชั่งใหม่ กก.']) ?>
                                                    <?= Html::textInput('evidence', '', ['class' => 'form-control form-control-sm w-auto', 'placeholder' => 'หลักฐาน/สภาพผ้า', 'minlength' => 5, 'required' => true]) ?>
                                                    <?= Html::submitButton('ส่งอนุมัติ', ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                                <?= Html::endForm() ?>
                                            </td></tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if (count($history) > 6): ?>
                                <div class="small text-body-secondary mt-1">แสดง 6 รอบล่าสุด (ทั้งหมด <?= count($history) ?> รอบ)</div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- รอตรวจผ้ากู้คืน (ผู้อนุมัติ) -->
    <?php if ($pendingRecoveries || ($recoveries && $canApprove)): ?>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mt-3">
            <div class="card-header bg-body px-4 py-3"><h2 class="h6 fw-semibold mb-0"><i class="bi bi-clipboard-check me-2"></i>ผลตรวจผ้าจากรอบที่หยุด</h2></div>
            <div class="card-body p-0"><div class="table-responsive"><table class="table align-middle mb-0">
                <thead class="table-light"><tr><th class="ps-4">รอบเดิม</th><th>ผลตรวจ</th><th class="text-end">ชั่งใหม่</th><th>หลักฐาน</th><th class="pe-4">สถานะ / ดำเนินการ</th></tr></thead>
                <tbody>
                <?php foreach ($recoveries as $r): ?>
                    <tr>
                        <td class="ps-4 fw-semibold"><?= Html::encode($r['batch_no']) ?> <span class="small text-body-secondary">(<?= number_format((float) $r['input_kg'], 1) ?> กก.)</span></td>
                        <td><?= $r['outcome'] === 'REPROCESS' ? 'นำกลับเข้ารอบ' : 'ใช้ต่อไม่ได้' ?></td>
                        <td class="text-end"><?= number_format((float) $r['measured_kg'], 1) ?> กก.</td>
                        <td><?= Html::encode($r['evidence']) ?></td>
                        <td class="pe-4">
                            <?php if ($r['status'] === 'PENDING' && $canApprove && (int) $r['created_by'] !== (int) Yii::$app->user->id): ?>
                                <?= Html::beginForm(['approve-recovery', 'id' => $r['id']], 'post') ?>
                                    <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i>อนุมัติ', ['class' => 'btn btn-sm btn-outline-success', 'data-confirm' => 'ยืนยันผลตรวจ?']) ?>
                                <?= Html::endForm() ?>
                            <?php else: ?>
                                <span class="badge <?= $r['status'] === 'APPROVED' ? 'text-bg-success' : 'text-bg-warning' ?>"><?= $r['status'] === 'APPROVED' ? 'อนุมัติแล้ว' : 'รออนุมัติ' ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div></div>
        </div>
    <?php endif; ?>
</div>

<?php if ($canManage): ?>
<div class="modal fade" id="startModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <?= Html::beginForm(['start'], 'post') ?>
            <?= Html::hiddenInput('asset_id', '', ['id' => 'start_asset']) ?>
            <?= Html::hiddenInput('stage', $stage, ['id' => 'start_stage']) ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-play-circle me-2"></i>เริ่มรอบ — <span id="start_machine" class="text-<?= $pageTone ?>"></span></h5>
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
                <?= Html::submitButton('<i class="bi bi-play-fill me-1"></i>เริ่มรอบ', ['class' => 'btn btn-' . $pageTone]) ?>
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
    if (c.getAttribute('data-stage')) document.getElementById('start_stage').value = c.getAttribute('data-stage');
    document.getElementById('start_machine').textContent = c.getAttribute('data-name') || '';
    var cap = c.getAttribute('data-cap') || '';
    var kg = document.getElementById('start_kg'); kg.value = ''; kg.removeAttribute('max');
    document.getElementById('start_caphint').textContent = cap ? ('กำลังเครื่อง ' + cap + ' กก.') : '';
  });
}
JS);
?>
<?php endif; ?>
