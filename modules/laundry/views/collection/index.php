<?php

use app\components\AppHelper;
use app\components\ThaiDateHelper;
use app\modules\laundry\models\LaundryUnit;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;

/** @var string $date        Y-m-d */
/** @var LaundryUnit[] $units */
/** @var array $names        tree_id => name */
/** @var array $totals       department_id => [times, soiled, infectious] */
/** @var string $staffName */
$this->title = 'รับผ้า';
$canManage = Yii::$app->user->can('laundry.manage');
?>
<div class="container-fluid py-3">
    <?= $this->render('../_nav', ['active' => 'collection']) ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-bold mb-0"><i class="bi bi-basket3 me-2"></i>รับผ้า <span class="text-body-secondary fs-6 fw-normal">ประจำวันที่ <?= Html::encode(ThaiDateHelper::formatThaiDate($date)) ?></span></h1>
        <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex align-items-center gap-2']) ?>
            <label class="text-body-secondary small mb-0 text-nowrap">เลือกวันที่</label>
            <?= DatepickerThai::widget([
                'name' => 'date',
                'value' => AppHelper::convertToThai($date),
                'options' => ['id' => 'recv_date', 'class' => 'form-control form-control-sm', 'style' => 'max-width:150px', 'autocomplete' => 'off', 'onchange' => 'this.form.submit()'],
            ]) ?>
        <?= Html::endForm() ?>
    </div>

    <?php foreach (['error' => 'danger', 'success' => 'success'] as $k => $c): ?>
        <?php if (Yii::$app->session->hasFlash($k)): ?><div class="alert alert-<?= $c ?> d-flex align-items-center"><i class="bi bi-<?= $c === 'danger' ? 'exclamation-triangle' : 'check-circle' ?> me-2"></i><?= Html::encode(Yii::$app->session->getFlash($k)) ?></div><?php endif; ?>
    <?php endforeach; ?>

    <?php if (!$units): ?>
        <div class="card border-0 shadow-sm rounded-4"><div class="card-body text-center text-body-secondary py-5">
            <i class="bi bi-diagram-3 fs-1 d-block mb-3"></i>
            ยังไม่มีหน่วยงานในทะเบียน — ไปเพิ่มที่ <?= Html::a('เมนูตั้งค่า → หน่วยงาน', ['/laundry/setting/unit'], ['class' => 'fw-semibold']) ?> ก่อน
        </div></div>
    <?php else: ?>
        <?php $tones = ['primary', 'success', 'info', 'warning', 'danger', 'secondary']; ?>
        <div class="row g-4 g-xl-5">
            <?php foreach ($units as $i => $u): $t = $totals[$u->tree_id] ?? null; $tone = $tones[$i % count($tones)]; $times = (int) ($t['times'] ?? 0); ?>
                <div class="col-6 col-md-4 col-lg-3 col-xxl-2">
                    <?php
                    $attrs = [
                        'class' => 'card border-0 shadow-sm rounded-4 h-100 w-100 text-start lnd-unit-card' . ($canManage ? '' : ' disabled'),
                        'style' => 'transition:transform .1s;background:var(--bs-' . $tone . '-bg-subtle,#f8f9fa);border-top:4px solid var(--bs-' . $tone . ',#0d6efd) !important',
                        'data-dept' => $u->tree_id,
                        'data-name' => $names[$u->tree_id] ?? ('#' . $u->tree_id),
                        'data-abbr' => $u->abbr ?: '',
                    ];
                    if ($canManage) {
                        $attrs['type'] = 'button';
                        $attrs['data-bs-toggle'] = 'modal';
                        $attrs['data-bs-target'] = '#recvModal';
                    }
                    $tag = $canManage ? 'button' : 'div';
                    ?>
                    <<?= $tag ?> <?= Html::renderTagAttributes($attrs) ?>>
                        <div class="card-body py-3">
                            <div class="text-center">
                                <div class="fw-bold lh-1 mb-1" style="font-size:1.9rem;color:var(--bs-<?= $tone ?>,#0d6efd)"><?= Html::encode($u->abbr ?: mb_substr($names[$u->tree_id] ?? '?', 0, 4)) ?></div>
                                <div class="small text-body-secondary text-truncate mb-3" title="<?= Html::encode($names[$u->tree_id] ?? '') ?>"><?= Html::encode($names[$u->tree_id] ?? ('#' . $u->tree_id)) ?></div>
                            </div>
                            <div class="small">
                                <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                                    <span>รับผ้า</span><span class="fw-semibold"><?= $times ?> ครั้ง</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-body-secondary">ผ้าเปื้อน</span><span><span class="fw-semibold"><?= number_format((float) ($t['soiled'] ?? 0), 1) ?></span> กก.</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-body-secondary">ผ้าติดเชื้อ</span><span><span class="fw-semibold"><?= number_format((float) ($t['infectious'] ?? 0), 1) ?></span> กก.</span>
                                </div>
                            </div>
                        </div>
                    </<?= $tag ?>>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php if ($canManage && $units): ?>
