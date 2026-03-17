<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use kartik\widgets\ActiveForm;
use app\components\CategoriseHelper;
use app\modules\hr\models\Employees;
use app\widgets\datepicker\DatepickerThai;

/** @var yii\web\View $this */
/** @var app\modules\development\models\Development $model */
/** @var yii\widgets\ActiveForm $form */
$emp = UserHelper::GetEmployee();
$url = Url::to(['/depdrop/employee-by-id']);
$listDocumentMe = $emp ? $emp->listDocumentMe() : [];
$isNewRecord = $model->isNewRecord;
$formAction = $isNewRecord
    ? Url::to(['/development/default/create'])
    : Url::to(['/development/default/update', 'id' => $model->id]);
?>

<?php $form = ActiveForm::begin([
    'id' => 'form-development',
    'action' => $formAction,
    'options' => ['class' => 'development-form'],
    'fieldConfig' => [
        'labelOptions' => ['class' => 'form-label fw-medium text-body mb-1'],
        'inputOptions' => ['class' => 'form-control'],
        'errorOptions' => ['class' => 'invalid-feedback'],
    ],
]); ?>

<!-- 1. ข้อมูลเอกสาร -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-transparent border-0 border-bottom border-secondary border-opacity-25 py-3 px-3 px-md-4">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-semibold text-body d-flex align-items-center">
                <span class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center me-2 p-2 small">1</span>
                <i class="bi bi-file-earmark-text text-primary me-2"></i>ข้อมูลเอกสาร
            </h6>
            <span class="text-muted small d-none d-sm-inline">ขั้นที่ 1/4</span>
        </div>
    </div>
    <div class="card-body p-3 p-md-4">
        <div class="row g-3">
            <div class="col-12 col-md-3">
                <?= $form->field($model, 'thai_year')->textInput(['placeholder' => 'ปี พ.ศ.']) ?>
                <?= $form->field($model, 'data_json[doc_number]')->textInput(['placeholder' => 'เลขที่หนังสือ'])->label('เลขที่หนังสือ') ?>
            </div>
            <div class="col-12 col-md-9">
                <?php
                echo $form->field($model, 'document_id')->widget(Select2::classname(), [
                    'data' => ArrayHelper::map($listDocumentMe, 'id', 'topic'),
                    'options' => ['placeholder' => 'เลือกหนังสืออ้างอิง ...', 'class' => 'form-select'],
                    'pluginOptions' => ['allowClear' => true],
                    'pluginEvents' => [
                        'select2:select' => new JsExpression("function(e) {
                            var data = e.params.data;
                            $('#development-topic').val(data.text);
                        }"),
                    ],
                ])->label('หนังสืออ้างอิง');
                ?>
            </div>
        </div>
    </div>
</div>

