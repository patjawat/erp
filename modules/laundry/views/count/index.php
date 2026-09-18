<?php

use app\components\AppHelper;
use app\components\ThaiDateHelper;
use app\modules\laundry\models\LaundryUnit;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;

/** @var string $date */
/** @var LaundryUnit[] $units */
/** @var array $names   tree_id => name */
/** @var array $items   [{id, item_name}] */
/** @var array $summary tree_id => [times, latest] */
$this->title = 'ตรวจนับผ้า';
$canManage = Yii::$app->user->can('laundry.manage');
$tones = ['success', 'primary', 'info', 'warning', 'danger', 'secondary'];
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => 'inspect']) ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-clipboard-check me-2"></i>ตรวจนับผ้า <span class="text-body-secondary fs-6 fw-normal">ประจำวันที่ <?= Html::encode(ThaiDateHelper::formatThaiDate($date)) ?></span></h1>
        <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex align-items-center gap-2']) ?>
            <label class="text-body-secondary small mb-0 text-nowrap">เลือกวันที่</label>
            <?= DatepickerThai::widget(['name' => 'date', 'value' => AppHelper::convertToThai($date), 'options' => ['class' => 'form-control form-control-sm', 'style' => 'max-width:150px', 'autocomplete' => 'off', 'onchange' => 'this.form.submit()']]) ?>
        <?= Html::endForm() ?>
    </div>
    <div class="text-body-secondary small mb-3"><i class="bi bi-info-circle me-1"></i>ไปนับผ้าสะอาดคงเหลือที่ตู้ของหน่วยงาน แยกตามประเภท — ใช้อ้างอิงตอนจ่ายผ้า</div>

    <?php foreach (['error' => 'danger', 'success' => 'success'] as $k => $c): ?>
        <?php if (Yii::$app->session->hasFlash($k)): ?><div class="alert alert-<?= $c ?> d-flex align-items-center"><i class="bi bi-<?= $c === 'danger' ? 'exclamation-triangle' : 'check-circle' ?> me-2"></i><?= Html::encode(Yii::$app->session->getFlash($k)) ?></div><?php endif; ?>
    <?php endforeach; ?>

    <?php if (!$units): ?>
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body text-center text-body-secondary py-5">
            <i class="bi bi-diagram-3 fs-1 d-block mb-3"></i>ยังไม่มีหน่วยงาน — เพิ่มที่ <?= Html::a('ตั้งค่า → หน่วยงาน', ['/laundry/setting/unit'], ['class' => 'fw-semibold']) ?>
        </div></div>
    <?php elseif (!$items): ?>
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body text-center text-body-secondary py-5">
            <i class="bi bi-collection fs-1 d-block mb-3"></i>ยังไม่มีประเภทผ้า — เพิ่มที่ <?= Html::a('ตั้งค่า → ประเภทผ้า', ['/laundry/setting/item'], ['class' => 'fw-semibold']) ?> ก่อน
        </div></div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($units as $i => $u): $s = $summary[$u->tree_id] ?? null; $tone = $tones[$i % count($tones)]; ?>
                <div class="col-6 col-md-4 col-lg-3 col-xxl-2">
                    <?php
                    $attrs = [
                        'class' => 'card border-0 shadow-sm rounded-4 h-100 w-100',
                        'style' => 'background:var(--bs-' . $tone . '-bg-subtle,#f8f9fa);border-top:4px solid var(--bs-' . $tone . ',#0d6efd) !important',
                        'data-dept' => $u->tree_id, 'data-name' => $names[$u->tree_id] ?? ('#' . $u->tree_id), 'data-abbr' => $u->abbr ?: '',
                    ];
                    if ($canManage) { $attrs['type'] = 'button'; $attrs['data-bs-toggle'] = 'modal'; $attrs['data-bs-target'] = '#countModal'; }
                    $tag = $canManage ? 'button' : 'div';
                    ?>
                    <<?= $tag ?> <?= Html::renderTagAttributes($attrs) ?>>
                        <div class="card-body py-3 text-center">
                            <div class="fw-bold lh-1 mb-1" style="font-size:1.9rem;color:var(--bs-<?= $tone ?>)"><?= Html::encode($u->abbr ?: mb_substr($names[$u->tree_id] ?? '?', 0, 4)) ?></div>
                            <div class="small text-body-secondary text-truncate mb-2" title="<?= Html::encode($names[$u->tree_id] ?? '') ?>"><?= Html::encode($names[$u->tree_id] ?? ('#' . $u->tree_id)) ?></div>
                            <div class="small text-body-secondary">ตรวจนับ <span class="fw-semibold text-body"><?= (int) ($s['times'] ?? 0) ?></span> ครั้ง</div>
                            <div class="small text-body-secondary">ล่าสุด <?= number_format((int) ($s['latest'] ?? 0)) ?> ชิ้น</div>
                        </div>
                    </<?= $tag ?>>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php if ($canManage && $units && $items): ?>
<div class="modal fade" id="countModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4">
            <?= Html::beginForm(['save'], 'post') ?>
            <?= Html::hiddenInput('tree_id', '', ['id' => 'count_dept']) ?>
            <?= Html::hiddenInput('counted_date', AppHelper::convertToThai($date)) ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-clipboard-check me-2"></i>ตรวจนับผ้า — <span id="count_unit_name" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-2 small">
                    <span class="text-body-secondary"><i class="bi bi-info-circle me-1"></i>ลงจำนวนที่นับได้ (ชิ้น) แต่ละประเภท</span>
                    <div class="d-flex align-items-center gap-1">
                        <span class="text-body-secondary">เวลา</span>
                        <?= Html::input('time', 'counted_time', date('H:i'), ['class' => 'form-control form-control-sm', 'style' => 'width:110px']) ?>
                    </div>
                </div>
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light"><tr><th class="ps-2">ประเภทผ้า</th><th class="text-end pe-2" style="width:130px">จำนวน (ชิ้น)</th></tr></thead>
                    <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td class="ps-2"><?= Html::encode($it['item_name']) ?></td>
                            <td class="pe-2"><?= Html::input('number', 'qty[' . $it['id'] . ']', '', ['class' => 'form-control form-control-sm text-end count-qty', 'min' => '0', 'step' => '1', 'inputmode' => 'numeric', 'placeholder' => '0']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <?= Html::submitButton('<i class="bi bi-save me-1"></i>บันทึกผลนับ', ['class' => 'btn btn-primary']) ?>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>
<?php
$this->registerJs(<<<'JS'
var countModal = document.getElementById('countModal');
if (countModal) {
  countModal.addEventListener('show.bs.modal', function (e) {
    var c = e.relatedTarget; if (!c) return;
    document.getElementById('count_dept').value = c.getAttribute('data-dept') || '';
    var abbr = c.getAttribute('data-abbr'), name = c.getAttribute('data-name') || '';
    document.getElementById('count_unit_name').textContent = abbr ? (abbr + ' · ' + name) : name;
    countModal.querySelectorAll('.count-qty').forEach(function (el) { el.value = ''; });
  });
}
JS);
?>
<?php endif; ?>
