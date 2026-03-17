<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap5\ActiveForm;
use yii\web\JsExpression;
use yii\web\View;
use app\widgets\TomSelectWidget;
use app\modules\jd\models\JdTemplate;
use app\components\CategoriseHelper;
use app\components\AppHelper;
use app\widgets\datepicker\DatepickerThai;
use kartik\widgets\Select2;

/** @var yii\web\View $this */
/** @var JdTemplate $model */

$positionItems = ['' => '-- เลือกตำแหน่งงาน --'] + CategoriseHelper::PositionName();
$positionTypeFromDb = CategoriseHelper::PositionType();
$employmentTypeItems = empty($positionTypeFromDb) ? JdTemplate::employmentTypeOptions() : (['' => '-- เลือก --'] + $positionTypeFromDb);

/** แปลงข้อความหลายบรรทัดเป็น array สำหรับแสดงทีละหัวข้อ */
$toLines = function ($text) {
    if ($text === null || $text === '') {
        return [];
    }
    $lines = preg_split('/\r\n|\r|\n/', (string) $text, -1, PREG_SPLIT_NO_EMPTY);
    return array_map('trim', $lines);
};
?>
<?php $form = ActiveForm::begin(['id' => 'jd-template-form']); ?>

<ul class="nav nav-tabs mb-4" id="jdFormTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="ftab1-tab" data-bs-toggle="tab" data-bs-target="#ftab1" type="button" role="tab">
            <i class="bi bi-tag me-1"></i> ข้อมูลพื้นฐาน
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="ftab2-tab" data-bs-toggle="tab" data-bs-target="#ftab2" type="button" role="tab">
            <i class="bi bi-person-check me-1"></i> คุณสมบัติ
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="ftab3-tab" data-bs-toggle="tab" data-bs-target="#ftab3" type="button" role="tab">
            <i class="bi bi-lightbulb me-1"></i> สมรรถนะ + KPI
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="ftab4-tab" data-bs-toggle="tab" data-bs-target="#ftab4" type="button" role="tab">
            <i class="bi bi-cash-coin me-1"></i> ค่าตอบแทน
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="ftab5-tab" data-bs-toggle="tab" data-bs-target="#ftab5" type="button" role="tab">
            <i class="bi bi-graph-up-arrow me-1"></i> สภาพแวดล้อม + HR
        </button>
    </li>
</ul>

