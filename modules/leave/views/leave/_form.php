<?php

use app\components\ApproveLevelResolver;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\hr\models\Employees;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;


/** @var yii\web\View $this */
/** @var app\modules\lm\models\Leave $model */
/** @var yii\widgets\ActiveForm $form */
$formatJs = <<<'JS'
    var formatRepo = function (repo) {
        if (repo.loading) {
            return repo.avatar;
        }
        // console.log(repo);
        var markup =
    '<div class="row">' +
        '<div class="col-12">' +
            '<span>' + repo.avatar + '</span>' +
        '</div>' +
    '</div>';
        if (repo.description) {
          markup += '<p>' + repo.avatar + '</p>';
        }
        return '<div style="overflow:hidden;">' + markup + '</div>';
    };
    var formatRepoSelection = function (repo) {
        return repo.avatar || repo.avatar;
    }
    JS;

// Register the formatting script
$this->registerJs($formatJs, View::POS_HEAD);

// script to parse the results into the format expected by Select2
$resultsJs = <<<JS
    function (data, params) {
        params.page = params.page || 1;
        return {
            results: data.results,
            pagination: {
                more: (params.page * 30) < data.total_count
            }
        };
    }
    JS;

/** @var app\modules\leave\models\Leave $model */
/** @var string $draftRef */
$employee   = $employee ?? null;
$types      = $types ?? [];
$stats      = $stats ?? [];
$roundLabel = $roundLabel ?? '';
$draftRef   = $draftRef ?? null;
$leaveWorkSendInitText = $leaveWorkSendInitText ?? '';

$isUpdate = $model instanceof \app\modules\leave\models\Leave && !$model->isNewRecord;
if ($isUpdate) {
    $typeOptions = $model->listLeaveType();
} else {
    $typeOptions = [];
    foreach ($types as $t) {
        $code = $t->code ?? '';
        if ($code !== '') $typeOptions[$code] = $t->title;
    }
}


$attrWorkShift      = 'data_json[work_shift]';
$attrReason         = 'data_json[reason]';
$attrPhone          = 'data_json[leave_contact_phone]';
$attrPlaceGo        = 'data_json[place_go]';
$attrAddress        = 'data_json[address]';
$attrLeaveSend      = 'data_json[leave_work_send_id]';
$attrLeaveName      = 'data_json[leave_work_send_name]';
$attrDateStartType  = 'data_json[date_start_type]';
$attrDateEndType    = 'data_json[date_end_type]';