<div class="modal fade" id="recvModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <?= Html::beginForm(['save'], 'post') ?>
            <?= Html::hiddenInput('department_id', '', ['id' => 'recv_dept']) ?>
            <?= Html::hiddenInput('collected_date', AppHelper::convertToThai($date)) ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-lg me-2"></i>รับผ้า — <span id="recv_unit_name" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom small">
                    <span class="text-body-secondary"><i class="bi bi-person-badge me-1"></i>เจ้าหน้าที่เก็บ</span>
                    <span class="fw-semibold"><?= Html::encode($staffName) ?></span>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">ผ้าเปื้อน (กก.)</label>
                        <?= Html::input('number', 'soiled_kg', '', ['class' => 'form-control form-control-lg', 'min' => '0', 'step' => '0.001', 'placeholder' => '0.000', 'inputmode' => 'decimal']) ?>
                    </div>
                    <div class="col-6">
                        <label class="form-label">ผ้าติดเชื้อ (กก.)</label>
                        <?= Html::input('number', 'infectious_kg', '', ['class' => 'form-control form-control-lg', 'min' => '0', 'step' => '0.001', 'placeholder' => '0.000', 'inputmode' => 'decimal']) ?>
                    </div>
                    <div class="col-6">
                        <label class="form-label">รอบเก็บ</label>
                        <?= Html::input('number', 'round_seq', '1', ['class' => 'form-control', 'min' => '1', 'inputmode' => 'numeric']) ?>
                    </div>
                    <div class="col-6">
                        <label class="form-label">เวลา</label>
                        <?= Html::input('time', 'collected_time', date('H:i'), ['class' => 'form-control']) ?>
                    </div>
                </div>
                <div class="form-text mt-2"><i class="bi bi-info-circle me-1"></i>กรอกน้ำหนักอย่างน้อยหนึ่งประเภท</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <?= Html::submitButton('<i class="bi bi-save me-1"></i>บันทึกข้อมูล', ['class' => 'btn btn-primary']) ?>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>
<?php
$this->registerJs(<<<'JS'
var recvModal = document.getElementById('recvModal');
if (recvModal) {
  recvModal.addEventListener('show.bs.modal', function (e) {
    var card = e.relatedTarget; if (!card) return;
    document.getElementById('recv_dept').value = card.getAttribute('data-dept') || '';
    var abbr = card.getAttribute('data-abbr'), name = card.getAttribute('data-name') || '';
    document.getElementById('recv_unit_name').textContent = abbr ? (abbr + ' · ' + name) : name;
    recvModal.querySelector('[name="soiled_kg"]').value = '';
    recvModal.querySelector('[name="infectious_kg"]').value = '';
  });
}
JS);
?>
<?php endif; ?>
