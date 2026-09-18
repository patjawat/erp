<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var string $stage */
/** @var array $machines */
/** @var array $summaryByAsset  asset_id => [times, in_kg, out_kg] */
/** @var array $runByAsset       asset_id => running round */
/** @var array $pendingRecoveries */
$isWash = ($stage ?? 'WASH') === 'WASH';
$pageIcon = $isWash ? 'bi-droplet-half' : 'bi-wind';
$pageTone = $isWash ? 'primary' : 'warning';
$this->title = $isWash ? 'ซักผ้า' : 'อบผ้า';
$canManage = Yii::$app->user->can('laundry.manage');
$canApprove = Yii::$app->user->can('laundry.approve');
$classLabel = static fn($c) => $c === 'INFECTIOUS' ? 'ผ้าติดเชื้อ' : 'ผ้าเปื้อน';
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => $isWash ? 'wash' : 'dry']) ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 fw-bold mb-1"><i class="bi <?= $pageIcon ?> me-2 text-<?= $pageTone ?>"></i><?= Html::encode($this->title) ?></h1>
            <div class="text-body-secondary">แตะ<?= $isWash ? 'เครื่องซัก' : 'เครื่องอบ' ?>เพื่อเริ่มรอบ — สรุปงานวันนี้ต่อเครื่อง</div>
        </div>
        <?= Html::a('<i class="bi bi-clock-history me-1"></i>ประวัติรอบ', ['history', 'stage' => $stage], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
    <?php foreach (['error' => 'danger', 'success' => 'success'] as $key => $class): ?>
        <?php if (Yii::$app->session->hasFlash($key)): ?><div class="alert alert-<?= $class ?> d-flex align-items-center"><i class="bi bi-<?= $class === 'danger' ? 'exclamation-triangle' : 'check-circle' ?> me-2"></i><?= Html::encode(Yii::$app->session->getFlash($key)) ?></div><?php endif; ?>
    <?php endforeach; ?>

    <?php if (!$machines): ?>
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body text-center text-body-secondary py-5">
            <i class="bi bi-cpu fs-1 d-block mb-2"></i>ยังไม่มี<?= $isWash ? 'เครื่องซัก' : 'เครื่องอบ' ?> — เพิ่มที่ <?= Html::a('ตั้งค่า → เครื่องซัก–อบ', ['/laundry/setting/machine'], ['class' => 'fw-semibold']) ?>
        </div></div>
    <?php else: ?>
        <div class="row g-4">
        <?php foreach ($machines as $idx => $m): $aid = $m['asset_id']; $run = $runByAsset[$aid] ?? null; $s = $summaryByAsset[$aid] ?? null; ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100" style="border-top:4px solid var(--bs-<?= $pageTone ?>) !important">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-<?= $pageTone ?> text-white flex-shrink-0" style="width:56px;height:56px">
                                <i class="bi <?= $pageIcon ?>" style="font-size:1.8rem"></i>
                            </span>
                            <div class="flex-grow-1" style="min-width:0">
                                <div class="fw-bold fs-5"><?= $isWash ? 'เครื่องซัก' : 'เครื่องอบ' ?> <?= $idx + 1 ?></div>
                                <div class="small text-body-secondary text-truncate"><?= Html::encode($m['asset_code'] ?: '#' . $aid) ?> · <?= number_format((float) $m['capacity_kg'], 0) ?> กก.</div>
                            </div>
                            <?php if (!$run && $canManage): ?>
                                <button type="button" class="btn btn-<?= $pageTone ?> rounded-circle d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:46px;height:46px"
                                    title="เริ่มรอบ" data-bs-toggle="modal" data-bs-target="#startModal"
                                    data-asset="<?= $aid ?>" data-stage="<?= $stage ?>" data-cap="<?= (float) $m['capacity_kg'] ?>"
                                    data-name="<?= Html::encode(($isWash ? 'เครื่องซัก' : 'เครื่องอบ') . ' ' . ($idx + 1)) ?>">
                                    <i class="bi bi-play-fill fs-4"></i>
                                </button>
                            <?php elseif ($run): ?>
                                <span class="badge text-bg-warning flex-shrink-0"><i class="bi bi-arrow-repeat me-1"></i>กำลังทำงาน</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($run): ?>
                            <div class="alert alert-warning py-2 px-3 mb-2 small">
                                <?= Html::encode($classLabel($run['linen_class'])) ?> · เข้า <?= number_format((float) $run['input_kg'], 1) ?> กก. · เริ่ม <?= date('H:i', strtotime($run['started_at'])) ?>
                            </div>
                            <?php if ($canManage): ?>
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <?= Html::beginForm(['finish', 'id' => $run['id']], 'post', ['class' => 'd-flex gap-1 flex-grow-1']) ?>
                                        <?= Html::input('number', 'output_kg', '', ['class' => 'form-control form-control-sm', 'step' => '0.001', 'min' => '0.001', 'placeholder' => 'ออก กก.', 'required' => true]) ?>
                                        <?= Html::submitButton('<i class="bi bi-check-lg"></i> ปิดรอบ', ['class' => 'btn btn-sm btn-success text-nowrap']) ?>
                                    <?= Html::endForm() ?>
                                    <?= Html::beginForm(['abort', 'id' => $run['id']], 'post', ['class' => 'd-flex gap-1']) ?>
                                        <?= Html::textInput('reason', '', ['class' => 'form-control form-control-sm', 'style' => 'width:90px', 'placeholder' => 'เหตุหยุด', 'minlength' => 5, 'required' => true]) ?>
                                        <?= Html::submitButton('<i class="bi bi-stop-fill"></i>', ['class' => 'btn btn-sm btn-outline-danger', 'data-confirm' => 'ยืนยันหยุดรอบ?']) ?>
                                    <?= Html::endForm() ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top small">
                            <span class="text-body-secondary">วันนี้ <span class="fw-semibold text-body"><?= (int) ($s['times'] ?? 0) ?></span> รอบ · เข้า <?= number_format((float) ($s['in_kg'] ?? 0), 0) ?> / ออก <?= number_format((float) ($s['out_kg'] ?? 0), 0) ?> กก.</span>
                            <a href="<?= Url::to(['history', 'stage' => $stage, 'asset_id' => $aid]) ?>" class="link-secondary text-nowrap ms-2"><i class="bi bi-clock-history"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($pendingRecoveries): ?>
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mt-3">
            <div class="card-header bg-body px-4 py-3"><h2 class="h6 fw-semibold mb-0"><i class="bi bi-clipboard-check me-2"></i>ผลตรวจผ้าจากรอบที่หยุด (รออนุมัติ)</h2></div>
            <div class="card-body p-0"><div class="table-responsive"><table class="table align-middle mb-0">
                <thead class="table-light"><tr><th class="ps-4">รอบเดิม</th><th>ผลตรวจ</th><th class="text-end">ชั่งใหม่</th><th>หลักฐาน</th><th class="pe-4">ดำเนินการ</th></tr></thead>
                <tbody>
                <?php foreach ($pendingRecoveries as $r): ?>
                    <tr>
                        <td class="ps-4 fw-semibold"><?= Html::encode($r['batch_no']) ?> <span class="small text-body-secondary">(<?= number_format((float) $r['input_kg'], 1) ?> กก.)</span></td>
                        <td><?= $r['outcome'] === 'REPROCESS' ? 'นำกลับเข้ารอบ' : 'ใช้ต่อไม่ได้' ?></td>
                        <td class="text-end"><?= number_format((float) $r['measured_kg'], 1) ?> กก.</td>
                        <td><?= Html::encode($r['evidence']) ?></td>
                        <td class="pe-4">
                            <?php if ($canApprove && (int) $r['created_by'] !== (int) Yii::$app->user->id): ?>
                                <?= Html::beginForm(['approve-recovery', 'id' => $r['id']], 'post') ?>
                                    <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i>อนุมัติ', ['class' => 'btn btn-sm btn-outline-success', 'data-confirm' => 'ยืนยันผลตรวจ?']) ?>
                                <?= Html::endForm() ?>
                            <?php else: ?>
                                <span class="badge text-bg-warning">รออนุมัติ</span>
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