<div class="tab-content" id="jdFormTabContent">

    <!-- ─── Tab 1: ข้อมูลพื้นฐาน + วัตถุประสงค์ ─── -->
    <div class="tab-pane fade show active" id="ftab1" role="tabpanel">
        <div class="row g-3">
            <div class="col-md-8">
                <?= $form->field($model, 'name')
                    ->textInput(['maxlength' => true, 'class' => 'form-control', 'placeholder' => 'เช่น JD นักพัฒนาซอฟต์แวร์ ระดับ Senior'])
                    ->label('ชื่อ Template <span class="text-danger">*</span>', ['encode' => false]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'is_active')
                    ->dropDownList([1 => 'ใช้งาน', 0 => 'ปิดใช้'], ['class' => 'form-select'])
                    ->label('สถานะ') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'position_code')->widget(TomSelectWidget::class, [
                    'items'         => $positionItems,
                    'options'       => ['class' => 'form-select'],
                    'clientOptions' => ['placeholder' => '-- เลือกตำแหน่งงาน --', 'allowEmptyOption' => true],
                ])->label('ตำแหน่งงาน <span class="text-danger">*</span>', ['encode' => false]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'job_code')
                    ->textInput(['maxlength' => true, 'class' => 'form-control', 'placeholder' => 'เช่น DEV-001'])
                    ->label('รหัสตำแหน่ง (Job Code)') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'job_level')
                    ->dropDownList(JdTemplate::jobLevelOptions(), ['class' => 'form-select'])
                    ->label('ระดับตำแหน่ง (Job Level)') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'department')
                    ->textInput(['maxlength' => true, 'class' => 'form-control', 'placeholder' => 'เช่น ฝ่ายเทคโนโลยีสารสนเทศ'])
                    ->label('แผนก / ฝ่าย') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'report_to')
                    ->textInput(['maxlength' => true, 'class' => 'form-control', 'placeholder' => 'เช่น ผู้จัดการฝ่าย IT'])
                    ->label('รายงานตรงต่อ') ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'has_subordinates')
                    ->dropDownList([0 => 'ไม่มี', 1 => 'มี'], ['class' => 'form-select'])
                    ->label('ผู้ใต้บังคับบัญชา') ?>
            </div>
            <div class="col-12">
                <p class="text-muted small mb-1">อธิบายภาพรวมว่าตำแหน่งนี้มีเหตุผลในการดำรงอยู่เพื่ออะไร มีบทบาทสำคัญต่อองค์กรอย่างไร (3–5 บรรทัด)</p>
                <?= $form->field($model, 'job_purpose')
                    ->textarea(['rows' => 5, 'class' => 'form-control', 'placeholder' => 'ระบุวัตถุประสงค์หลักของตำแหน่งนี้...'])
                    ->label('วัตถุประสงค์ของตำแหน่ง (Job Purpose)') ?>
            </div>
        </div>
    </div>

    <!-- ─── Tab 2: คุณสมบัติที่ต้องการ ─── -->
    <div class="tab-pane fade" id="ftab2" role="tabpanel">
        <div class="row g-3">
            <div class="col-12">
                <?= $form->field($model, 'edu_requirement')
                    ->textarea(['rows' => 3, 'class' => 'form-control', 'placeholder' => 'เช่น ปริญญาตรี สาขาวิทยาการคอมพิวเตอร์ หรือสาขาที่เกี่ยวข้อง'])
                    ->label('การศึกษา') ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'exp_years')
                    ->textInput(['type' => 'number', 'min' => 0, 'class' => 'form-control', 'placeholder' => '0'])
                    ->label('ประสบการณ์ขั้นต่ำ (ปี)') ?>
            </div>
            <div class="col-md-10">
                <?= $form->field($model, 'exp_detail')
                    ->textarea(['rows' => 3, 'class' => 'form-control', 'placeholder' => 'ระบุลักษณะงานที่ต้องการ เช่น เคยพัฒนา web application ขนาดใหญ่ เคยบริหารทีม ฯลฯ'])
                    ->label('รายละเอียดประสบการณ์') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'hard_skills')
                    ->textarea(['rows' => 6, 'class' => 'form-control', 'placeholder' => "เช่น\n- PHP, Python, JavaScript\n- MySQL, Redis\n- Docker, Kubernetes\n- ใบรับรอง AWS Solutions Architect"])
                    ->label('ทักษะเฉพาะทาง (Hard Skills)') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'soft_skills')
                    ->textarea(['rows' => 6, 'class' => 'form-control', 'placeholder' => "เช่น\n- การสื่อสารที่ชัดเจน\n- ภาวะผู้นำ\n- การแก้ปัญหาเชิงระบบ\n- การทำงานภายใต้แรงกดดัน"])
                    ->label('ทักษะด้านพฤติกรรม (Soft Skills)') ?>
            </div>
        </div>
    </div>

    <!-- ─── Tab 3: สมรรถนะ + KPI ─── -->
    <div class="tab-pane fade" id="ftab3" role="tabpanel">
        <div class="row g-3">
            <div class="col-12">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1">Core</span>
                    <span class="fw-medium">Core Competency — สมรรถนะที่ทุกคนในองค์กรต้องมี</span>
                    <button type="button" class="btn btn-sm btn-outline-primary ms-2 btn-add-row" data-target="core-competency-rows" data-placeholder="เช่น ความซื่อสัตย์และจริยธรรม">
                        <i class="bi bi-plus-lg me-1"></i> เพิ่มหัวข้อ
                    </button>
                </div>
                <?= Html::activeHiddenInput($model, 'core_competency', ['id' => 'jd-template-core_competency']) ?>
                <div id="core-competency-rows" class="jd-item-rows mb-2">
                    <?php foreach ($toLines($model->core_competency) as $line): ?>
                    <div class="input-group mb-2 jd-row">
                        <input type="text" class="form-control jd-row-input" value="<?= Html::encode($line) ?>" placeholder="สมรรถนะข้อที่...">
                        <button type="button" class="btn btn-outline-danger btn-remove-row" title="ลบ"><i class="bi bi-trash"></i></button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mb-2">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-add-row" data-target="core-competency-rows" data-placeholder="เช่น ความซื่อสัตย์และจริยธรรม">
                        <i class="bi bi-plus-lg me-1"></i> เพิ่มหัวข้อ
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1">Functional</span>
                    <span class="fw-medium">Functional Competency — สมรรถนะเฉพาะสายงาน</span>
                </div>
                <?= $form->field($model, 'functional_competency')
                    ->textarea(['rows' => 4, 'class' => 'form-control', 'placeholder' => 'สมรรถนะเฉพาะด้านของสายงานนี้...'])
                    ->label(false) ?>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1">Leadership</span>
                    <span class="fw-medium">Leadership Competency — สำหรับตำแหน่งระดับหัวหน้าขึ้นไป</span>
                </div>
                <?= $form->field($model, 'leadership_competency')
                    ->textarea(['rows' => 4, 'class' => 'form-control', 'placeholder' => 'ระบุเฉพาะตำแหน่งระดับหัวหน้าขึ้นไป...'])
                    ->label(false) ?>
            </div>
            <div class="col-12">
                <hr class="my-2">
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <p class="text-muted small mb-0">ระบุ KPI หลักที่ใช้วัดความสำเร็จของตำแหน่งนี้</p>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-add-row" data-target="kpis-rows" data-placeholder="เช่น ความพึงพอใจของผู้ใช้งาน ≥ 90%">
                        <i class="bi bi-plus-lg me-1"></i> เพิ่มหัวข้อ
                    </button>
                </div>
                <?= Html::activeHiddenInput($model, 'kpis', ['id' => 'jd-template-kpis']) ?>
                <div id="kpis-rows" class="jd-item-rows mb-2">
                    <?php foreach ($toLines($model->kpis) as $line): ?>
                    <div class="input-group mb-2 jd-row">
                        <input type="text" class="form-control jd-row-input" value="<?= Html::encode($line) ?>" placeholder="ตัวชี้วัดผลงาน...">
                        <button type="button" class="btn btn-outline-danger btn-remove-row" title="ลบ"><i class="bi bi-trash"></i></button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mb-2">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-add-row" data-target="kpis-rows" data-placeholder="เช่น จำนวน Feature ที่ส่งมอบต่อ Sprint ≥ 3">
                        <i class="bi bi-plus-lg me-1"></i> เพิ่มหัวข้อ
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ─── Tab 4: โครงสร้างค่าตอบแทน ─── -->
    <div class="tab-pane fade" id="ftab4" role="tabpanel">
        <div class="row g-3">
            <div class="col-md-4">
                <?= $form->field($model, 'salary_min')
                    ->textInput(['type' => 'number', 'min' => 0, 'class' => 'form-control', 'placeholder' => '25000'])
                    ->label('เงินเดือนต่ำสุด (บาท)') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'salary_max')
                    ->textInput(['type' => 'number', 'min' => 0, 'class' => 'form-control', 'placeholder' => '60000'])
                    ->label('เงินเดือนสูงสุด (บาท)') ?>
            </div>
            <div class="col-md-4 d-flex align-items-end pb-3">
                <p class="text-muted small mb-0">ช่วงเงินเดือนนี้ใช้สำหรับการสรรหาและความเป็นธรรมในองค์กร (ไม่แสดงต่อพนักงานทั่วไป)</p>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <label class="form-label mb-0 fw-medium">สวัสดิการหลัก</label>
                    <button type="button" class="btn btn-sm btn-outline-primary btn-add-row" data-target="benefits-rows" data-placeholder="เช่น ประกันสุขภาพกลุ่ม">
                        <i class="bi bi-plus-lg me-1"></i> เพิ่มหัวข้อ
                    </button>
                </div>
                <?= Html::activeHiddenInput($model, 'benefits', ['id' => 'jd-template-benefits']) ?>
                <div id="benefits-rows" class="jd-item-rows mb-2">
                    <?php foreach ($toLines($model->benefits) as $line): ?>
                    <div class="input-group mb-2 jd-row">
                        <input type="text" class="form-control jd-row-input" value="<?= Html::encode($line) ?>" placeholder="สวัสดิการ...">
                        <button type="button" class="btn btn-outline-danger btn-remove-row" title="ลบ"><i class="bi bi-trash"></i></button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mb-2">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-add-row" data-target="benefits-rows" data-placeholder="เช่น กองทุนสำรองเลี้ยงชีพ">
                        <i class="bi bi-plus-lg me-1"></i> เพิ่มหัวข้อ
                    </button>
                </div>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'variable_pay')
                    ->textarea(['rows' => 6, 'class' => 'form-control', 'placeholder' => "เช่น\n- โบนัสประจำปี 2–4 เดือน (ขึ้นกับผลงาน)\n- ค่าคอมมิชชั่น\n- ค่าล่วงเวลา (OT)\n- Incentive"])
                    ->label('ค่าตอบแทนผันแปร') ?>
            </div>
        </div>
    </div>

    <!-- ─── Tab 5: สภาพแวดล้อม + เส้นทางอาชีพ + HR Analytics ─── -->
    <div class="tab-pane fade" id="ftab5" role="tabpanel">
        <div class="row g-3">
            <div class="col-12">
                <h6 class="fw-medium text-muted mb-2">สภาพแวดล้อมและเงื่อนไขการทำงาน</h6>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'work_type')
                    ->dropDownList(JdTemplate::workTypeOptions(), ['class' => 'form-select'])
                    ->label('รูปแบบการทำงาน') ?>
            </div>
            <div class="col-md-5">
                <?= $form->field($model, 'work_location')
                    ->textInput(['maxlength' => true, 'class' => 'form-control', 'placeholder' => 'เช่น สำนักงานใหญ่ กรุงเทพฯ'])
                    ->label('สถานที่ปฏิบัติงาน') ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'work_hours')
                    ->textInput(['maxlength' => true, 'class' => 'form-control', 'placeholder' => 'เช่น จันทร์–ศุกร์ 08.30–17.30 น.'])
                    ->label('เวลาทำงาน / กะ') ?>
            </div>
            <div class="col-12">
                <?= $form->field($model, 'work_conditions')
                    ->textarea(['rows' => 3, 'class' => 'form-control', 'placeholder' => 'สภาพแวดล้อมพิเศษ เช่น พื้นที่อุตสาหกรรม ต้องเดินทางต่างประเทศ ทำงานในสภาพอากาศร้อน'])
                    ->label('สภาพแวดล้อมพิเศษ') ?>
            </div>

            <div class="col-12 mt-2">
                <h6 class="fw-medium text-muted mb-2">เส้นทางความก้าวหน้า (Career Path)</h6>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'career_vertical')
                    ->textarea(['rows' => 4, 'class' => 'form-control', 'placeholder' => "ตำแหน่งที่เติบโตขึ้นไปได้ (เลื่อนระดับ)\nเช่น Senior Dev → Tech Lead → Engineering Manager"])
                    ->label('เส้นทางแนวดิ่ง (Vertical)') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'career_lateral')
                    ->textarea(['rows' => 4, 'class' => 'form-control', 'placeholder' => "ตำแหน่งที่สามารถย้ายสายงานได้ (ระดับเดียวกัน)\nเช่น Backend Dev → DevOps Engineer / Data Engineer"])
                    ->label('เส้นทางแนวราบ (Lateral)') ?>
            </div>

            <div class="col-12 mt-2">
                <h6 class="fw-medium text-muted mb-2">ข้อมูล HR Analytics</h6>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'employment_type')
                    ->dropDownList($employmentTypeItems, ['class' => 'form-select'])
                    ->label('ประเภทการจ้าง') ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'headcount')
                    ->textInput(['type' => 'number', 'min' => 1, 'class' => 'form-control', 'placeholder' => '1'])
                    ->label('Headcount (อัตรา)') ?>
            </div>
            <div class="col-md-4">
                <label class="form-label">ผู้อนุมัติ JD (ดึงจากโครงสร้างหัวหน้า/ผังองค์กร <a href="<?= Url::to(['/hr/organization/diagram']) ?>" target="_blank" class="small">ตั้งค่า <i class="bi bi-box-arrow-up-right"></i></a> — เลือกพนักงานที่อนุมัติ JD)</label>
                <?= Html::activeHiddenInput($model, 'jd_approved_by', ['id' => 'jd-approved-by']) ?>
                <?php
                $resultsJs = "function(data,p){p.page=p.page||1;return{results:data.results,pagination:{more:(p.page*30)<(data.total_count||999)}};}";
                $formatRepo = "var formatRepo=function(repo){if(repo.loading)return repo.avatar;return '<div>'+repo.avatar+'</div>';};";
                $this->registerJs($formatRepo, View::POS_HEAD);
                ?>
                <?= Select2::widget([
                    'name' => 'jd_approved_picker',
                    'value' => '',
                    'initValueText' => $model->jd_approved_by ?: '— เลือกพนักงาน —',
                    'options' => ['placeholder' => 'พิมพ์ชื่อเพื่อค้นหา...', 'id' => 'jd-approved-picker'],
                    'pluginEvents' => [
                        'select2:select' => new JsExpression('function(e) {
                            var d = $(this).select2("data")[0];
                            if (d && d.id) {
                                var pos = (d.position_name_text || d.position_name || "").trim();
                                $("#jd-approved-by").val(pos ? (d.fullname + " (" + pos + ")") : (d.fullname || ""));
                            }
                        }'),
                        'select2:clear' => new JsExpression('function() { $("#jd-approved-by").val(""); }'),
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'minimumInputLength' => 0,
                        'ajax' => [
                            'url' => Url::to(['/depdrop/employee-by-id']),
                            'dataType' => 'json',
                            'delay' => 250,
                            'data' => new JsExpression('function(params) { return {q: params.term || "", page: params.page || 1}; }'),
                            'processResults' => new JsExpression($resultsJs),
                            'cache' => true,
                        ],
                        'escapeMarkup' => new JsExpression('function(m) { return m; }'),
                        'templateSelection' => new JsExpression('function(item) { return item.fullname || item.text || item.id || ""; }'),
                        'templateResult' => new JsExpression('formatRepo'),
                    ],
                ]) ?>
            </div>
            <div class="col-md-2">
                <?= $form->field($model, 'jd_approved_at')->widget(DatepickerThai::class, [
                    'options' => [
                        'placeholder' => 'ว/ด/พ.ศ.',
                        'value' => $model->jd_approved_at ? AppHelper::convertToThai($model->jd_approved_at) : '',
                    ],
                ])->label('วันที่อนุมัติ') ?>
            </div>
        </div>
    </div>

