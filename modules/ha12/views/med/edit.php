<?php

use app\components\AppHelper;
use app\modules\ha12\models\Ha12MedCount;
use app\modules\ha12\models\Ha12MedReport;
use app\modules\ha12\models\Ha12MedTemplate;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Ha12MedReport $report */
/** @var bool $canManage */

$this->title = 'HA12-PCT · กรอกรายงานยา';
$sev = Ha12MedTemplate::SEVERITY;         // key => label
$teams = Ha12MedTemplate::TEAMS;
$units = Ha12MedTemplate::DIVISOR_UNITS;
$bases = Ha12MedTemplate::RATE_BASES;
$csrf = Yii::$app->request->csrfToken;
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode('HA12-PCT') ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>กรอกความคลาดเคลื่อนทางยา · <?= AppHelper::convertToThai($report->period_start) ?> – <?= AppHelper::convertToThai($report->period_end) ?><?php $this->endBlock(); ?>

<style>
    .med-sev-input{width:64px;text-align:center}
    .med-total{font-variant-numeric:tabular-nums;font-weight:600}
    .med-grid th, .med-grid td{white-space:nowrap;vertical-align:middle}
    .med-narrow{width:120px}
</style>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/ha12/menu', ['active' => 'med']) ?></div>

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $tone): ?>
        <?php if ($msg = Yii::$app->session->getFlash($flash)): ?>
            <div class="alert alert-<?= $tone ?> alert-dismissible fade show"><?= Html::encode($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h5 fw-semibold mb-0"><i class="bi bi-capsule me-1"></i> รายงานความคลาดเคลื่อนทางยา</h1>
            <div class="text-body-secondary small"><?= Html::encode($report->ownerUnit->name ?? '—') ?> · รวมทุกหัวข้อ <b><?= number_format($report->grandTotal()) ?></b> ครั้ง</div>
        </div>
        <?= Html::a('<i class="bi bi-arrow-left"></i> กลับ', ['index', 'fy' => $report->fiscal_year], ['class' => 'btn btn-outline-secondary rounded-pill btn-sm']) ?>
    </div>

    <div class="alert alert-info-subtle border small">
        <i class="bi bi-info-circle me-1"></i>
        ช่องว่าง = ยังไม่รายงาน · 0 = ตรวจแล้วไม่พบ · จำนวนครั้งรวมอัตโนมัติจากทุกระดับความรุนแรง ·
        อัตรา = จำนวนครั้ง ÷ ตัวหาร × ฐาน (ต้องกรอกตัวหาร &gt; 0 และเลือกฐาน)
    </div>

    <?php $form = Html::beginForm(['save-report', 'id' => $report->id], 'post'); ?>

    <!-- meta -->
    <div class="card border shadow-sm mb-3">
        <div class="card-body row g-3">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">วันที่ทบทวน</label>
                <?= DatepickerThai::widget(['name' => 'review_date_thai', 'value' => $report->review_date ? AppHelper::convertToThai($report->review_date) : '', 'options' => ['id' => 'med-rd', 'autocomplete' => 'off', 'class' => 'form-control', 'placeholder' => 'วว/ดด/พ.ศ.']]) ?>
            </div>
            <div class="col-md-9">
                <label class="form-label small fw-semibold">บันทึกรวม</label>
                <?= Html::textarea('Ha12MedReport[note]', (string) $report->note, ['class' => 'form-control', 'rows' => 1]) ?>
            </div>
        </div>
    </div>

    <div class="accordion" id="med-acc">
        <?php foreach ($report->groups as $idx => $g): ?>
            <?php $rate = $g->rate(); ?>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button <?= $idx === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#g<?= $g->id ?>">
                        <span class="fw-semibold"><?= $g->group_no ?>. <?= Html::encode($g->groupName()) ?></span>
                        <span class="badge bg-primary-subtle text-primary-emphasis ms-2">รวม <?= number_format($g->groupTotal()) ?></span>
                        <?php if ($rate !== null): ?>
                            <span class="badge bg-info-subtle text-info-emphasis ms-1">อัตรา <?= number_format($rate, 2) ?> ต่อ <?= number_format((int) $g->rate_base) ?></span>
                        <?php endif; ?>
                    </button>
                </h2>
                <div id="g<?= $g->id ?>" class="accordion-collapse collapse <?= $idx === 0 ? 'show' : '' ?>" data-bs-parent="#med-acc">
                    <div class="accordion-body">
                        <!-- ตัวหาร/หน่วย/ฐาน -->
                        <div class="row g-2 align-items-end mb-3">
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold mb-1">ตัวหาร</label>
                                <?= Html::input('number', "group[{$g->id}][divisor]", $g->divisor, ['class' => 'form-control', 'min' => '1', 'placeholder' => 'เช่น จำนวนใบสั่งยา']) ?>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold mb-1">หน่วยตัวหาร</label>
                                <?php
                                $allowedUnits = Ha12MedTemplate::groups()[$g->group_no]['units'] ?? array_keys($units);
                                $unitOpts = [];
                                foreach ($allowedUnits as $uk) {
                                    $unitOpts[$uk] = $units[$uk];
                                }
                                ?>
                                <?= Html::dropDownList("group[{$g->id}][divisor_unit]", $g->divisor_unit, $unitOpts, ['class' => 'form-select']) ?>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label small fw-semibold mb-1">ฐานอัตรา</label>
                                <?= Html::dropDownList("group[{$g->id}][rate_base]", $g->rate_base, array_combine($bases, array_map('number_format', $bases)), ['class' => 'form-select', 'prompt' => '— เลือกฐาน —']) ?>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle med-grid mb-2">
                                <thead class="bg-body-tertiary">
                                    <tr>
                                        <th style="min-width:12rem;">ความเสี่ยงย่อย</th>
                                        <th class="med-narrow">ทีม</th>
                                        <?php foreach ($sev as $label): ?><th class="text-center"><?= Html::encode($label) ?></th><?php endforeach; ?>
                                        <th class="text-center">รวม</th>
                                        <th style="min-width:14rem;">ผล / การแก้ไข</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($g->counts as $c): ?>
                                        <tr>
                                            <td>
                                                <?php if ($c->is_other): ?>
                                                    <?= Html::input('text', "count[{$c->id}][risk_name]", $c->risk_name, ['class' => 'form-control form-control-sm']) ?>
                                                <?php else: ?>
                                                    <?= Html::encode($c->risk_name) ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?= Html::dropDownList("count[{$c->id}][team]", $c->team, $teams, ['class' => 'form-select form-select-sm', 'prompt' => '—']) ?>
                                            </td>
                                            <?php foreach (array_keys($sev) as $col): ?>
                                                <td class="text-center"><?= Html::input('number', "count[{$c->id}][$col]", $c->$col, ['class' => 'form-control form-control-sm med-sev-input', 'min' => '0']) ?></td>
                                            <?php endforeach; ?>
                                            <td class="text-center med-total"><?= $c->total_count === null ? '—' : number_format((int) $c->total_count) ?></td>
                                            <td>
                                                <?= Html::textarea("count[{$c->id}][review_result]", (string) $c->review_result, ['class' => 'form-control form-control-sm mb-1', 'rows' => 1, 'placeholder' => 'ผลการทบทวน']) ?>
                                                <?= Html::textarea("count[{$c->id}][fix]", (string) $c->fix, ['class' => 'form-control form-control-sm', 'rows' => 1, 'placeholder' => 'การแก้ไข/ป้องกัน']) ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($c->is_other): ?>
                                                    <?= Html::a('<i class="bi bi-x-lg"></i>', ['delete-count', 'id' => $c->id], ['class' => 'btn btn-sm btn-outline-danger', 'aria-label' => 'ลบ', 'data' => ['method' => 'post', 'confirm' => 'ลบความเสี่ยงนี้?']]) ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-add-other="<?= $g->id ?>">
                            <i class="bi bi-plus-lg"></i> เพิ่มความเสี่ยงอื่น ๆ
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="d-grid d-sm-flex justify-content-sm-end gap-2 mt-3">
        <?= Html::submitButton('<i class="bi bi-save"></i> บันทึกทั้งรายงาน', ['class' => 'btn btn-primary']) ?>
    </div>
    <?= Html::endForm(); ?>
</div>

<?php
$addOtherUrlBase = Url::to(['add-other']);
$this->registerJs(<<<JS
if (typeof thaiDatepicker === 'function') { thaiDatepicker('#med-rd'); }
document.querySelectorAll('[data-add-other]').forEach(function(btn){
    btn.addEventListener('click', function(){
        var gid = this.getAttribute('data-add-other');
        var doAdd = function(name){
            if (!name) return;
            var f = document.createElement('form');
            f.method = 'post';
            f.action = '{$addOtherUrlBase}?id=' + gid;
            f.innerHTML = '<input type="hidden" name="_csrf" value="{$csrf}">' +
                          '<input type="hidden" name="risk_name">';
            f.querySelector('input[name=risk_name]').value = name;
            document.body.appendChild(f);
            f.submit();
        };
        if (window.Swal) {
            Swal.fire({title:'เพิ่มความเสี่ยงอื่น ๆ', input:'text', inputPlaceholder:'ชื่อความเสี่ยง',
                showCancelButton:true, confirmButtonText:'เพิ่ม', cancelButtonText:'ยกเลิก', reverseButtons:false})
                .then(function(r){ if (r.isConfirmed) doAdd((r.value||'').trim()); });
        } else {
            doAdd((window.prompt('ชื่อความเสี่ยงที่จะเพิ่ม') || '').trim());
        }
    });
});
JS); ?>
