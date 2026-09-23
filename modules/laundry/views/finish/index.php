<?php

use app\components\AppHelper;
use app\components\ThaiDateHelper;
use app\widgets\datepicker\DatepickerThai;
use kartik\widgets\Select2;
use yii\helpers\Html;

/** @var string $date */
/** @var array $items */
/** @var array $itemGroups [[name, items], ...] ตามหมวดผ้า */
/** @var array $externalOptions id => ชื่อหน่วยงานภายนอก */
/** @var array $dryBatches */
/** @var array $finishes */
/** @var array $staffNames */
/** @var int $cleanTotal */
$this->title = 'นับ–รีด–QC';
$canManage = Yii::$app->user->can('laundry.manage');
// แหล่งที่มาที่เลือกไว้ (คงแท็บหลังบันทึก)
$source = Yii::$app->request->get('source') === 'EXTERNAL' ? 'EXTERNAL' : 'DRY';
$batchOptions = [];
foreach ($dryBatches as $b) {
    $batchOptions[$b['id']] = ($b['batch_no'] ?? '#' . $b['id']) . ' · ' . ($b['code'] ?: '') . ($b['output_kg'] !== null ? ' · ออก ' . number_format((float) $b['output_kg'], 0) : ' · เข้า ' . number_format((float) $b['input_kg'], 0)) . ' กก.';
}
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => 'finish']) ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 fw-bold mb-1"><i class="bi bi-ui-checks me-2"></i>นับ–รีด–QC <span class="text-body-secondary fs-6 fw-normal">ประจำวันที่ <?= Html::encode(ThaiDateHelper::formatThaiDate($date)) ?></span></h1>
            <div class="text-body-secondary small">นับผ้าสะอาดหลังอบ หรือรับผ้าจากหน่วยงานภายนอก (รายประเภท) → บวกเข้าคลังหลัก</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge text-bg-primary rounded-pill fs-6">คลังหลักคงเหลือ <?= number_format($cleanTotal) ?> ชิ้น</span>
            <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex align-items-center']) ?>
                <?= DatepickerThai::widget(['name' => 'date', 'value' => AppHelper::convertToThai($date), 'options' => ['class' => 'form-control form-control-sm', 'style' => 'max-width:140px', 'autocomplete' => 'off', 'onchange' => 'this.form.submit()']]) ?>
            <?= Html::endForm() ?>
        </div>
    </div>

    <?php foreach (['error' => 'danger', 'success' => 'success'] as $k => $c): ?>
        <?php if (Yii::$app->session->hasFlash($k)): ?><div class="alert alert-<?= $c ?> d-flex align-items-center"><i class="bi bi-<?= $c === 'danger' ? 'exclamation-triangle' : 'check-circle' ?> me-2"></i><?= Html::encode(Yii::$app->session->getFlash($k)) ?></div><?php endif; ?>
    <?php endforeach; ?>

    <div class="row g-3">
        <!-- ฟอร์มนับเข้าคลังหลัก -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-body px-4 py-3"><h2 class="h6 fw-semibold mb-0"><i class="bi bi-plus-circle me-2"></i>นับผ้าสะอาดเข้าคลังหลัก</h2></div>
                <div class="card-body">
                    <?php if (!$canManage): ?>
                        <div class="text-body-secondary small">ดูอย่างเดียว</div>
                    <?php elseif (!$items): ?>
                        <div class="text-body-secondary small"><i class="bi bi-info-circle me-1"></i>ยังไม่มีประเภทผ้า — เพิ่มที่ <?= Html::a('ตั้งค่า → ประเภทผ้า', ['/laundry/setting/item'], ['class' => 'fw-semibold']) ?></div>
                    <?php else: ?>
                        <?= Html::beginForm(['save'], 'post', ['id' => 'finish-form']) ?>
                            <label class="form-label small">แหล่งที่มาของผ้า</label>
                            <div class="btn-group w-100 mb-3" role="group">
                                <input type="radio" class="btn-check" name="source_type" id="src_dry" value="DRY" <?= $source === 'DRY' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-primary" for="src_dry"><i class="bi bi-wind me-1"></i>หลังอบ (ภายใน)</label>
                                <input type="radio" class="btn-check" name="source_type" id="src_ext" value="EXTERNAL" <?= $source === 'EXTERNAL' ? 'checked' : '' ?>>
                                <label class="btn btn-outline-primary" for="src_ext"><i class="bi bi-truck me-1"></i>รับจากหน่วยงานภายนอก</label>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-8 src-dry"<?= $source === 'DRY' ? '' : ' style="display:none"' ?>>
                                    <label class="form-label small">อ้างอิงรอบอบ <span class="text-body-secondary">(ถ้ามี)</span></label>
                                    <?= Html::dropDownList('dry_batch_id', '', $batchOptions, ['prompt' => '— ไม่ระบุ —', 'class' => 'form-select form-select-sm']) ?>
                                </div>
                                <div class="col-8 src-ext"<?= $source === 'EXTERNAL' ? '' : ' style="display:none"' ?>>
                                    <label class="form-label small">หน่วยงานที่ส่งผ้ามา <span class="text-body-secondary">(เลือก หรือพิมพ์ชื่อใหม่)</span></label>
                                    <?= Select2::widget([
                                        'name' => 'external_source_id', 'data' => $externalOptions,
                                        'value' => $source === 'EXTERNAL' && count($externalOptions) === 1 ? array_key_first($externalOptions) : '',
                                        'options' => ['placeholder' => 'เช่น โรงพยาบาลเลย', 'id' => 'finish-external'],
                                        'size' => Select2::SMALL,
                                        'pluginOptions' => ['tags' => true, 'allowClear' => true, 'width' => '100%'],
                                    ]) ?>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small">เวลา</label>
                                    <?= Html::input('time', 'counted_time', date('H:i'), ['class' => 'form-control form-control-sm']) ?>
                                </div>
                            </div>
                            <?= Html::hiddenInput('counted_date', AppHelper::convertToThai($date)) ?>
                            <table class="table table-sm align-middle mb-3">
                                <thead class="table-light"><tr><th class="ps-2">ประเภทผ้า</th><th class="text-end pe-2" style="width:130px">จำนวน (ชิ้น)</th></tr></thead>
                                <tbody>
                                <?php foreach ($itemGroups as $g): ?>
                                    <?php if (count($itemGroups) > 1): ?>
                                        <tr><td colspan="2" class="ps-2 fw-semibold text-primary bg-body-tertiary small"><i class="bi bi-tag me-1"></i><?= Html::encode($g['name']) ?></td></tr>
                                    <?php endif; ?>
                                    <?php foreach ($g['items'] as $it): ?>
                                    <tr>
                                        <td class="ps-2"><?= Html::encode($it['item_name']) ?></td>
                                        <td class="pe-2"><?= Html::input('number', 'qty[' . $it['id'] . ']', '', ['class' => 'form-control form-control-sm text-end', 'min' => '0', 'step' => '1', 'inputmode' => 'numeric', 'placeholder' => '0']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?= Html::submitButton('<i class="bi bi-save me-1"></i>บันทึกเข้าคลังหลัก', ['class' => 'btn btn-primary w-100']) ?>
                        <?= Html::endForm() ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- รายการนับหลังอบวันนี้ -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-body px-4 py-3"><h2 class="h6 fw-semibold mb-0"><i class="bi bi-list-ul me-2"></i>รายการนับเข้าคลังวันนี้</h2></div>
                <div class="card-body p-0"><div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light"><tr><th class="ps-4">เลขที่</th><th>แหล่งที่มา</th><th>วันที่</th><th>เวลา</th><th>ผู้นับ</th><th class="text-end">ประเภท</th><th class="text-end pe-4">รวม (ชิ้น)</th></tr></thead>
                        <tbody>
                        <?php foreach ($finishes as $f): ?>
                            <tr>
                                <td class="ps-4 fw-semibold"><?= Html::encode($f['finish_no'] ?: '#' . $f['id']) ?></td>
                                <td class="small">
                                    <?php if ($f['source_type'] === 'EXTERNAL'): ?>
                                        <span class="badge text-bg-warning"><i class="bi bi-truck me-1"></i>ภายนอก</span> <?= Html::encode($f['external_name'] ?: '—') ?>
                                    <?php else: ?>
                                        <span class="badge text-bg-light border">หลังอบ</span> <?= Html::encode($f['batch_no'] ?: '') ?>
                                    <?php endif; ?>
                                </td>
                                <td class="small"><?= Html::encode(ThaiDateHelper::formatThaiDate($f['counted_at'])) ?></td>
                                <td><?= date('H:i', strtotime($f['counted_at'])) ?></td>
                                <td class="text-body-secondary small"><?= Html::encode($staffNames[$f['created_by']] ?? '') ?></td>
                                <td class="text-end"><?= (int) $f['types'] ?></td>
                                <td class="text-end pe-4 fw-semibold"><?= number_format((int) $f['total']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$finishes): ?><tr><td colspan="7" class="text-center text-body-secondary py-5"><i class="bi bi-inbox fs-3 d-block mb-2"></i>ยังไม่มีการนับเข้าคลังวันนี้</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div></div>
            </div>
        </div>
    </div>
</div>
<?php
// สลับช่องตามแหล่งที่มา: หลังอบ → อ้างอิงรอบอบ / ภายนอก → หน่วยงานที่ส่งผ้ามา
$this->registerJs(<<<'JS'
document.querySelectorAll('#finish-form input[name="source_type"]').forEach(function (r) {
  r.addEventListener('change', function () {
    var ext = this.value === 'EXTERNAL';
    document.querySelectorAll('#finish-form .src-dry').forEach(function (el) { el.style.display = ext ? 'none' : ''; });
    document.querySelectorAll('#finish-form .src-ext').forEach(function (el) { el.style.display = ext ? '' : 'none'; });
  });
});
JS);
?>