$this->title = $isUpdate ? 'แก้ไขใบลา' : 'สร้างใบลาใหม่';
$this->params['breadcrumbs'][] = ['label' => 'การลางาน', 'url' => ['/leave/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$name = $employee ? trim(($employee->fname ?? '') . ' ' . ($employee->lname ?? '')) : '';
$positionName = $employee && $employee->positionType ? $employee->positionType->title : '';
$phone = $employee->phone ?? '';
?>

<style>
    .workflow-step .step-dot {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        background: #dee2e6;
        color: #6c757d;
        font-size: 0.75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 1;
    }

    .workflow-step.done .step-dot {
        background: #5D5FEF;
        color: #fff;
    }
</style>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2">
    <a href="<?= Url::to(['/leave/default/index']) ?>" class="btn btn-link btn-sm text-body p-0"><i class="bi bi-arrow-left fs-4"></i></a>
    <h4 class="fw-bold text-body mb-0"><?= $isUpdate ? 'แก้ไขใบลา' : 'สร้างใบลาใหม่' ?></h4>
</div>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/components/ui/btnReturn') ?>
<?php $this->endBlock(); ?>

<?php

$resolveEmpId = (int) ($model->emp_id ?? 0);
?>
<div class="container-fluid py-3">
    <?php $form = ActiveForm::begin([
        'id' => $isUpdate ? 'form-leave-update' : 'leave-create-form',
        'action' => $isUpdate ? ['/leave/leave/update', 'id' => $model->id] : ['/leave/leave/create'],
        'method' => 'post',
        'options' => ['class' => 'row'],
        'enableAjaxValidation' => $isUpdate,
        'validationUrl' => $isUpdate ? ['/leave/leave/update-validation', 'id' => $model->id] : null,
        'fieldConfig' => [
            'labelOptions' => ['class' => 'form-label small text-body'],
            'inputOptions' => ['class' => 'form-control'],
            'errorOptions' => ['class' => 'invalid-feedback'],
        ],
    ]); ?>
    <?php if ($isUpdate): ?>
        <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
    <?php endif; ?>

    <div class="col-12">

        <h6 class="d-flex align-items-center gap-2 fw-bold text-body mb-4">
            <span class="erp-icon-box bg-warning bg-opacity-10 text-warning rounded-3 d-flex align-items-center justify-content-center">
                <i data-lucide="pencil" style="width:1.125rem;height:1.125rem"></i>
            </span>
            กรอกรายละเอียด
        </h6>

        <!-- ประเภทการลา + ประเภทของเวร (แถวเดียวกัน) -->
        <div class="row g-3 mb-3">
            <div class="col-md-7">
                <?= $form->field($model, 'leave_type_id')->dropDownList(
                    $typeOptions,
                    [
                        'id'     => 'leave-create-form-leave_type_id',
                        'class'  => 'form-select',
                        'prompt' => '--- เลือกประเภทการลา ---',
                    ]
                )->label('ประเภทการลา') ?>
            </div>
            <div class="col-md-5">
                <?= $form->field($model, $attrWorkShift)->dropDownList(
                    ['normal' => 'เวรเช้า', 'shift' => 'เวร 8 ชั่วโมง'],
                    [
                        'id'    => 'leave-work_shift',
                        'class' => 'form-select',
                    ]
                )->label('ประเภทของเวร (เวร 8: ไม่นับวันหยุดและเสาร์-อาทิตย์)') ?>

            </div>
        </div>

        <!-- วันที่ + ประเภท -->
        <div class="row g-3 mb-3">
            <div class="col-md-7">
                <?= $form->field($model, 'date_start')->textInput([
                    'id' => 'leave-date_start',
                    'class' => 'form-control',
                    'placeholder' => 'วันที่/เดือน/พ.ศ.',
                ])->label('ตั้งแต่วันที่') ?>
            </div>
            <div class="col-md-5">
                <?= $form->field($model, $attrDateStartType)->dropDownList(
                    ['0' => 'เต็มวัน', '0.5' => 'ครึ่งวัน'],
                    ['id' => 'leave-date_start_type', 'class' => 'form-select']
                )->label('ประเภท') ?>
            </div>
            <div class="col-md-7">
                <?= $form->field($model, 'date_end')->textInput([
                    'id' => 'leave-date_end',
                    'class' => 'form-control',
                    'placeholder' => 'วันที่/เดือน/พ.ศ.',
                ])->label('ถึงวันที่') ?>
            </div>
            <div class="col-md-5">
                <?= $form->field($model, $attrDateEndType)->dropDownList(
                    ['0' => 'เต็มวัน', '0.5' => 'ครึ่งวัน'],
                    ['id' => 'leave-date_end_type', 'class' => 'form-select']
                )->label('ประเภท') ?>
            </div>
        </div>

        <!-- สรุปวันลา -->
        <?php
        $initSatsun = 0;
        $initHoliday = 0;
        $initTotal = 0;
        if ($isUpdate && is_array($model->data_json ?? null)) {
            $initSatsun  = (int) ($model->data_json['summary_sat_sun'] ?? $model->data_json['sat_sun_days'] ?? 0);
            $initHoliday = (int) ($model->data_json['summary_holiday'] ?? $model->data_json['holidays'] ?? 0);
            $initTotal   = (float) ($model->total_days ?? 0);
        }
        ?>
        <div class="card border-0 border-start border-3 border-info mb-3">
            <div class="card-body py-2 px-3">
                <div class="small fw-semibold text-secondary mb-2"><i class="bi bi-calendar3 me-1"></i> สรุปวันลา</div>
                <div class="row g-2">
                    <div class="col-4">
                        <label class="form-label small text-muted mb-1">วันเสาร์-อาทิตย์ <span class="text-secondary">(วัน)</span></label>
                        <input type="text" id="leave-summary-satsun" class="form-control text-center fw-semibold" value="<?= (int) $initSatsun ?>" readonly tabindex="-1">
                    </div>
                    <div class="col-4">
                        <label class="form-label small text-muted mb-1">วันหยุดนักขัตฤกษ์ <span class="text-secondary">(วัน)</span></label>
                        <input type="text" id="leave-summary-holiday" class="form-control text-center fw-semibold" value="<?= (int) $initHoliday ?>" readonly tabindex="-1">
                    </div>
                    <div class="col-4">
                        <label class="form-label small text-muted mb-1">
                            สรุปวันลา <span class="text-secondary">(วัน)</span>
                            <span id="leave-total-hint" class="text-info d-none" style="font-size:0.7rem"> (แก้ไขได้)</span>
                        </label>
                        <input type="text" id="leave-summary-total" class="form-control text-center fw-bold text-primary" value="<?= (float) $initTotal ?>" readonly tabindex="-1">
                    </div>
                </div>
            </div>
        </div>
        <?= $form->field($model, 'total_days')->hiddenInput(['id' => 'leave-total_days'])->label(false) ?>
        <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded bg-body-secondary bg-opacity-25">
            <i class="bi bi-file-text text-primary"></i>
            <span class="small">รวมวันลา</span>
            <span class="small text-muted ms-2">คำนวณอัตโนมัติ</span>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1 ms-auto" id="leave-total-days">0 วัน</span>
        </div>

        <!-- มอบหมายงานให้ -->
        <div class="mb-3">
            <?php
            $searchEmpUrl = \yii\helpers\Url::to(['/leave/leave/search-employee']);
            ?>
<<<<<<< HEAD
            <?php
            $leaveSendWidgetOpts = [
                'options'       => ['id' => 'leave-work_send_id', 'placeholder' => 'พิมพ์ชื่อเพื่อค้นหา...'],
                'pluginOptions' => [
                    'allowClear'         => true,
                    'minimumInputLength' => 1,
                    'ajax'               => [
                        'url'            => $searchEmpUrl,
                        'dataType'       => 'json',
                        'delay'          => 250,
                        'data'           => new JsExpression('function(params){ return {q:params.term}; }'),
                        'processResults' => new JsExpression('function(data){ return {results: data.results}; }'),
                        'cache'          => true,
                    ],
                    'escapeMarkup'       => new JsExpression('function(m){ return m; }'),
                    'templateResult'     => new JsExpression('function(r){ return r.text||r.id; }'),
                    'templateSelection'  => new JsExpression('function(r){ return r.text||r.id; }'),
                ],
                'pluginEvents'  => [
                    'select2:select'   => new JsExpression('function(e){ var d=e.params.data; jQuery("#leave-work_send_name").val(d.fullname||d.text||""); }'),
                    'select2:unselect' => new JsExpression('function(){ jQuery("#leave-work_send_name").val(""); }'),
                ],
            ];
            if ($isUpdate && $leaveWorkSendInitText !== '') {
                $leaveSendWidgetOpts['initValueText'] = $leaveWorkSendInitText;
            }
            ?>
            <?php
            try {
                $initEmployee = Employees::find()->where(['id' => $model->data_json['leave_work_send_id']])->one()->getAvatar(false);
            } catch (\Throwable $th) {
                $initEmployee = '';
            }
            echo $form->field($model, 'data_json[leave_work_send_id]')->widget(Select2::classname(), [
                'initValueText' => $initEmployee,
                'options' => ['placeholder' => 'เลือกรายการ...'],
                'size' => Select2::LARGE,
                'pluginEvents' => [
                    'select2:unselect' => 'function() {
                                    $("#leave-data_json-leave_work_send").val("")
                                    }',
                    'select2:select' => 'function() {
                                            var fullname = $(this).select2("data")[0].fullname;
                                            $("#leave-data_json-leave_work_send").val(fullname)
                                    }',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'dropdownParent' => '#main-modal',
                    'minimumInputLength' => 1,
                    'ajax' => [
                        'url' => Url::to(['/depdrop/employee-by-id']),
                        'dataType' => 'json',
                        'delay' => 250,
                        'data' => new JsExpression('function(params) { return {q:params.term, page: params.page}; }'),
                        'processResults' => new JsExpression($resultsJs),
                        'cache' => true,
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateSelection' => new JsExpression('function (item) { return item.text; }'),
                    'templateResult' => new JsExpression('formatRepo'),
                ],
            ])->label('มอบหมายงานให้')
            ?>
            <?= $form->field($model, $attrLeaveName)->hiddenInput(['id' => 'leave-work_send_name'])->label(false) ?>
=======
            <?php foreach ($listApprove as $i => $step): ?>
                <?php
                $approveEmployee = Employees::findOne(['id' => $step['emp_id']]);
                ?>
                <div class="col-3 workflow-step ">


                    <div class="step-dot">
                        <?php
                        if ($approveEmployee) {
                            echo Html::img($approveEmployee->showAvatar(), ['class' => 'rounded-circle', 'width' => 35, 'height' => 35, 'style' => 'min-width: 35px; min-height: 35px;', 'alt' => $approveEmployee->fullname]);
                        }
                        ?>
                    </div>
                    <span class="step-label d-block text-truncate">
                        <?= Html::encode($step['title'] ?? $step['label']) ?>
                    </span>

                </div>
            <?php endforeach; ?>
>>>>>>> dev
        </div>

        <div class="mb-3">
            <?= $form->field($model, $attrReason)->textarea([
                'class' => 'form-control rounded-3',
                'rows' => 3,
                'placeholder' => 'เช่น ป่วยเป็นไข้หวัด, ติดธุระทางครอบครัว...',
            ])->label('สาเหตุการลา') ?>
        </div>
        <div class="mb-3">
            <?= $form->field($model, $attrPhone)->textInput([
                'class' => 'form-control rounded-3',
                'placeholder' => 'เช่น 08x-xxx-xxxx',
            ])->label('เบอร์โทรติดต่อ') ?>
        </div>
        <div class="mb-3">
            <?= $form->field($model, $attrPlaceGo)->dropDownList(
                [
                    'ภายในจังหวัด' => 'ภายในจังหวัด',
                    'ต่างจังหวัด'  => 'ต่างจังหวัด',
                    'ต่างประเทศ'   => 'ต่างประเทศ',
                ],
                ['class' => 'form-select', 'prompt' => '--- เลือกสถานที่ไป ---']
            )->label('สถานที่ไป') ?>
        </div>
        <div class="mb-4">
            <?= $form->field($model, $attrAddress)->textarea([
                'class' => 'form-control rounded-3',
                'rows' => 3,
                'placeholder' => 'บ้านเลขที่ หมู่ ถนน ตำบล อำเภอ....',
            ])->label('ที่อยู่ที่ติดต่อได้') ?>
        </div>

        <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'emp_id')->hiddenInput()->label(false) ?>
        <?php if ($isUpdate): ?>
            <?= $form->field($model, 'data_json[leave_work_send]')->hiddenInput()->label(false) ?>
            <?= $form->field($model, 'data_json[title]')->hiddenInput()->label(false) ?>
            <?= $form->field($model, 'data_json[director]')->hiddenInput()->label(false) ?>
            <?= $form->field($model, 'data_json[director_fullname]')->hiddenInput()->label(false) ?>
        <?php endif; ?>

        <!-- เอกสารแนบ / ใบรับรองแพทย์ -->

        <h6 class="d-flex align-items-center gap-2 fw-bold text-body mb-3">
            <span class="erp-icon-box bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center">
                <i data-lucide="paperclip" style="width:1.125rem;height:1.125rem"></i>
            </span>
            เอกสารแนบ / ใบรับรองแพทย์
        </h6>
        <?= $isUpdate ? $model->Upload('leave_file') : FileManagerHelper::FileUpload($draftRef, 'leave_file') ?>

        <?php
        $existingSigType = is_array($model->data_json) ? ($model->data_json['signature_type'] ?? 'canvas') : 'canvas';
        $existingSigData = is_array($model->data_json) ? ($model->data_json['signature_data'] ?? '') : '';
        $signatureSystemUrl = null;
        if ($employee && method_exists($employee, 'SignatureShow')) {
            try { $signatureSystemUrl = $employee->SignatureShow(); } catch (\Throwable $e) {}
        }
        ?>

        <!-- ── ลายเซ็น ──────────────────────────────────────────── -->
        <div class="mb-4 mt-3">
            <h6 class="d-flex align-items-center gap-2 fw-bold text-body mb-3">
                <span class="erp-icon-box bg-info bg-opacity-10 text-info rounded-3 d-flex align-items-center justify-content-center" style="width:2rem;height:2rem;">
                    <i class="bi bi-pen" style="font-size:.9rem;"></i>
                </span>
                ลายเซ็น
            </h6>

            <!-- ตัวเลือกโหมด -->
            <div class="d-flex gap-2 mb-3">
                <button type="button" class="btn btn-sm rounded-3 btn-sig-tab <?= $existingSigType !== 'system' ? 'btn-primary' : 'btn-outline-primary' ?>" data-sig-type="canvas">
                    <i class="bi bi-pen me-1"></i> เซ็นสด
                </button>
                <button type="button" class="btn btn-sm rounded-3 btn-sig-tab <?= $existingSigType === 'system' ? 'btn-primary' : 'btn-outline-primary' ?>" data-sig-type="system">
                    <i class="bi bi-person-badge me-1"></i> ลายเซ็นระบบ
                </button>
            </div>

            <!-- แผง: เซ็นสด -->
            <div id="sig-panel-canvas" class="<?= $existingSigType === 'system' ? 'd-none' : '' ?>">
                <div class="border rounded-3 bg-white overflow-hidden" style="touch-action:none;max-width:560px;">
                    <canvas id="sig-canvas" width="560" height="180" style="display:block;width:100%;height:180px;cursor:crosshair;"></canvas>
                </div>
                <div class="d-flex gap-2 mt-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3" id="sig-clear-btn">
                        <i class="bi bi-eraser me-1"></i> ล้างลายเซ็น
                    </button>
                </div>
            </div>

            <!-- แผง: ลายเซ็นระบบ -->
            <div id="sig-panel-system" class="<?= $existingSigType !== 'system' ? 'd-none' : '' ?>">
                <?php if ($signatureSystemUrl): ?>
                    <div class="border rounded-3 p-3 d-inline-block bg-body-secondary">
                        <img src="<?= Html::encode($signatureSystemUrl) ?>" alt="ลายเซ็นระบบ"
                             style="max-height:120px;max-width:300px;display:block;">
                    </div>
                    <div class="form-text mt-1">ลายเซ็นจากระบบ HR จะถูกแนบในใบลาโดยอัตโนมัติ</div>
                <?php else: ?>
                    <div class="alert alert-warning border-0 rounded-3 small d-flex align-items-center gap-2 py-2 px-3" style="max-width:480px;">
                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                        ไม่พบลายเซ็นในระบบ กรุณาใช้การเซ็นสดแทน
                    </div>
                <?php endif; ?>
            </div>

            <!-- hidden fields -->
            <?= $form->field($model, 'data_json[signature_type]')->hiddenInput(['id' => 'sig-type-input'])->label(false) ?>
            <?= $form->field($model, 'data_json[signature_data]')->hiddenInput(['id' => 'sig-data-input'])->label(false) ?>
        </div>

        <div class="d-flex justify-content-center">
            <button type="submit" class="btn btn-primary rounded-3 px-4">
                <i class="bi bi-check2-circle me-1"></i> บันทึก
            </button>
        </div>

    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
