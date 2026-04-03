<?php
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\widgets\ActiveForm;

/** @var app\modules\leave\models\LeaveCreateForm $model */
$this->title = 'สร้างใบลาใหม่';
$this->params['breadcrumbs'][] = ['label' => 'การลางาน', 'url' => ['/leave/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$name = $employee ? trim(($employee->fname ?? '') . ' ' . ($employee->lname ?? '')) : '';
$positionName = $employee && $employee->positionType ? $employee->positionType->title : '';
$phone = $employee->phone ?? '';
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2">
    <a href="<?= Url::to(['/leave/default/index']) ?>" class="btn btn-link btn-sm text-body p-0"><i class="bi bi-arrow-left fs-4"></i></a>
    <h4 class="fw-bold text-body mb-0">สร้างใบลาใหม่</h4>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid py-3">
    <?php $form = ActiveForm::begin([
        'id' => 'leave-create-form',
        'action' => ['/leave/leave/create'],
        'method' => 'post',
        'options' => ['class' => 'row g-4', 'enctype' => 'multipart/form-data'],
        'enableAjaxValidation' => true,
        'validationUrl' => ['/leave/leave/validation'],
        'fieldConfig' => [
            'labelOptions' => ['class' => 'form-label small text-body'],
            'inputOptions' => ['class' => 'form-control'],
            'errorOptions' => ['class' => 'invalid-feedback'],
        ],
    ]); ?>

    <div class="col-12 col-lg-5">
        <!-- การ์ดข้อมูลผู้ใช้ -->
        <?php
        $workShiftLabel = '';
        if ($employee && isset($employee->work_shift)) {
            $workShiftLabel = $employee->work_shift === 'shift' ? 'เวร 8' : 'เวรเช้า';
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

        <!-- เลือกประเภทการลา -->
        <?php
        $lucideIconMap = [
            'calendar-check' => 'calendar-check', 'heart' => 'heart', 'droplet' => 'droplet', 'baby' => 'baby',
            'sun' => 'sun', 'stethoscope' => 'stethoscope', 'palm' => 'palmtree', 'palmtree' => 'palmtree',
            'calendar' => 'calendar', 'briefcase' => 'briefcase', 'coffee' => 'coffee', 'umbrella' => 'umbrella',
            'heart-pulse' => 'heart-pulse', 'syringe' => 'syringe', 'graduation-cap' => 'graduation-cap',
            'book-open' => 'book-open', 'church' => 'church', 'scale' => 'scale', 'power' => 'power',
            'bike' => 'bike', 'user-circle' => 'user-circle',
        ];
        // Font Awesome class (fa-*) → Lucide icon name
        $faToLucide = [
            'fa-stethoscope' => 'stethoscope', 'fa-person-breastfeeding' => 'baby', 'fa-person-circle-exclamation' => 'user-circle',
            'fa-person-biking' => 'bike', 'fa-power-off' => 'power',
        ];
        // รหัสประเภทการลา → [lucide icon, สี] (fallback เมื่อไม่มีใน data_json)
        $typeTheme = [
            'LT1' => ['lucide' => 'stethoscope', 'color' => '#f4cccc'],
            'LT2' => ['lucide' => 'baby', 'color' => '#db2777'],
            'LT3' => ['lucide' => 'user-circle', 'color' => '#dc2626'],
            'LT4' => ['lucide' => 'bike', 'color' => '#2563eb'],
            'LT5' => ['lucide' => 'heart-pulse', 'color' => '#fff2cc'],
            'LT6' => ['lucide' => 'graduation-cap', 'color' => '#d9d2e9'],
            'LT7' => ['lucide' => 'scale', 'color' => '#d9ead3'],
            'LT8' => ['lucide' => 'book-open', 'color' => '#d0e0e3'],
            'LT9' => ['lucide' => 'briefcase', 'color' => '#cfe2f3'],
            'LT10' => ['lucide' => 'heart', 'color' => '#c9daf8'],
            'LT11' => ['lucide' => 'briefcase', 'color' => '#d0e0e3'],
            'LT12' => ['lucide' => 'power', 'color' => '#6c757d'],
        ];
        // ดึง Lucide icon และสีจาก data_json (รองรับ icon เป็น HTML เช่น <i class="fa-solid fa-stethoscope"></i> และ color เป็น hex)
        $getTypeIconColor = function ($t) use ($typeTheme, $lucideIconMap, $faToLucide) {
            $code = $t->code ?? '';
            $theme = $typeTheme[$code] ?? null;
            $json = is_string($t->data_json ?? null) ? json_decode($t->data_json, true) : ($t->data_json ?? []);
            $json = is_array($json) ? $json : [];
            $color = !empty($json['color']) ? $json['color'] : ($theme['color'] ?? '#0d6efd');
            $iconRaw = $json['icon'] ?? null;
            if ($iconRaw !== null && $iconRaw !== '') {
                if (is_string($iconRaw) && (strpos($iconRaw, 'fa-') !== false || strpos($iconRaw, 'class=') !== false)) {
                    if (preg_match_all('/fa-[a-z0-9-]+/i', $iconRaw, $m)) {
                        $faClasses = $m[0];
                        $skip = ['fa-solid', 'fa-regular', 'fa-light', 'fa-duotone', 'fa-brands'];
                        $faClass = 'fa-solid';
                        foreach (array_reverse($faClasses) as $c) {
                            if (!in_array($c, $skip, true)) { $faClass = $c; break; }
                        }
                        $lucide = $faToLucide[$faClass] ?? $lucideIconMap[$faClass] ?? ($theme['lucide'] ?? 'calendar-check');
                    } else {
                        $lucide = $theme['lucide'] ?? 'calendar-check';
                    }
                } else {
                    $lucide = $lucideIconMap[$iconRaw] ?? $iconRaw;
                }
            } else {
                $lucide = $theme['lucide'] ?? 'calendar-check';
            }
            return ['lucide' => $lucide, 'color' => $color];
        };
        ?>
        <?php $hasLeaveTypeError = $model->hasErrors('leave_type_id'); ?>
        <div class="card border-0 shadow-sm rounded-3 mb-4 <?= $hasLeaveTypeError ? 'border border-2 border-danger' : '' ?>">
            <div class="card-body p-3">
                <h6 class="d-flex align-items-center gap-2 fw-bold text-body mb-1">
                    <span class="erp-icon-box bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center">
                        <i data-lucide="calendar-check" style="width:1.125rem;height:1.125rem"></i>
                    </span>
                    เลือกประเภทการลา
                </h6>
                <p class="small text-muted mb-3">กรุณาเลือก 1 ประเภทด้านล่าง</p>
                <?= $form->field($model, 'leave_type_id')->hiddenInput(['id' => 'leave-create-form-leave_type_id'])->label(false) ?>
                <div class="row g-2">
                    <?php foreach ($types as $t):
                        $ic = $getTypeIconColor($t);
                        $lucideIcon = $ic['lucide'];
                        $iconColor = $ic['color'];
                        $iconColorSafe = Html::encode($iconColor);
                        $code = $t->code ?? '';
                        if ($code === '') continue;
                    ?>
                    <div class="col-6">
                        <label class="d-block mb-0 position-relative cursor-pointer leave-type-option rounded-3 border border-2 p-3 text-center text-body text-decoration-none" style="cursor: pointer;" data-type-color="<?= $iconColorSafe ?>" data-type-value="<?= Html::encode($code) ?>">
                            <input type="radio" name="leave_type_radio" value="<?= Html::encode($code) ?>" class="form-check-input position-absolute top-0 end-0 m-2 leave-type-radio" <?= ($model->leave_type_id === $code) ? 'checked' : '' ?>>
                            <i data-lucide="<?= Html::encode($lucideIcon) ?>" class="d-block mb-2 mx-auto leave-type-lucide" style="width:1.75rem;height:1.75rem;color:<?= $iconColorSafe ?>"></i>
                            <span class="small fw-medium"><?= Html::encode($t->title) ?></span>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- สถิติการลา -->
        <div class="card border-0 shadow-sm rounded-3">
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

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <?= $form->field($model, 'date_start')->textInput([
                            'id' => 'leave-date_start',
                            'class' => 'form-control',
                            'placeholder' => 'dd/mm/yyyy',
                        ])->label('ตั้งแต่วันที่') ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'date_end')->textInput([
                            'id' => 'leave-date_end',
                            'class' => 'form-control',
                            'placeholder' => 'dd/mm/yyyy',
                        ])->label('ถึงวันที่') ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small d-block">ลักษณะการลา</label>
                    <div class="d-flex flex-wrap gap-2">
                        <label class="d-flex align-items-center gap-2 rounded-3 border border-2 p-3 cursor-pointer leave-time-option flex-grow-1 flex-md-grow-0" style="cursor: pointer; min-width: 120px;" data-value="1">
                            <input type="radio" name="LeaveCreateForm[leave_time_type]" value="1" class="form-check-input leave-time-radio" <?= ((float) $model->leave_time_type === 1.0) ? 'checked' : '' ?>>
                            <span class="small fw-medium">การลาเต็มวัน</span>
                        </label>
                        <label class="d-flex align-items-center gap-2 rounded-3 border border-2 p-3 cursor-pointer leave-time-option flex-grow-1 flex-md-grow-0" style="cursor: pointer; min-width: 120px;" data-value="0.5">
                            <input type="radio" name="LeaveCreateForm[leave_time_type]" value="0.5" class="form-check-input leave-time-radio" <?= ((float) $model->leave_time_type === 0.5) ? 'checked' : '' ?>>
                            <span class="small fw-medium">ครึ่งวัน</span>
                        </label>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded bg-body-secondary bg-opacity-25">
                    <i class="bi bi-file-text text-primary"></i>
                    <span class="small">รวมวันลา</span>
                    <span class="small text-muted ms-2">คำนวณอัตโนมัติ</span>
                    <span class="badge bg-primary ms-auto text-white" id="leave-total-days">0 วัน</span>
                </div>
                <div class="card border-0 border-start border-3 border-info mb-4">
                    <div class="card-body py-2 px-3">
                        <div class="small fw-semibold text-secondary mb-2"><i class="bi bi-calendar3 me-1"></i> สรุปวันลา</div>
                        <div class="row g-2 small">
                            <div class="col-4 text-center">
                                <div class="text-muted">รวมระยะเวลา</div>
                                <div class="fw-semibold" id="leave-summary-total">0 วัน</div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="text-muted">วันเสาร์-อาทิตย์</div>
                                <div class="fw-semibold" id="leave-summary-satsun">0 วัน</div>
                            </div>
                            <div class="col-4 text-center">
                                <div class="text-muted">วันหยุดนักขัตฤกษ์</div>
                                <div class="fw-semibold" id="leave-summary-holiday">0 วัน</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <?= $form->field($model, 'reason')->textarea([
                        'class' => 'form-control rounded-3',
                        'rows' => 3,
                        'placeholder' => 'เช่น ป่วยเป็นไข้หวัด, ติดธุระทางครอบครัว...',
                    ])->label('สาเหตุการลา') ?>
                </div>
                <div class="mb-3">
                    <?= $form->field($model, 'contact_phone')->textInput([
                        'class' => 'form-control rounded-3',
                        'placeholder' => 'เช่น 08x-xxx-xxxx',
                    ])->label('เบอร์โทรติดต่อ') ?>
                </div>
                <div class="mb-3">
                    <?= $form->field($model, 'place_go')->textInput([
                        'class' => 'form-control rounded-3',
                        'placeholder' => 'สถานที่ที่ไประหว่างลาหรือปล่อยว่าง',
                    ])->label('สถานที่ไป') ?>
                </div>
                <div class="mb-4">
                    <label class="form-label small">เอกสารแนบ / ใบรับรองแพทย์</label>
                    <input type="file" name="leave_attachments[]" class="form-control rounded-3" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" multiple>
                    <div class="form-text small text-muted">รองรับ PDF, รูปภาพ, Word (หลายไฟล์ได้)</div>
                </div>

                <div class="mb-4">
                    <?= $form->field($model, 'address')->textarea([
                        'class' => 'form-control rounded-3',
                        'rows' => 3,
                        'placeholder' => 'บ้านเลขที่ หมู่ ถนน ตำบล อำเภอ....',
                    ])->label('ที่อยู่ที่ติดต่อได้') ?>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary rounded-3 px-4">
                        ถัดไป <i class="bi bi-arrow-right ms-1"></i>
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
$summaryDaysUrl = \yii\helpers\Url::to(['/leave/leave/summary-days']);
$js = <<<JS
(function(){
    var summaryDaysUrl = "{$summaryDaysUrl}";
    function parseThaiDate(str) {
        if (!str || str.length < 8) return null;
        var parts = str.split('/');
        if (parts.length !== 3) return null;
        var d = parseInt(parts[0],10), m = parseInt(parts[1],10)-1, y = parseInt(parts[2],10)-543;
        if (y < 2400) y += 543;
        var date = new Date(y, m, d);
        return isNaN(date.getTime()) ? null : date;
    }
    function getLeaveTimeType() {
        var r = document.querySelector('input[name="LeaveCreateForm[leave_time_type]"]:checked');
        return r ? parseFloat(r.value) : 1;
    }
    function calDays() {
        var start = document.getElementById('leave-date_start');
        var end = document.getElementById('leave-date_end');
        if (!start || !end) return;
        var d1 = parseThaiDate(start.value);
        var d2 = parseThaiDate(end.value);
        if (!d1 || !d2) { updateSummary(0, 0, 0, 0); return; }
        updateSummaryViaAjax(start.value, end.value, getLeaveTimeType());
    }
    function updateSummary(calendarDays, totalLeaveDays, satSun, holiday) {
        var elSummaryTotal = document.getElementById('leave-summary-total');
        var elSatSun = document.getElementById('leave-summary-satsun');
        var elHoliday = document.getElementById('leave-summary-holiday');
        var elBadge = document.getElementById('leave-total-days');
        if (elSummaryTotal) elSummaryTotal.textContent = (calendarDays || 0) + ' วัน';
        if (elSatSun) elSatSun.textContent = (satSun || 0) + ' วัน';
        if (elHoliday) elHoliday.textContent = (holiday || 0) + ' วัน';
        if (elBadge) elBadge.textContent = (totalLeaveDays != null ? totalLeaveDays : 0) + ' วัน';
    }
    function updateSummaryViaAjax(dateStartTh, dateEndTh, leaveTimeType) {
        if (!dateStartTh || !dateEndTh || dateStartTh.length < 8 || dateEndTh.length < 8) {
            updateSummary(0, 0, 0, 0);
            return;
        }
        var params = new URLSearchParams({ date_start: dateStartTh, date_end: dateEndTh, leave_time_type: leaveTimeType });
        fetch(summaryDaysUrl + '?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                updateSummary(data.calendar_days || 0, data.total_leave_days || 0, data.sat_sun_days || 0, data.holiday_days || 0);
            })
            .catch(function() { updateSummary(0, 0, 0, 0); });
    }
    function bindCalDays() {
        var startEl = document.getElementById('leave-date_start');
        var endEl = document.getElementById('leave-date_end');
        if (!startEl || !endEl) return;
        ['change', 'input', 'blur'].forEach(function(ev) {
            startEl.addEventListener(ev, calDays);
            endEl.addEventListener(ev, calDays);
        });
        var jq = typeof jQuery !== 'undefined' ? jQuery : null;
        if (jq) {
            [startEl, endEl].forEach(function(el) {
                var w = jq(el).data('xdsoft_datetimepicker');
                if (w) {
                    w.on('close.xdsoft', calDays);
                    w.on('changedatetime.xdsoft', calDays);
                }
            });
        }
    }
    bindCalDays();
    calDays();
    document.querySelectorAll('input[name="LeaveCreateForm[leave_time_type]"]').forEach(function(r){
        r.addEventListener('change', calDays);
    });
    document.querySelectorAll('.leave-time-option').forEach(function(el){
        el.addEventListener('click', function(){
            el.querySelector('input[type=radio]').checked = true;
            document.querySelectorAll('.leave-time-option').forEach(function(o){
                o.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
                o.style.borderColor = '';
                o.style.backgroundColor = '';
            });
            el.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
            el.style.borderColor = '';
            el.style.backgroundColor = '';
            calDays();
        });
    });
    document.querySelector('.leave-time-option[data-value="1"]').classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
    var hiddenLeaveType = document.getElementById('leave-create-form-leave_type_id');
    function syncLeaveTypeToHidden() {
        if (!hiddenLeaveType) return;
        var r = document.querySelector('input[name="leave_type_radio"]:checked');
        hiddenLeaveType.value = r ? r.value : '';
    }
    document.querySelectorAll('.leave-type-option').forEach(function(el){
        el.addEventListener('click', function(){
            el.querySelector('input[type=radio]').checked = true;
            syncLeaveTypeToHidden();
            var color = el.getAttribute('data-type-color') || '#0d6efd';
            document.querySelectorAll('.leave-type-option').forEach(function(o){
                o.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
                o.style.borderColor = '';
                o.style.backgroundColor = '';
            });
            el.style.borderColor = color;
            el.style.backgroundColor = color + '18';
        });
    });
    document.querySelectorAll('input[name="leave_type_radio"]').forEach(function(r){
        r.addEventListener('change', syncLeaveTypeToHidden);
    });
    syncLeaveTypeToHidden();
    if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
