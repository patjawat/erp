<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

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
        'options' => ['class' => 'row g-4'],
    ]); ?>

    <div class="col-12 col-lg-5">
        <!-- การ์ดข้อมูลผู้ใช้ -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle overflow-hidden border border-2 border-primary" style="width: 56px; height: 56px;">
                        <?= $employee ? $employee->getAvatar(false) : '<i class="bi bi-person fs-2 d-block text-center text-muted"></i>' ?>
                    </div>
                    <div>
                        <div class="fw-bold text-body"><?= Html::encode($name) ?></div>
                        <div class="small text-muted"><?= Html::encode($positionName) ?></div>
                        <div class="small text-primary">☎ <?= Html::encode($phone) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- เลือกประเภทการลา -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body p-3">
                <h6 class="d-flex align-items-center gap-2 fw-bold text-body mb-3">
                    <i class="bi bi-droplet text-primary"></i>
                    เลือกประเภทการลา
                </h6>
                <div class="row g-2">
                    <?php foreach ($types as $t):
                        $color = $t->data_json['color'] ?? '#0d6efd';
                        $icon = $t->data_json['icon'] ?? 'calendar-check';
                    ?>
                    <div class="col-6">
                        <label class="d-block mb-0 position-relative cursor-pointer leave-type-option rounded-3 border border-2 p-3 text-center text-body text-decoration-none" style="cursor: pointer;">
                            <input type="radio" name="leave_type_id" value="<?= Html::encode($t->code) ?>" class="form-check-input position-absolute top-0 end-0 m-2 leave-type-radio">
                            <i class="bi bi-<?= Html::encode($icon) ?> d-block fs-3 mb-2 text-secondary"></i>
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
                <h6 class="d-flex align-items-center gap-2 fw-bold text-body mb-2">
                    <i class="bi bi-bar-chart text-primary"></i>
                    สถิติการลา
                </h6>
                <p class="small text-muted mb-2"><?= Html::encode($roundLabel) ?></p>
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
                    <i class="bi bi-pencil-square text-warning"></i>
                    กรอกรายละเอียด
                </h6>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small">ตั้งแต่วันที่</label>
                        <?= Html::textInput('date_start', '', ['id' => 'leave-date_start', 'class' => 'form-control', 'placeholder' => 'dd/mm/yyyy']) ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">ถึงวันที่</label>
                        <?= Html::textInput('date_end', '', ['id' => 'leave-date_end', 'class' => 'form-control', 'placeholder' => 'dd/mm/yyyy']) ?>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 mb-4 p-2 rounded bg-light">
                    <i class="bi bi-file-text text-primary"></i>
                    <span class="small">รวมระยะเวลา</span>
                    <span class="small text-muted ms-2">คำนวณอัตโนมัติ</span>
                    <span class="badge bg-primary ms-auto" id="leave-total-days">0 วัน</span>
                </div>

                <div class="mb-3">
                    <label class="form-label small">สาเหตุการลา</label>
                    <textarea name="reason" class="form-control rounded-3" rows="3" placeholder="เช่น ป่วยเป็นไข้หวัด, ติดธุระทางครอบครัว..."></textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label small">ที่อยู่ที่ติดต่อได้</label>
                    <textarea name="address" class="form-control rounded-3" rows="3" placeholder="บ้านเลขที่ หมู่ ถนน ตำบล อำเภอ...."><?= Html::encode($employee && $employee->fulladdress ? $employee->fulladdress : '') ?></textarea>
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
$js = <<<'JS'
(function(){
    function parseThaiDate(str) {
        if (!str || str.length < 8) return null;
        var parts = str.split('/');
        if (parts.length !== 3) return null;
        var d = parseInt(parts[0],10), m = parseInt(parts[1],10)-1, y = parseInt(parts[2],10)-543;
        if (y < 2400) y += 543;
        var date = new Date(y, m, d);
        return isNaN(date.getTime()) ? null : date;
    }
    function calDays() {
        var start = document.getElementById('leave-date_start');
        var end = document.getElementById('leave-date_end');
        var badge = document.getElementById('leave-total-days');
        if (!start || !end || !badge) return;
        var d1 = parseThaiDate(start.value);
        var d2 = parseThaiDate(end.value);
        if (!d1 || !d2) { badge.textContent = '0 วัน'; return; }
        var diff = Math.round((d2 - d1) / 86400000) + 1;
        badge.textContent = (diff > 0 ? diff : 0) + ' วัน';
    }
    document.getElementById('leave-date_start').addEventListener('change', calDays);
    document.getElementById('leave-date_end').addEventListener('change', calDays);
    document.querySelectorAll('.leave-type-option').forEach(function(el){
        el.addEventListener('click', function(){ el.querySelector('input[type=radio]').checked = true;
            document.querySelectorAll('.leave-type-option').forEach(function(o){ o.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10'); });
            el.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
        });
    });
})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