<!-- 2. รายละเอียดการพัฒนา -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-transparent border-0 border-bottom border-secondary border-opacity-25 py-3 px-3 px-md-4">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-semibold text-body d-flex align-items-center">
                <span class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center me-2 p-2 small">2</span>
                <i class="bi bi-info-circle text-primary me-2"></i>รายละเอียดการพัฒนา
            </h6>
            <span class="text-muted small d-none d-sm-inline">ขั้นที่ 2/4</span>
        </div>
    </div>
    <div class="card-body p-3 p-md-4">
        <div class="row g-3">
            <div class="col-12">
                <?= $form->field($model, 'topic')->textInput(['maxlength' => true, 'placeholder' => 'ระบุหัวข้อการอบรม/ประชุม/ดูงาน']) ?>
            </div>
            <div class="col-12 col-md-6">
                <div class="border border-secondary border-opacity-25 rounded-3 p-3">
                    <p class="text-muted small fw-medium mb-2 mb-md-3"><i class="bi bi-calendar3 me-1"></i> วันที่และประเภท</p>
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-sm-6">
                            <?= $form->field($model, 'date_start')->widget(DatepickerThai::class, ['options' => ['placeholder' => 'ว/ด/พ.ศ.']]) ?>
                        </div>
                        <div class="col-12 col-sm-6">
                            <?= $form->field($model, 'date_end')->widget(DatepickerThai::class, ['options' => ['placeholder' => 'ว/ด/พ.ศ.']]) ?>
                        </div>
                    </div>
                    <?php
                    echo $form->field($model, 'development_type_id')->widget(Select2::classname(), [
                        'data' => CategoriseHelper::DevelopmentType(),
                        'options' => ['placeholder' => 'เลือกประเภทการพัฒนา', 'class' => 'form-select'],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label('ประเภทการพัฒนา');
                    ?>
                    <?php
                    echo $form->field($model, 'data_json[development_level_name]')->widget(Select2::classname(), [
                        'data' => CategoriseHelper::DevelopmentLevel(true),
                        'options' => ['placeholder' => 'เลือกระดับการพัฒนา', 'class' => 'form-select'],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label('ระดับการพัฒนา');
                    ?>
                    <?php
                    echo $form->field($model, 'data_json[time_slot]')->widget(Select2::classname(), [
                        'data' => [
                            'เต็มวัน' => 'เต็มวัน',
                            'ครั้งวันเช้า' => 'ครั้งวันเช้า',
                            'ครั้งวันบ่าย' => 'ครั้งวันบ่าย',
                        ],
                        'options' => ['placeholder' => 'เลือกช่วงเวลา', 'class' => 'form-select'],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label('ช่วงเวลา');
                    ?>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="border border-secondary border-opacity-25 rounded-3 p-3">
                    <p class="text-muted small fw-medium mb-2 mb-md-3"><i class="bi bi-person-badge me-1"></i> ผู้รับผิดชอบและลักษณะการเข้าร่วม</p>
                    <?php
                    echo $form->field($model, 'data_json[development_go_type_name]')->widget(Select2::classname(), [
                        'data' => CategoriseHelper::DevelopmentGoType(true),
                        'options' => ['placeholder' => 'เลือกลักษณะ', 'class' => 'form-select'],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label('ลักษณะการเข้าร่วม');
                    ?>
                    <?php
                    echo $form->field($model, 'data_json[claim_type_name]')->widget(Select2::classname(), [
                        'data' => CategoriseHelper::DevelopmentClaimType(true),
                        'options' => ['placeholder' => 'เลือกการเบิกเงิน', 'class' => 'form-select'],
                        'pluginOptions' => ['allowClear' => true],
                    ])->label('การเบิกเงิน');
                    ?>
                    
                    <div class="avatar-form">
                        <?php
                        $initEmployeeAssignedTo = empty($model->assigned_to) ? '' : (Employees::findOne($model->assigned_to) ? Employees::findOne($model->assigned_to)->getAvatar(false) : '');
                        echo $form->field($model, 'assigned_to')->widget(Select2::classname(), [
                            'initValueText' => $initEmployeeAssignedTo,
                            'options' => ['placeholder' => 'เลือกบุคลากร ...', 'class' => 'form-select'],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'minimumInputLength' => 1,
                                'ajax' => [
                                    'url' => $url,
                                    'dataType' => 'json',
                                    'data' => new JsExpression('function(params) { return {q:params.term};}'),
                                ],
                                'escapeMarkup' => new JsExpression('function(markup) { return markup;}'),
                                'templateResult' => new JsExpression('function(emp) { return emp && emp.text ? emp.text : "กำลังค้นหา..."; }'),
                                'templateSelection' => new JsExpression('function(emp) {return emp.text;}'),
                            ],
                        ])->label('ผู้ปฏิบัติหน้าที่แทน');
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 3. สถานที่และหน่วยงาน -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-transparent border-0 border-bottom border-secondary border-opacity-25 py-3 px-3 px-md-4">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-semibold text-body d-flex align-items-center">
                <span class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center me-2 p-2 small">3</span>
                <i class="bi bi-geo-alt text-primary me-2"></i>สถานที่และหน่วยงาน
            </h6>
            <span class="text-muted small d-none d-sm-inline">ขั้นที่ 3/4</span>
        </div>
    </div>
    <div class="card-body p-3 p-md-4">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <?php
                echo $form->field($model, 'data_json[location]')->widget(Select2::classname(), [
                    'data' => CategoriseHelper::ListLocationOrg(true),
                    'options' => ['placeholder' => 'เลือกสถานที่', 'class' => 'form-select'],
                    'pluginOptions' => ['tags' => true, 'allowClear' => true],
                ])->label('สถานที่จัดงาน');
                ?>
                <?php
                echo $form->field($model, 'data_json[province_name]')->widget(Select2::classname(), [
                    'data' => CategoriseHelper::ListProvinceName(true),
                    'options' => ['placeholder' => 'เลือกจังหวัด', 'class' => 'form-select'],
                    'pluginOptions' => [],
                ])->label('จังหวัด');
                ?>
            </div>
            <div class="col-12 col-md-6">
                <?php
                echo $form->field($model, 'data_json[location_org]')->widget(Select2::classname(), [
                    'data' => CategoriseHelper::ListLocationOrg(true),
                    'options' => ['placeholder' => 'เลือกหน่วยงาน', 'class' => 'form-select'],
                    'pluginOptions' => ['tags' => true, 'allowClear' => true],
                ])->label('หน่วยงานที่จัด');
                ?>
                <?= $form->field($model, 'data_json[location_org_type]')->radioList([
                    'ในจังหวัด' => 'ในจังหวัด',
                    'ต่างจังหวัด' => 'ต่างจังหวัด',
                    'ต่างประเทศ' => 'ต่างประเทศ',
                ], [
                    'class' => 'd-flex flex-wrap gap-3',
                    'item' => function ($index, $label, $name, $checked, $value) {
                        $checked = $checked ? 'checked' : '';
                        return "<div class='form-check mb-0'>
                            <input class='form-check-input' type='radio' name='{$name}' id='{$index}' value='{$value}' {$checked}>
                            <label class='form-check-label' for='{$index}'>{$label}</label>
                        </div>";
                    }
                ])->label('ประเภทสถานที่');
                ?>
            </div>
        </div>
    </div>
</div>

<!-- 4. ข้อมูลการเดินทาง -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-transparent border-0 border-bottom border-secondary border-opacity-25 py-3 px-3 px-md-4">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-semibold text-body d-flex align-items-center">
                <span class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center me-2 p-2 small">4</span>
                <i class="bi bi-car-front text-primary me-2"></i>ข้อมูลการเดินทาง
            </h6>
            <span class="text-muted small d-none d-sm-inline">ขั้นที่ 4/4</span>
        </div>
    </div>
    <div class="card-body p-3 p-md-4">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <div class="border border-secondary border-opacity-25 rounded-3 p-3 mb-0">
                    <p class="text-muted small fw-medium mb-2"><i class="bi bi-signpost-2 me-1"></i> วันเดินทางไป</p>
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-sm-8">
                            <?= $form->field($model, 'vehicle_date_start')->widget(DatepickerThai::class, ['options' => ['placeholder' => 'ว/ด/พ.ศ.']])->label('วันไป') ?>
                        </div>
                        <div class="col-12 col-sm-4">
                            <?= $form->field($model, 'data_json[vehicle_time_start]')->widget('yii\widgets\MaskedInput', ['mask' => '99:99', 'options' => ['class' => 'form-control', 'placeholder' => '00:00']])->label('เวลา') ?>
                        </div>
                    </div>
                </div>
                <div class="border border-secondary border-opacity-25 rounded-3 p-3 mt-3">
                    <p class="text-muted small fw-medium mb-2"><i class="bi bi-signpost-2-fill me-1"></i> วันเดินทางกลับ</p>
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-sm-8">
                            <?= $form->field($model, 'vehicle_date_end')->widget(DatepickerThai::class, ['options' => ['placeholder' => 'ว/ด/พ.ศ.']])->label('วันกลับ') ?>
                        </div>
                        <div class="col-12 col-sm-4">
                            <?= $form->field($model, 'data_json[vehicle_time_end]')->widget('yii\widgets\MaskedInput', ['mask' => '99:99', 'options' => ['class' => 'form-control', 'placeholder' => '00:00']])->label('เวลา') ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="border border-secondary border-opacity-25 rounded-3 p-3">
                    <p class="text-muted small fw-medium mb-2"><i class="bi bi-truck me-1"></i> พาหนะและระยะทาง</p>
                    <div class="row g-2 g-md-3">
                        <div class="col-12 col-sm-6">
                            <?php
                            echo $form->field($model, 'vehicle_type_id')->widget(Select2::classname(), [
                                'data' => $model->ListVehicleType(),
                                'options' => ['placeholder' => 'เลือกพาหนะเดินทาง', 'class' => 'form-select'],
                                'pluginOptions' => ['allowClear' => true],
                            ])->label('พาหนะเดินทาง');
                            ?>
                        </div>
                        <div class="col-12 col-sm-6">
                            <?= $form->field($model, 'data_json[license_plate]')->textInput(['placeholder' => 'ทะเบียน'])->label('ทะเบียนพาหนะ') ?>
                        </div>
                    </div>
                    <?= $form->field($model, 'data_json[distance]')->textInput(['placeholder' => 'ระบุระยะทาง (กม.)'])->label('ระยะทาง/กิโลเมตร') ?>
                </div>
            </div>

            <!-- คณะเดินทาง -->
            <div class="col-12 mt-3">
                <div class="border border-secondary border-opacity-25 rounded-3 p-3">
                    <p class="text-muted small fw-medium mb-3"><i class="bi bi-people me-1"></i> คณะเดินทาง (ผู้ร่วมเดินทางนอกจากผู้ขอ)</p>
                    <?php if ($isNewRecord): ?>
                    <div id="development-member-tags" class="d-flex flex-wrap gap-2 mb-3"></div>
                    <input type="hidden" name="members_json" id="members-json" value="[]">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md-6">
                            <label class="form-label small text-muted mb-1">ค้นหาบุคลากรในระบบ</label>
                            <select id="development-add-employee" class="form-select" style="width: 100%;" data-placeholder="พิมพ์ชื่อเพื่อค้นหา..."></select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small text-muted mb-1">หรือเพิ่มชื่อคนนอก</label>
                            <div class="d-flex gap-2">
                                <input type="text" id="development-outsider-name" class="form-control" placeholder="พิมพ์ชื่อแล้วกด Enter หรือกดปุ่มเพิ่ม">
                                <button type="button" id="development-btn-add-outsider" class="btn btn-outline-secondary rounded-pill flex-shrink-0">เพิ่มคนนอก</button>
                            </div>
                        </div>
                    </div>
                    <p class="text-muted small mt-2 mb-0">ผู้ขอ (<?= $emp ? Html::encode(trim(($emp->fname ?? '') . ' ' . ($emp->lname ?? ''))) : '-' ?>) จะถูกบันทึกเป็นผู้ขอโดยอัตโนมัติ</p>
                    <?php else: ?>
                    <?php $travelMembers = $model->listMemberPrint(); ?>
                    <div class="d-flex flex-wrap gap-3 align-items-start mb-2">
                        <?php foreach ($travelMembers as $m): ?>
                        <?php
                            $name = $m->emp ? trim(($m->emp->fname ?? '') . ' ' . ($m->emp->lname ?? '')) : ($m->data_json['label'] ?? $m->emp_id ?? '');
                            if ($name === '') {
                                $name = 'ไม่ระบุ';
                            }
                            $position = $m->data_json['emp_position'] ?? ($m->emp && method_exists($m->emp, 'positionName') ? $m->emp->positionName() : '');
                            if ($position === '') {
                                $position = '-';
                            }
                            $avatarUrl = $m->emp && method_exists($m->emp, 'ShowAvatar') ? $m->emp->ShowAvatar() : null;
                        ?>
                        <div class="d-flex align-items-center border border-secondary border-opacity-25 rounded-3 p-2 position-relative development-member-card">
                            <?php if ($avatarUrl): ?>
                            <img class="avatar avatar-sm bg-primary text-white lazyload rounded-circle flex-shrink-0" src="<?= \Yii::getAlias('@web/img/loading.gif') ?>" alt="" data-expand="-20" data-sizes="auto" data-src="<?= Html::encode($avatarUrl) ?>">
                            <?php else: ?>
                            <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 2.5rem; height: 2.5rem; font-size: 0.875rem;"><i class="bi bi-person"></i></div>
                            <?php endif; ?>
                            <div class="avatar-detail ms-2 min-w-0">
                                <h6 class="mb-0 fs-14 text-body" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="ดูเพิ่มเติม..."><?= Html::encode($name) ?></h6>
                                <p class="text-muted mb-0 fs-12"><?= Html::encode($position) ?></p>
                            </div>
                            <?= Html::a('<i class="bi bi-x-lg small"></i>', ['/development/default/delete-member', 'id' => $m->id, 'return' => 'update'], [
                                'class' => 'text-danger text-decoration-none position-absolute top-0 end-0 p-1 opacity-75',
                                'title' => 'ลบออกจากคณะเดินทาง',
                                'data' => ['confirm' => 'ต้องการลบคนนี้ออกจากคณะเดินทางหรือไม่?'],
                            ]) ?>
                        </div>
                        <?php endforeach; ?>
                        <?= Html::a('<i class="bi bi-person-plus me-1"></i> เพิ่มสมาชิก', ['/development/default/add-member', 'id' => $model->id], [
                            'class' => 'btn btn-outline-primary btn-sm rounded-pill open-modal align-self-center',
                            'title' => 'เพิ่มคณะเดินทาง',
                            'data' => ['size' => 'modal-md'],
                        ]) ?>
                    </div>
                    <?php if (empty($travelMembers)): ?>
                    <p class="text-muted small mb-0">ยังไม่มีผู้ร่วมเดินทาง กดปุ่ม <strong>เพิ่มสมาชิก</strong> เพื่อเพิ่มบุคลากรในระบบ หรือไปเพิ่มจากหน้ารายละเอียดหลังบันทึก</p>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ปุ่มดำเนินการ -->
<div class="d-flex flex-column flex-sm-row flex-wrap gap-2 justify-content-between align-items-stretch align-sm-center py-4 px-0">
    <?= Html::a('<i class="bi bi-chevron-left me-1"></i> ย้อนกลับ', $isNewRecord ? ['/development/default/list', 'thai_year' => $model->thai_year ?: (int) date('Y') + 543] : 'javascript:history.back()', ['class' => 'btn btn-outline-secondary rounded-3 px-4 py-2 order-2 order-sm-1']) ?>
    <?= Html::submitButton(($isNewRecord ? 'ยืนยันสร้างบันทึก' : 'บันทึกข้อมูล') . ' <i class="bi bi-check2 ms-1"></i>', ['class' => 'btn btn-primary rounded-3 px-4 py-2 order-1 order-sm-2 fw-medium', 'id' => 'summit']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$depdropUrl = Url::to(['/depdrop/employee-by-id']);
$depdropUrlJson = json_encode($depdropUrl);
$requesterId = (int) ($emp->id ?? 0);
$js = <<<JS
(function() {
    var membersJsonEl = document.getElementById('members-json');
    if (!membersJsonEl) return;
    var requesterId = {$requesterId};
    var depdropUrl = {$depdropUrlJson};
    var members = JSON.parse(membersJsonEl.value || '[]');

    function stripHtml(s) {
        if (typeof s !== 'string') return '';
        var div = document.createElement('div');
        div.innerHTML = s;
        return (div.textContent || div.innerText || '').trim() || s.replace(/<[^>]*>/g, '').trim();
    }
    function renderTags() {
        var html = '';
        members.forEach(function(m, i) {
            var raw = (m.label || '').toString();
            var label = (raw.indexOf('<') !== -1) ? stripHtml(raw) : raw;
            if (!label && m.emp_id) label = 'รหัส ' + m.emp_id;
            if (!label) label = '-';
            html += '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2">' +
                (label.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')) +
                ' <button type="button" class="btn btn-link p-0 text-danger text-decoration-none development-remove-member" data-index="' + i + '" aria-label="ลบ">&times;</button></span>';
        });
        document.getElementById('development-member-tags').innerHTML = html || '';
        membersJsonEl.value = JSON.stringify(members);
    }

    document.getElementById('development-member-tags').addEventListener('click', function(e) {
        if (e.target.classList.contains('development-remove-member')) {
            var i = parseInt(e.target.getAttribute('data-index'), 10);
            members.splice(i, 1);
            renderTags();
        }
    });

    document.getElementById('development-btn-add-outsider').onclick = function() {
        var q = document.getElementById('development-outsider-name').value.trim();
        if (!q) return;
        members.push({ label: q });
        document.getElementById('development-outsider-name').value = '';
        renderTags();
    };
    document.getElementById('development-outsider-name').onkeydown = function(e) {
        if (e.key === 'Enter') { e.preventDefault(); document.getElementById('development-btn-add-outsider').click(); }
    };

    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#development-add-employee').select2({
            placeholder: 'พิมพ์ชื่อเพื่อค้นหา...',
            allowClear: true,
            minimumInputLength: 1,
            width: '100%',
            ajax: {
                url: depdropUrl,
                dataType: 'json',
                delay: 250,
                data: function(params) { return { q: params.term || '', page: params.page || 1 }; },
                processResults: function(data) {
                    if (data && data.results) return { results: data.results };
                    return { results: [] };
                }
            },
            templateResult: function(item) {
                if (!item || !item.id) return (item && item.text) ? item.text : 'กำลังค้นหา...';
                var pos = (item.position_name != null && item.position_name !== '') ? item.position_name : '';
                return pos ? item.fullname + ' — ' + pos : (item.fullname || ('รหัส ' + item.id));
            },
            templateSelection: function(item) {
                if (!item || !item.id) return (item && item.text) ? item.text : 'พิมพ์ชื่อเพื่อค้นหา...';
                return item.fullname || ('รหัส ' + item.id);
            }
        }).on('select2:select', function(e) {
            var d = e.params.data;
            var id = d.id;
            var displayName = (d.fullname != null && d.fullname !== '') ? d.fullname : ('รหัส ' + id);
            if (id && id != requesterId && members.every(function(m) { return m.emp_id != id; })) {
                members.push({ emp_id: parseInt(id, 10), label: displayName });
                renderTags();
            }
            $(this).val(null).trigger('change');
        });
    }

    document.getElementById('form-development').addEventListener('submit', function() {
        membersJsonEl.value = JSON.stringify(members);
    });
    renderTags();
})();

$('#form-development').on('beforeSubmit', function (e) {
    var form = $(this);
    Swal.fire({
        title: "ยืนยัน?",
        text: "บันทึกขออบรม/ประชุม/ดูงาน!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "ยกเลิก!",
        confirmButtonText: "ใช่, ยืนยัน!"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: form.attr('action'),
                type: 'post',
                data: form.serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response && response.status == 'success') {
                        if (typeof closeModal === 'function') closeModal();
                        var redirectUrl = response.redirect || (response.id ? ('/development/default/view?id=' + response.id) : null);
                        Swal.fire({
                            title: "สำเร็จ!",
                            text: "บันทึกข้อมูลเรียบร้อยแล้ว",
                            icon: "success",
                            timer: 1000,
                            showConfirmButton: false
                        }).then(function() {
                            if (redirectUrl) {
                                window.location.href = redirectUrl;
                            } else {
                                window.location.reload();
                            }
                        });
                    }
                }
            });
        }
    });
    return false;
});
JS;
$this->registerJs($js, View::POS_END);
?>
