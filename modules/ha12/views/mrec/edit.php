<?php

use app\components\AppHelper;
use app\modules\ha12\models\Ha12MrecAudit;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Ha12MrecAudit $audit */

$this->title = 'HA12-PCT · กรอกเวชระเบียน';
$csrf = Yii::$app->request->csrfToken;
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode('HA12-PCT') ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ความสมบูรณ์ของเวชระเบียน · <?= Html::encode($audit->ownerUnit->name ?? '—') ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/ha12/menu', ['active' => 'mrec']) ?></div>

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $tone): ?>
        <?php if ($msg = Yii::$app->session->getFlash($flash)): ?>
            <div class="alert alert-<?= $tone ?> alert-dismissible fade show"><?= Html::encode($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 fw-semibold mb-0"><i class="bi bi-journal-medical me-1"></i> กรอกความสมบูรณ์เวชระเบียน</h1>
        <?= Html::a('<i class="bi bi-arrow-left"></i> กลับ', ['index', 'fy' => $audit->fiscal_year], ['class' => 'btn btn-outline-secondary rounded-pill btn-sm']) ?>
    </div>

    <?php $form = Html::beginForm(['save-audit', 'id' => $audit->id], 'post'); ?>
    <div class="card border shadow-sm mb-3"><div class="card-body row g-3">
        <div class="col-md-3">
            <label class="form-label small fw-semibold">จำนวนที่ตรวจ (ฉบับ)</label>
            <?= Html::activeInput('number', $audit, 'total_charts', ['class' => 'form-control', 'min' => '0']) ?>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">วันที่ทบทวน</label>
            <?= DatepickerThai::widget(['name' => 'review_date_thai', 'value' => $audit->review_date ? AppHelper::convertToThai($audit->review_date) : '', 'options' => ['id' => 'mr-rd', 'autocomplete' => 'off', 'class' => 'form-control', 'placeholder' => 'วว/ดด/พ.ศ.']]) ?>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">ปัญหาที่พบ</label>
            <?= Html::activeTextarea($audit, 'problem', ['class' => 'form-control', 'rows' => 1]) ?>
        </div>
        <div class="col-12">
            <label class="form-label small fw-semibold">ผล/การปรับปรุง</label>
            <?= Html::activeTextarea($audit, 'result', ['class' => 'form-control', 'rows' => 1]) ?>
        </div>
    </div></div>

    <div class="card border shadow-sm">
        <div class="card-header bg-body-tertiary fw-semibold"><i class="bi bi-list-check me-1"></i> หัวข้อความครบถ้วน</div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="bg-body-tertiary"><tr>
                    <th style="width:44px;" class="text-center">#</th>
                    <th>หัวข้อ</th>
                    <th style="width:140px;" class="text-center">จำนวนที่ครบ</th>
                    <th style="width:90px;" class="text-end">ร้อยละ</th>
                    <th style="width:44px;"></th>
                </tr></thead>
                <tbody>
                <?php foreach ($audit->items as $it): $pct = $it->percent(); ?>
                    <tr>
                        <td class="text-center text-body-secondary" style="font-variant-numeric:tabular-nums;"><?= $it->item_no ?></td>
                        <td>
                            <?php if ($it->is_other): ?>
                                <?= Html::input('text', "item[{$it->id}][item_name]", $it->item_name, ['class' => 'form-control form-control-sm']) ?>
                            <?php else: ?><?= Html::encode($it->item_name) ?><?php endif; ?>
                        </td>
                        <td class="text-center"><?= Html::input('number', "item[{$it->id}][complete_count]", $it->complete_count, ['class' => 'form-control form-control-sm text-center', 'min' => '0', 'style' => 'width:110px;margin:auto']) ?></td>
                        <td class="text-end" style="font-variant-numeric:tabular-nums;"><?= $pct !== null ? number_format($pct, 1) . '%' : '—' ?></td>
                        <td class="text-center">
                            <?php if ($it->is_other): ?>
                                <?= Html::a('<i class="bi bi-x-lg"></i>', ['delete-item', 'id' => $it->id], ['class' => 'btn btn-sm btn-outline-danger', 'aria-label' => 'ลบ', 'data' => ['method' => 'post', 'confirm' => 'ลบหัวข้อนี้?']]) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-body">
            <button type="button" class="btn btn-sm btn-outline-primary" id="mrec-add-item"><i class="bi bi-plus-lg"></i> เพิ่มหัวข้อ</button>
        </div>
    </div>

    <div class="d-grid d-sm-flex justify-content-sm-end gap-2 mt-3">
        <?= Html::submitButton('<i class="bi bi-save"></i> บันทึกทั้งหมด', ['class' => 'btn btn-primary']) ?>
    </div>
    <?= Html::endForm(); ?>
</div>

<?php
$addUrl = Url::to(['add-item', 'id' => $audit->id]);
$this->registerJs(<<<JS
if (typeof thaiDatepicker === 'function') { thaiDatepicker('#mr-rd'); }
document.getElementById('mrec-add-item').addEventListener('click', function(){
    var doAdd = function(name){ if(!name) return;
        var f=document.createElement('form'); f.method='post'; f.action='{$addUrl}';
        f.innerHTML='<input type="hidden" name="_csrf" value="{$csrf}"><input type="hidden" name="item_name">';
        f.querySelector('input[name=item_name]').value=name; document.body.appendChild(f); f.submit(); };
    if (window.Swal) { Swal.fire({title:'เพิ่มหัวข้อ', input:'text', showCancelButton:true, confirmButtonText:'เพิ่ม', cancelButtonText:'ยกเลิก', reverseButtons:false}).then(function(r){ if(r.isConfirmed) doAdd((r.value||'').trim()); }); }
    else { doAdd((window.prompt('ชื่อหัวข้อ')||'').trim()); }
});
JS); ?>
