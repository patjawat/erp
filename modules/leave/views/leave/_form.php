<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use yii\web\JsExpression;
use app\modules\filemanager\components\FileManagerHelper;

/** @var app\modules\leave\models\LeaveCreateForm|app\modules\leave\models\Leave $model */
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
$attrWorkShift   = $isUpdate ? 'data_json[work_shift]' : 'work_shift';
$attrReason     = $isUpdate ? 'data_json[reason]' : 'reason';
$attrPhone      = $isUpdate ? 'data_json[phone]' : 'contact_phone';
$attrPlaceGo    = $isUpdate ? 'data_json[place_go]' : 'place_go';
$attrAddress    = $isUpdate ? 'data_json[address]' : 'address';
$attrLeaveSend  = $isUpdate ? 'data_json[leave_work_send_id]' : 'leave_work_send_id';
$attrLeaveName  = $isUpdate ? 'data_json[leave_work_send_name]' : 'leave_work_send_name';

$this->title = $isUpdate ? 'แก้ไขใบลา' : 'สร้างใบลาใหม่';
$this->params['breadcrumbs'][] = ['label' => 'การลางาน', 'url' => ['/leave/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$name = $employee ? trim(($employee->fname ?? '') . ' ' . ($employee->lname ?? '')) : '';
$positionName = $employee && $employee->positionType ? $employee->positionType->title : '';
$phone = $employee->phone ?? '';
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2">
    <a href="<?= Url::to(['/leave/default/index']) ?>" class="btn btn-link btn-sm text-body p-0"><i class="bi bi-arrow-left fs-4"></i></a>
    <h4 class="fw-bold text-body mb-0"><?= $isUpdate ? 'แก้ไขใบลา' : 'สร้างใบลาใหม่' ?></h4>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid py-3">
    <?php $form = ActiveForm::begin([
        'id' => $isUpdate ? 'form-leave-update' : 'leave-create-form',
        'action' => $isUpdate ? ['/leave/leave/update', 'id' => $model->id] : ['/leave/leave/create'],
        'method' => 'post',
        'options' => ['class' => 'row g-4'],
        'enableAjaxValidation' => true,
        'validationUrl' => $isUpdate ? ['/leave/leave/update-validation', 'id' => $model->id] : ['/leave/leave/validation'],
        'fieldConfig' => [
            'labelOptions' => ['class' => 'form-label small text-body'],
            'inputOptions' => ['class' => 'form-control'],
            'errorOptions' => ['class' => 'invalid-feedback'],
        ],
    ]); ?>
    <?php if ($isUpdate): ?>
    <?= $form->field($model, 'id')->hiddenInput()->label(false) ?>
    <?php endif; ?>

    <div class="col-12 col-lg-5">
        <!-- การ์ดข้อมูลผู้ใช้ -->
        <?php
        $workShiftLabel = '';
        if ($employee && isset($employee->work_shift)) {
            $workShiftLabel = $employee->work_shift === 'shift' ? 'เวร 8' : 'เวรปกติ';
        }
        ?>
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle overflow-hidden border border-2 border-primary bg-body-secondary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 56px; height: 56px;">
                        <?php
                        if ($employee && method_exists($employee, 'ShowAvatar')) {
                            $photoUrl = $employee->ShowAvatar();
                            echo Html::img($photoUrl, [
                                'alt' => Html::encode($name),
                                'class' => 'w-100 h-100 object-fit-cover',
                                'style' => 'object-fit: cover;',
                            ]);
                        } else {
                            echo '<i class="bi bi-person fs-2 text-muted"></i>';
                        }
                        ?>
                    </div>
                    <div>
                        <div class="fw-bold text-body"><?= Html::encode($name) ?></div>
                        <div class="small text-muted"><?= Html::encode($positionName) ?></div>
                        <?php if ($workShiftLabel !== ''): ?>
                        <div class="small text-secondary">🕐 <?= Html::encode($workShiftLabel) ?></div>
                        <?php endif; ?>
                        <div class="small text-primary">☎ <?= Html::encode($phone) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!$isUpdate): ?>
        <!-- สถิติการลา -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                    <h6 class="d-flex align-items-center gap-2 fw-bold text-body mb-0">
                        <span class="erp-icon-box bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center">
                            <i data-lucide="bar-chart-2" style="width:1.125rem;height:1.125rem"></i>
                        </span>
                        สถิติการลา
                    </h6>
                    <span class="small text-muted"><?= Html::encode($roundLabel) ?></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm small mb-0">
                        <thead class="table-primary">
                            <tr>
                                <th>ประเภท</th>
                                <th class="text-center" colspan="2">ลามาแล้ว</th>
                                <th class="text-center" colspan="2">ลาครั้งนี้</th>
                                <th class="text-center" colspan="2">รวม</th>
                            </tr>
                            <tr class="table-light">
                                <th></th>
                                <th class="text-center">ครั้ง</th>
                                <th class="text-center">วัน</th>
                                <th class="text-center">ครั้ง</th>
                                <th class="text-center">วัน</th>
                                <th class="text-center">ครั้ง</th>
                                <th class="text-center">วัน</th>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider">
                            <?php foreach ($stats as $row): ?>
                            <tr>
                                <td><?= Html::encode($row['title']) ?></td>
                                <td class="text-center"><?= (int) $row['used_times'] ?></td>
                                <td class="text-center"><?= (float) $row['used_days'] ?></td>
                                <td class="text-center leave-this-times">0</td>
                                <td class="text-center leave-this-days">0</td>
                                <td class="text-center"><?= (int) $row['used_times'] ?></td>
                                <td class="text-center"><?= (float) $row['used_days'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- เอกสารแนบ / ใบรับรองแพทย์ -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3">
                <h6 class="d-flex align-items-center gap-2 fw-bold text-body mb-3">
                    <span class="erp-icon-box bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center">
                        <i data-lucide="paperclip" style="width:1.125rem;height:1.125rem"></i>
                    </span>
                    เอกสารแนบ / ใบรับรองแพทย์
                </h6>
                <?= $isUpdate ? $model->Upload('leave_file') : FileManagerHelper::FileUpload($draftRef, 'leave_file') ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
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
                            ['normal' => 'เวรปกติ', 'shift' => 'เวร 8 ชั่วโมง'],
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
                            'placeholder' => 'dd/mm/yyyy',
                        ])->label('ตั้งแต่วันที่') ?>
                    </div>
                    <div class="col-md-5">
                        <?= $form->field($model, 'date_start_type')->dropDownList(
                            ['0' => 'เต็มวัน', '0.5' => 'ครึ่งวัน'],
                            ['id' => 'leave-date_start_type', 'class' => 'form-select']
                        )->label('ประเภท') ?>
                    </div>
                    <div class="col-md-7">
                        <?= $form->field($model, 'date_end')->textInput([
                            'id' => 'leave-date_end',
                            'class' => 'form-control',
                            'placeholder' => 'dd/mm/yyyy',
                        ])->label('ถึงวันที่') ?>
                    </div>
                    <div class="col-md-5">
                        <?= $form->field($model, 'date_end_type')->dropDownList(
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
                <?php if ($isUpdate): ?>
                <?= $form->field($model, 'total_days')->hiddenInput(['id' => 'leave-total_days'])->label(false) ?>
                <?php else: ?>
                <?= $form->field($model, 'total_days_manual')->hiddenInput(['id' => 'leave-total_days_manual'])->label(false) ?>
                <?php endif; ?>
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
                    <?= $form->field($model, $attrLeaveSend)->widget(Select2::class, $leaveSendWidgetOpts)->label('มอบหมายงานให้') ?>
                    <?= $form->field($model, $attrLeaveName)->hiddenInput(['id' => 'leave-work_send_name'])->label(false) ?>
                </div>

                <?php if ($isUpdate): ?>
                <?= $this->render('@app/modules/hr/views/leave/approve', ['form' => $form, 'model' => $model]) ?>
                <?php endif; ?>

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

                <?php if ($isUpdate): ?>
                <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'emp_id')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'data_json[leave_work_send]')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'data_json[title]')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'data_json[director]')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'data_json[director_fullname]')->hiddenInput()->label(false) ?>
                <?php endif; ?>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary rounded-3 px-4">
                        <?= $isUpdate ? '<i class="bi bi-check2-circle me-1"></i> บันทึก' : 'ถัดไป <i class="bi bi-arrow-right ms-1"></i>' ?>
                    </button>
                </div>
            </div>
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
        var m = document.getElementById('leave-total_days_manual');
        var totalDaysInput = document.getElementById('leave-total_days');
        var b = document.getElementById('leave-total-days');
        if (s) s.value = satsun  != null ? satsun  : 0;
        if (h) h.value = holiday != null ? holiday : 0;
        if (t) t.value = total   != null ? total   : 0;
        if (m) m.value = (total   != null ? total   : 0);
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
                var m = document.getElementById('leave-total_days_manual');
                var b = document.getElementById('leave-total-days');
                if (m) m.value = v;
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

    // โหมดแก้ไข: beforeSubmit ตรวจวันลา + ยืนยัน + ส่ง ajax
    var formUpdate = document.getElementById('form-leave-update');
    if (formUpdate && typeof jQuery !== 'undefined') {
        jQuery(formUpdate).on('beforeSubmit', function(e){
            e.preventDefault();
            var form = jQuery(this);
            var total = parseFloat(document.getElementById('leave-total_days') && document.getElementById('leave-total_days').value) || 0;
            if (total <= 0 && typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'วันลาต้องมากกว่า 0', text: '' });
                return false;
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'ยืนยันบันทึก?', icon: 'question', showCancelButton: true, confirmButtonText: 'บันทึก', cancelButtonText: 'ยกเลิก' }).then(function(r){
                    if (!r.isConfirmed) return;
                    if (document.getElementById('main-modal')) jQuery('#main-modal').modal('hide');
                    Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: function(){ Swal.showLoading(); } });
                    jQuery.ajax({ url: form.attr('action'), type: 'post', data: form.serialize(), dataType: 'json' })
                        .done(function(res){
                            if (res.status === 'success') {
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
        });
    }

    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