\app\widgets\datepicker\Assets::register($this);
$this->registerJs("if (typeof thaiDatepicker === 'function') thaiDatepicker('#leave-date_start,#leave-date_end');", \yii\web\View::POS_END);
$calDaysUrl       = \yii\helpers\Url::to(['/leave/leave/cal-days']);
$updateShiftUrl   = \yii\helpers\Url::to(['/leave/leave/update-work-shift']);
$empWorkShift     = $employee && isset($employee->work_shift) ? $employee->work_shift : 'normal';
$csrfParam        = \Yii::$app->request->csrfParam;
$csrfToken        = \Yii::$app->request->csrfToken;
$js = <<<JS
(function(){
    var calDaysUrl     = "{$calDaysUrl}";
    var updateShiftUrl = "{$updateShiftUrl}";
    var csrfParam      = "{$csrfParam}";
    var csrfToken      = "{$csrfToken}";

    // ── helpers ───────────────────────────────────────────────────────────
    function getDateStart()     { var el = document.getElementById('leave-date_start');      return el ? el.value.trim() : ''; }
    function getDateEnd()       { var el = document.getElementById('leave-date_end');        return el ? el.value.trim() : ''; }
    function getDateStartType() { var el = document.getElementById('leave-date_start_type'); return el ? parseFloat(el.value) || 0 : 0; }
    function getDateEndType()   { var el = document.getElementById('leave-date_end_type');   return el ? parseFloat(el.value) || 0 : 0; }
    function getWorkShift()     { var el = document.getElementById('leave-work_shift');      return el ? el.value : ''; }
    function getLeaveTypeId()   { var el = document.getElementById('leave-create-form-leave_type_id'); return el ? el.value : ''; }

    // ── toggleTotalEditable — เวร 8: สรุปวันลาแก้ไขได้ ───────────────────
    function toggleTotalEditable() {
        var shiftEl = document.getElementById('leave-work_shift');
        var totalEl = document.getElementById('leave-summary-total');
        var hintEl  = document.getElementById('leave-total-hint');
        if (!totalEl) return;
        var isShift8 = shiftEl && shiftEl.value === 'shift';
        if (isShift8) {
            totalEl.removeAttribute('readonly');
            totalEl.tabIndex = 0;
            totalEl.classList.add('border-info');
            if (hintEl) hintEl.classList.remove('d-none');
        } else {
            totalEl.setAttribute('readonly', 'readonly');
            totalEl.tabIndex = -1;
            totalEl.classList.remove('border-info');
            if (hintEl) hintEl.classList.add('d-none');
        }
    }

    // ── updateSummary ──────────────────────────────────────────────────────
    function updateSummary(satsun, holiday, total) {
        var s = document.getElementById('leave-summary-satsun');
        var h = document.getElementById('leave-summary-holiday');
        var t = document.getElementById('leave-summary-total');
        var totalDaysInput = document.getElementById('leave-total_days');
        var b = document.getElementById('leave-total-days');
        if (s) s.value = satsun  != null ? satsun  : 0;
        if (h) h.value = holiday != null ? holiday : 0;
        if (t) t.value = total   != null ? total   : 0;
        if (totalDaysInput) totalDaysInput.value = (total != null ? total : 0);
        if (b) b.textContent = (total != null ? total : 0) + ' วัน';
    }

    // ── toggleDateEndType ─── ปิด end_type เมื่อ start==end ──────────────
    function toggleDateEndType() {
        var s = getDateStart();
        var e = getDateEnd();
        var endTypeEl = document.getElementById('leave-date_end_type');
        if (!endTypeEl) return;
        if (s && e && s.length >= 8 && e.length >= 8 && s === e) {
            endTypeEl.disabled = true;
            endTypeEl.value = '0';
        } else {
            endTypeEl.disabled = false;
        }
    }

    // ── calDays — เรียก /leave/leave/cal-days ─────────────────────────────
    function calDays() {
        var s = getDateStart();
        var e = getDateEnd();
        if (!s || !e || s.length < 8 || e.length < 8) { updateSummary(0, 0, 0); return; }
        var params = new URLSearchParams({
            date_start:      s,
            date_end:        e,
            date_start_type: getDateStartType(),
            date_end_type:   getDateEndType(),
            leave_type_id:   getLeaveTypeId(),
            work_shift:      getWorkShift(),
        });
        fetch(calDaysUrl + '?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r){ return r.json(); })
            .then(function(res){
                if (res.status === 'error') {
                    updateSummary(0, 0, 0);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: res.message, timer: 4000, showConfirmButton: false });
                    } else {
                        alert(res.message);
                    }
                    return;
                }
                updateSummary(res.satsunDays || 0, res.holiday || 0, res.total || 0);
                // auto-fill work_shift ถ้าผู้ใช้ยังไม่เลือก
                var shiftEl = document.getElementById('leave-work_shift');
                if (shiftEl && shiftEl.value === '' && res.shift) {
                    shiftEl.value = res.shift;
                }
            })
            .catch(function(){ updateSummary(0, 0, 0); });
    }

    // ── updateWorkShift — บันทึก work_shift ลง DB แล้ว recalc ────────────
    function updateWorkShift(val) {
        if (!val) { calDays(); return; }
        var fd = new FormData();
        fd.append('work_shift', val);
        fd.append(csrfParam, csrfToken);
        fetch(updateShiftUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(){ calDays(); })
            .catch(function(){ calDays(); });
    }

    // ── bind events ───────────────────────────────────────────────────────
    function bindEvents() {
        var startEl     = document.getElementById('leave-date_start');
        var endEl       = document.getElementById('leave-date_end');
        var shiftEl     = document.getElementById('leave-work_shift');
        var startTypeEl = document.getElementById('leave-date_start_type');
        var endTypeEl   = document.getElementById('leave-date_end_type');

        function onDateChange() { toggleDateEndType(); calDays(); }

        if (startEl) { startEl.addEventListener('change', onDateChange); startEl.addEventListener('blur', onDateChange); }
        if (endEl)   { endEl.addEventListener('change',   onDateChange); endEl.addEventListener('blur',   onDateChange); }
        if (startTypeEl) startTypeEl.addEventListener('change', calDays);
        if (endTypeEl)   endTypeEl.addEventListener('change',   calDays);

        if (shiftEl) {
            shiftEl.addEventListener('change', function(){
                toggleTotalEditable();
                updateWorkShift(this.value);
            });
        }

        // sync manual total → hidden field + badge เมื่อผู้ใช้แก้ไขตรง (เวร 8)
        var totalEl = document.getElementById('leave-summary-total');
        if (totalEl) {
            totalEl.addEventListener('input', function() {
                var v = parseFloat(this.value.replace(/[^0-9.]/g, '')) || 0;
                var inp = document.getElementById('leave-total_days');
                var b   = document.getElementById('leave-total-days');
                if (inp) inp.value = v;
                if (b) b.textContent = v + ' วัน';
            });
        }

        // xdsoft datepicker: bind ผ่าน setOptions + changedatetime.xdsoft (ครอบคลุมทุก version)
        if (typeof jQuery !== 'undefined') {
            function onPickerChange() {
                setTimeout(function() { toggleDateEndType(); calDays(); }, 50);
            }
            jQuery('#leave-date_start, #leave-date_end').datetimepicker('setOptions', {
                onSelectDate: onPickerChange
            });
            jQuery(document).on('changedatetime.xdsoft', '#leave-date_start,#leave-date_end', onPickerChange);
        }
    }

    // ── leave type dropdown ───────────────────────────────────────────────
    var leaveTypeEl = document.getElementById('leave-create-form-leave_type_id');
    if (leaveTypeEl) {
        leaveTypeEl.addEventListener('change', function(){ calDays(); });
    }

    // ── init ──────────────────────────────────────────────────────────────
    bindEvents();
    toggleDateEndType();
    toggleTotalEditable();
    calDays();

    // ── helper: ajax submit with Swal confirm ────────────────────────────
    function ajaxSubmitForm(form, totalVal, successKey) {
        if (totalVal <= 0 && typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'วันลาต้องมากกว่า 0', text: '' });
            return false;
        }
        if (typeof Swal !== 'undefined') {
            Swal.fire({ title: 'ยืนยันบันทึก?', icon: 'question', showCancelButton: true, confirmButtonText: 'บันทึก', cancelButtonText: 'ยกเลิก' }).then(function(r){
                if (!r.isConfirmed) return;
                Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: function(){ Swal.showLoading(); } });
                jQuery.ajax({ url: form.attr('action'), type: 'post', data: form.serialize(), dataType: 'json' })
                    .done(function(res){
                        var ok = successKey ? res[successKey] : (res.status === 'success' || res.success);
                        if (ok) {
                            if (document.getElementById('main-modal')) jQuery('#main-modal').modal('hide');
                            Swal.fire({ icon: 'success', title: 'บันทึกสำเร็จ', showConfirmButton: false, timer: 1500 }).then(function(){
                                if (res.redirect) location.href = res.redirect; else location.reload();
                            });
                        } else {
                            Swal.fire({ icon: 'error', text: res.message || '' });
                        }
                    })
                    .fail(function(){ Swal.fire({ icon: 'error', text: 'เกิดข้อผิดพลาด' }); });
            });
        } else {
            form.off('beforeSubmit').submit();
        }
        return false;
    }

    // โหมดแก้ไข: beforeSubmit ตรวจวันลา + ยืนยัน + ส่ง ajax
    var formUpdate = document.getElementById('form-leave-update');
    if (formUpdate && typeof jQuery !== 'undefined') {
        jQuery(formUpdate).on('beforeSubmit', function(e){
            e.preventDefault();
            var total = parseFloat((document.getElementById('leave-total_days') || {}).value) || 0;
            return ajaxSubmitForm(jQuery(this), total, 'status');
        });
    }

    // โหมดสร้าง (modal): beforeSubmit ส่ง ajax โดยตรง
    var formCreate = document.getElementById('leave-create-form');
    if (formCreate && typeof jQuery !== 'undefined') {
        jQuery(formCreate).on('beforeSubmit', function(e){
            e.preventDefault();
            var total = parseFloat((document.getElementById('leave-total_days') || {}).value) || 0;
            return ajaxSubmitForm(jQuery(this), total, 'success');
        });
    }

    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