</div><!-- end tab-content -->

<div class="d-flex gap-2 mt-4 pt-3 border-top">
    <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
    <?php if ($model->isNewRecord): ?>
        <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    <?php else: ?>
        <?= Html::a('ยกเลิก', ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
    <?php endif; ?>
</div>

<?php ActiveForm::end(); ?>
<?php \app\widgets\datepicker\Assets::register($this); ?>
<?php
$fieldMapJs = json_encode([
    'core-competency-rows' => 'jd-template-core_competency',
    'kpis-rows' => 'jd-template-kpis',
    'benefits-rows' => 'jd-template-benefits',
]);
$this->registerJs(<<<JS
(function(){
    var fieldMap = {$fieldMapJs};

    function addRow(containerId, placeholder) {
        var container = document.getElementById(containerId);
        if (!container) return;
        var div = document.createElement("div");
        div.className = "input-group mb-2 jd-row";
        div.innerHTML = '<input type="text" class="form-control jd-row-input" placeholder="' + (placeholder || "") + '">' +
            '<button type="button" class="btn btn-outline-danger btn-remove-row" title="ลบ"><i class="bi bi-trash"></i></button>';
        container.appendChild(div);
    }

    document.getElementById("jd-template-form").addEventListener("submit", function() {
        Object.keys(fieldMap).forEach(function(containerId) {
            var hid = document.getElementById(fieldMap[containerId]);
            var container = document.getElementById(containerId);
            if (!hid || !container) return;
            var vals = [];
            container.querySelectorAll(".jd-row-input").forEach(function(inp) {
                var v = (inp.value || "").trim();
                if (v) vals.push(v);
            });
            hid.value = vals.join("\\n");
        });
    });

    document.querySelectorAll(".btn-add-row").forEach(function(btn) {
        btn.addEventListener("click", function() {
            addRow(this.getAttribute("data-target"), this.getAttribute("data-placeholder") || "");
        });
    });

    document.addEventListener("click", function(e) {
        if (e.target.closest(".btn-remove-row")) {
            var row = e.target.closest(".jd-row");
            if (row) row.remove();
        }
    });
})();
JS
, View::POS_END);
?>