<<<<<<< HEAD

// ── Signature JS ────────────────────────────────────────────────────────
$existingSigDataJs = json_encode($existingSigData);
$sigJs = <<<JS
(function(){
    var canvas   = document.getElementById('sig-canvas');
    var typeInp  = document.getElementById('sig-type-input');
    var dataInp  = document.getElementById('sig-data-input');
    if (!canvas || !typeInp || !dataInp) return;

    var ctx      = canvas.getContext('2d');
    var drawing  = false;
    var lastX    = 0, lastY    = 0;
    var hasDrawn = false;

    // ── ขนาด canvas จริง vs แสดงผล ──────────────────────────────
    function scaleXY(clientX, clientY) {
        var rect  = canvas.getBoundingClientRect();
        var scaleX = canvas.width  / rect.width;
        var scaleY = canvas.height / rect.height;
        return [(clientX - rect.left) * scaleX, (clientY - rect.top) * scaleY];
    }

    function startDraw(x, y) {
        drawing = true;
        ctx.beginPath();
        ctx.moveTo(x, y);
        lastX = x; lastY = y;
    }
    function moveDraw(x, y) {
        if (!drawing) return;
        ctx.lineWidth   = 2;
        ctx.lineCap     = 'round';
        ctx.lineJoin    = 'round';
        ctx.strokeStyle = '#1a1a2e';
        ctx.lineTo(x, y);
        ctx.stroke();
        ctx.beginPath();
        ctx.moveTo(x, y);
        lastX = x; lastY = y;
        hasDrawn = true;
    }
    function endDraw() { drawing = false; ctx.beginPath(); }

    // mouse
    canvas.addEventListener('mousedown',  function(e){ var p=scaleXY(e.clientX,e.clientY); startDraw(p[0],p[1]); });
    canvas.addEventListener('mousemove',  function(e){ var p=scaleXY(e.clientX,e.clientY); moveDraw(p[0],p[1]); });
    canvas.addEventListener('mouseup',    endDraw);
    canvas.addEventListener('mouseleave', endDraw);

    // touch
    canvas.addEventListener('touchstart', function(e){ e.preventDefault(); var t=e.touches[0]; var p=scaleXY(t.clientX,t.clientY); startDraw(p[0],p[1]); }, {passive:false});
    canvas.addEventListener('touchmove',  function(e){ e.preventDefault(); var t=e.touches[0]; var p=scaleXY(t.clientX,t.clientY); moveDraw(p[0],p[1]);  }, {passive:false});
    canvas.addEventListener('touchend',   endDraw);

    // ── ล้างลายเซ็น ──────────────────────────────────────────────
    document.getElementById('sig-clear-btn')?.addEventListener('click', function(){
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        dataInp.value = '';
        hasDrawn = false;
    });

    // ── สลับแท็บ ─────────────────────────────────────────────────
    document.querySelectorAll('.btn-sig-tab').forEach(function(btn){
        btn.addEventListener('click', function(){
            var t = this.dataset.sigType;
            typeInp.value = t;
            document.querySelectorAll('.btn-sig-tab').forEach(function(b){
                b.classList.remove('btn-primary');
                b.classList.add('btn-outline-primary');
            });
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-primary');
            document.getElementById('sig-panel-canvas').classList.toggle('d-none', t !== 'canvas');
            document.getElementById('sig-panel-system').classList.toggle('d-none', t !== 'system');
            if (t === 'system') dataInp.value = '';
        });
    });

    // ── restore ลายเซ็นเดิม (กรณีแก้ไข) ─────────────────────────
    var existingData = {$existingSigDataJs};
    if (existingData && typeof existingData === 'string' && existingData.startsWith('data:image')) {
        var img = new Image();
        img.onload = function(){
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            hasDrawn = true;
        };
        img.src = existingData;
    }

    // ── capture canvas → hidden field ก่อน submit ─────────────────
    function captureSignature() {
        if (typeInp.value === 'canvas' && hasDrawn) {
            dataInp.value = canvas.toDataURL('image/png');
        }
    }

    var formCreate = document.getElementById('leave-create-form');
    var formUpdate = document.getElementById('form-leave-update');
    [formCreate, formUpdate].forEach(function(f){
        if (f) f.addEventListener('submit', captureSignature, true);
    });
    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('beforeSubmit', '#leave-create-form, #form-leave-update', captureSignature);
    }
})();
JS;
$this->registerJs($sigJs, \yii\web\View::POS_END);
?>
=======
?>
>>>>>>> dev
