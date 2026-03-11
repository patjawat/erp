<?php

use app\modules\health\models\HealthChronicDisease;
use app\modules\health\models\HealthFamilyDisease;
use app\modules\health\models\HealthLab;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;
use yii\web\View;
use Yiisoft\Arrays\ArrayHelper;

$this->title = 'บันทึกรายการ LAB และค่าใช้จ่าย';
$this->params['breadcrumbs'][] = ['label' => 'ข้อมูลสุขภาพ', 'url' => ['/health']];
$this->params['breadcrumbs'][] = $this->title;

// ใส่ใน View ไฟล์เดิมของคุณ
\app\assets\TomSelectAsset::register($this);

$labList = HealthLab::find()->all();
$labListData = ArrayHelper::map($labList, 'lab_code', 'lab_name');
?>


<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="scan-heart"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/health/menu', ['active' => 'list'])
?>
<?php $this->endBlock(); ?>


<?php
$data = is_array($model->data_json) ? $model->data_json : [];
$familyDiseaseList = HealthFamilyDisease::getActiveList();
$chronicDiseaseList = HealthChronicDisease::getActiveList();
$foodTasteMap = ['sweet' => 'หวาน', 'salty' => 'เค็ม', 'fatty' => 'มัน', 'sour' => 'เปรี้ยว', 'none' => 'ไม่ชอบทุกข้อ'];
$ftRaw = $data['food_taste'] ?? '';
$foodTasteDisplay = '-';
if (is_array($ftRaw)) {
    $foodTasteDisplay = implode(', ', array_map(function ($v) use ($foodTasteMap) {
        return $foodTasteMap[$v] ?? $v;
    }, $ftRaw)) ?: '-';
} elseif ($ftRaw !== '' && isset($foodTasteMap[$ftRaw])) {
    $foodTasteDisplay = $foodTasteMap[$ftRaw];
}
$condomMap = ['always' => 'ใช้ทุกครั้ง', 'requested' => 'ใช้เมื่อถูกร้องขอ', 'none' => 'ไม่ใช้', 'no_extramarital' => 'ไม่เคยมีเพศสัมพันธ์กับผู้ที่ไม่ใช่สามีหรือภรรยาของตนเอง', 'no_answer' => 'ไม่ตอบ'];
$drivingMap = ['none' => 'ไม่ขับขี่ไม่โดยสาร', 'always' => 'ทุกครั้ง', 'sometimes' => 'บางครั้ง', 'rarely' => 'นานๆ ครั้ง'];
$valueMapChronic = [0 => ['label' => 'ไม่มี', 'color' => 'success'], 1 => ['label' => 'มี', 'color' => 'danger'], 2 => ['label' => 'ไม่เคยตรวจ', 'color' => 'secondary']];
?>
<?php $form = ActiveForm::begin(['id' => 'lab-form']); ?>
<div class="container-fluid px-3 px-md-4 pb-4">
    <?= $this->render('patient_profile', ['model' => $model]) ?>

    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm border-top border-4 border-info">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                    <h6 class="mb-0 fw-bold text-info"><i class="fas fa-clipboard-check me-2"></i>สรุปข้อมูลการคัดกรอง</h6>
                    <button type="button" class="btn btn-sm btn-outline-info border-0 rounded-pill" data-bs-toggle="collapse" data-bs-target="#screening-summary-body" aria-expanded="true">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
                <div class="collapse show" id="screening-summary-body">
                    <div class="card-body pt-0">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <tbody class="table-group-divider">
                                        <tr><td class="text-muted small" style="width:120px;">บุหรี่</td><td class="fw-medium"><?= Html::encode(['smoke' => 'สูบ', 'none' => 'ไม่สูบ', 'quit' => 'เคยสูบแต่เลิกแล้ว'][$data['smoking_status'] ?? ''] ?? '-') ?></td></tr>
                                        <tr><td class="text-muted small">แอลกอฮอล์</td><td class="fw-medium"><?= Html::encode(['drink' => 'ดื่ม', 'none' => 'ไม่ดื่ม', 'quit' => 'เคยดื่มแต่เลิกแล้ว'][$data['alcohol_status'] ?? ''] ?? '-') ?></td></tr>
                                        <tr><td class="text-muted small">ออกกำลังกาย</td><td class="fw-medium"><?= Html::encode([
                                            'everyday' => 'ออกกำลังทุกวัน', '3_times_week' => 'ออก 3 ครั้ง/สัปดาห์', 'less_than_3' => 'น้อยกว่า 3 ครั้ง/สัปดาห์', 'none' => 'ไม่ออกกำลังกาย'
                                        ][$data['exercise_status'] ?? ''] ?? '-') ?></td></tr>
                                        <tr><td class="text-muted small">รสอาหาร</td><td class="fw-medium"><?= Html::encode($foodTasteDisplay) ?></td></tr>
                                        <tr><td class="text-muted small">ขับขี่ปลอดภัย</td><td class="fw-medium"><?= Html::encode($drivingMap[$data['driving_safety'] ?? ''] ?? '-') ?></td></tr>
                                        <tr><td class="text-muted small">เพศสัมพันธ์</td><td class="fw-medium"><?= Html::encode($condomMap[$data['condom_usage'] ?? ''] ?? '-') ?></td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="small">
                                    <div class="mb-2"><strong class="text-primary">ส่วนที่ 3: ประวัติการเจ็บป่วยในครอบครัว</strong></div>
                                    <?php
                                    $fh = $data['family_history'] ?? [];
                                    $fh = is_array($fh) ? $fh : ($fh !== '' && $fh !== null ? [$fh] : []);
                                    $fhLabels = array_filter(array_map(function ($code) use ($familyDiseaseList) {
                                        return $familyDiseaseList[$code] ?? $code;
                                    }, $fh));
                                    echo $fhLabels ? implode(', ', $fhLabels) : '<span class="text-muted">-</span>';
                                    ?>
                                    <div class="mb-2 mt-3"><strong class="text-info">ส่วนที่ 4: พี่น้อง (สายตรง) มีประวัติการเจ็บป่วยด้วย</strong></div>
                                    <?php
                                    $sh = $data['sibling_history'] ?? [];
                                    $sh = is_array($sh) ? $sh : ($sh !== '' && $sh !== null ? [$sh] : []);
                                    $shLabels = array_filter(array_map(function ($code) use ($familyDiseaseList) {
                                        return $familyDiseaseList[$code] ?? $code;
                                    }, $sh));
                                    echo $shLabels ? implode(', ', $shLabels) : '<span class="text-muted">-</span>';
                                    ?>
                                    <?php
                                    $chronicSelected = [];
                                    if (!empty($chronicDiseaseList)) {
                                        foreach ($chronicDiseaseList as $attr => $label) {
                                            $val = (int)($data[$attr] ?? 0);
                                            if ($val === 0) continue;
                                            $chronicSelected[] = Html::encode($label);
                                        }
                                    }
                                    if (!empty($chronicSelected)): ?>
                                    <div class="mb-2 mt-3"><strong class="text-danger">ส่วนที่ 5: โรคประจำตัว</strong></div>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach ($chronicSelected as $name): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill fw-medium px-2 py-1"><?= $name ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body py-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-sm-6 col-md-4">
                            <?= $form->field($model, 'appointment_date')->textInput([
                                'class' => 'form-control',
                                'placeholder' => 'เลือกวันที่นัดหมาย',
                            ])->label('วันที่การนัดหมาย') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary-gradient text-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>บันทึกรายการ LAB และค่าใช้จ่าย</h6>
                </div>
                <div class="card-body p-2 p-md-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="lab-table">
                            <thead>
                                <tr>
                                    <th style="width: 35%">รายการ Lab</th>
                                    <th style="width: 15%">จำนวน</th>
                                    <th style="width: 20%">ราคา/หน่วย</th>
                                    <th style="width: 20%" class="text-end">ราคารวม</th>
                                    <th class="text-center" style="width: 100px;">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody class="align-middle table-group-divider" id="lab-tbody">
                                <?php foreach ($labItems as $i => $item): ?>
                                    <tr class="lab-row">
                                        <td>
                                            <?= $form->field($item, "[$i]lab_code")->dropDownList($labListData, [
                                                'prompt' => 'เลือกรายการ...',
                                                'class' => 'form-select lab-select'
                                            ])->label(false) ?>
                                        </td>
                                        <td><?= $form->field($item, "[$i]qty")->textInput(['type' => 'number', 'min' => 1, 'class' => 'form-control qty-input'])->label(false) ?></td>
                                        <td><?= $form->field($item, "[$i]lab_price")->textInput(['type' => 'number', 'step' => '0.01', 'class' => 'form-control price-input'])->label(false) ?></td>
                                        <td class="text-end"><input type="text" class="form-control-plaintext fw-bold line-total text-end pe-3" readonly value="0.00"></td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-row border-0"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-secondary">
                                    <td colspan="3" class="text-end fw-bold">รวมเป็นเงินทั้งสิ้น</td>
                                    <td class="text-end">
                                        <p class="mb-0 fw-bold text-primary" id="grand-total">0.00</p>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="row g-3 mt-2 mb-2">
                        <div class="col-12 col-md-6 d-flex align-items-center">
                            <button type="button" class="btn btn-outline-success btn-sm rounded-pill" id="add-row">
                                <i class="fas fa-plus me-1"></i> เพิ่มรายการตรวจ
                            </button>
                        </div>
                        <div class="col-12 col-md-6 text-md-end">
                            <?= Html::submitButton('บันทึกข้อมูลและสรุปยอด', ['class' => 'btn btn-primary px-5 shadow-sm rounded-pill']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
<?php

$labPriceMap = ArrayHelper::map($labList, 'lab_code', 'lab_price');
$labPriceJson = json_encode($labPriceMap);

$count = count($labItems);
$labOptionsJson = json_encode($labListData);

$js = <<< JS
    let rowCount = {$count};
    const labOptions = {$labOptionsJson};
    const labPrices = {$labPriceJson}; // แผนที่ราคาจาก PHP

// ดักจับจังหวะการ Submit Form
    $('#lab-form').on('beforeSubmit', function(e) {
        e.preventDefault(); // หยุดการส่งฟอร์มปกติไว้ก่อน
        
        let form = this;
        let isValid = true;

        // 1. ตรวจสอบข้อมูลเบื้องต้น (ห้ามว่าง)
        \$('.lab-row').each(function() {
            let lab = \$(this).find('.lab-select').val();
            let qty = \$(this).find('.qty-input').val();
            let price = \$(this).find('.price-input').val();
            
            if (!lab || !qty || qty <= 0 || price === "") {
                isValid = false;
                return false;
            }
        });

        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'ข้อมูลไม่ครบถ้วน',
                text: 'กรุณากรอกรายการ Lab, จำนวน และราคาให้ถูกต้อง',
                confirmButtonColor: '#3085d6'
            });
            return false;
        }

        // 2. แสดง Confirm Dialog
        Swal.fire({
            title: 'ยืนยันการบันทึก?',
            text: "คุณต้องการบันทึกรายการ LAB และค่าใช้จ่ายทั้งหมดใช่หรือไม่?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="fas fa-save me-1"></i> ยืนยันบันทึก',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: false
        }).then((result) => {
            if (result.isConfirmed) {
                // 3. แสดง Loading รอ
                Swal.fire({
                    title: 'กำลังบันทึกข้อมูล...',
                    text: 'กรุณารอสักครู่',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // 4. ส่งฟอร์มจริง
                form.submit();
            }
        });
        return false;
    });

    // เพิ่ม SweetAlert ตอนลบแถว (แถมให้เพื่อความชัวร์)
    \$(document).on('click', '.remove-row', function() {
        if (\$('.lab-row').length > 1) {
            let \$row = \$(this).closest('tr');
            Swal.fire({
                title: 'ลบรายการนี้?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'ลบ',
                cancelButtonText: 'ยกเลิก',
                size: 'small'
            }).then((result) => {
                if (result.isConfirmed) {
                    let ts = \$row.find('.lab-select')[0].tomselect;
                    if(ts) ts.destroy();
                    \$row.remove();
                    calculateTotals();
                }
            });
        }
    });

    
    // ฟังก์ชันสำหรับสร้าง Tom Select
    function initTomSelect(element) {
        if (element.tomselect) return;
        
        let ts = new TomSelect(element, {
            create: false,
            allowEmptyOption: true,
            placeholder: 'เลือกรายการ...',
            width: '100%',
            dropdownParent: 'body', // ให้รายการลอยอยู่บนสุดของหน้าเว็บ ไม่โดนตารางบัง
            controlInput: '<input>',
        // ถ้าอยากให้เปิดขึ้นข้างบนอย่างเดียวให้ใช้คำสั่งนี้ (เลือกใช้อย่างใดอย่างหนึ่ง)
        direction: 'up',
        onDropdownOpen: function() {
            // 1. หาว่าตอนนี้มี Lab Code ไหนถูกเลือกอยู่บ้างในทั้งตาราง
            let selectedValues = [];
            $('.lab-select').each(function() {
                let v = $(this).val();
                // เก็บค่าทุตัวยกเว้นค่าของช่องปัจจุบันที่กำลังเลือกอยู่
                if (v && v !== element.value) {
                    selectedValues.push(v);
                }
            });

            // 2. สั่งซ่อนรายการ (Option) ใน Dropdown ปัจจุบัน
            // วนลูปหา options ทั้งหมดในตัวมันเอง
            Object.keys(this.options).forEach(key => {
                if (selectedValues.includes(key)) {
                    // ถ้า key นี้ถูกเลือกที่แถวอื่นแล้ว ให้ซ่อน
                    this.getOption(key).style.display = 'none';
                } else {
                    // ถ้ายังไม่ถูกเลือก ให้แสดงปกติ
                    this.getOption(key).style.display = 'block';
                }
            });
        },
        // -------------------------------------------
            // เมื่อมีการเปลี่ยนค่า (เลือกรายการ Lab)
            onChange: function(value) {
                let \$row = \$(element).closest('tr');
                let price = labPrices[value] || 0; // ดึงราคาจาก Map ถ้าไม่มีให้เป็น 0
                
                // ใส่ราคาลงในช่อง input lab_price ของแถวนั้น
                \$row.find('.price-input').val(parseFloat(price).toFixed(2));
                
                // สั่งคำนวณราคารวมใหม่ทันที
                calculateTotals();
            }
        });
        return ts;
    }

    // เรียกใช้ตอนโหลดหน้า
    \$('.lab-select').each(function() {
        initTomSelect(this);
    });

    // ฟังก์ชันเพิ่มแถวใหม่
    \$('#add-row').on('click', function() {
        // สร้าง Options HTML
        let optionsHtml = '<option value="">เลือกรายการ...</option>';
        \$.each(labOptions, function(val, text) {
            optionsHtml += `<option value="\${val}">\${text}</option>`;
        });

        // สร้าง HTML สำหรับแถวใหม่โดยตรง (สะอาดกว่าการ Clone)
        // แก้ไขในส่วนของ newRowHtml ใน JavaScript
let newRowHtml = `
    <tr class="lab-row">
        <td>
            <div class="form-group">
                <select id="healthlabconfirm-\${rowCount}-lab_code" 
                        class="form-select lab-select" 
                        name="HealthLabConfirm[\${rowCount}][lab_code]"> 
                    \${optionsHtml}
                </select>
            </div>
        </td>
        <td>
            <div class="form-group">
                <input type="number" 
                       id="healthlabconfirm-\${rowCount}-qty"
                       class="form-control qty-input" 
                       name="HealthLabConfirm[\${rowCount}][qty]" 
                       value="1" min="1">
            </div>
        </td>
        <td>
            <div class="form-group">
                <input type="number" 
                       id="healthlabconfirm-\${rowCount}-lab_price"
                       class="form-control price-input" 
                       name="HealthLabConfirm[\${rowCount}][lab_price]" 
                       step="0.01">
            </div>
        </td>
        <td>
            <input type="text" class="form-control-plaintext fw-bold line-total text-end pe-3" readonly value="0.00">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm remove-row border-0"><i class="fas fa-trash"></i></button>
        </td>
    </tr>
`;

        let \$newRow = \$(newRowHtml);
        \$('#lab-tbody').append(\$newRow);
        
        // สั่งให้ Select ตัวใหม่ในแถวนี้กลายเป็น Tom Select
        initTomSelect(\$newRow.find('.lab-select')[0]);
        
        rowCount++;
        calculateTotals();
    });

    // ดักจับการกด Tab ในช่องราคา
    $(document).on('keydown', '.price-input', function(e) {
    // ตรวจสอบว่าเป็นปุ่ม Tab (9) หรือ Enter (13)
    if (e.which == 9 || e.which == 13) { 
        let \$currentRow = $(this).closest('tr');
        
        // ตรวจสอบว่าเป็นแถวสุดท้ายของตารางหรือไม่
        if (\$currentRow.is('.lab-row:last')) {
            // เช็คว่าถ้าช่องราคามีค่า (ป้องกันการเพิ่มแถวว่าง)
            if ($(this).val() !== "") {
                e.preventDefault(); // หยุด Tab ปกติ และหยุด Enter ไม่ให้ Submit Form
                
                // 1. เรียกปุ่มเพิ่มแถวทำงาน
                $('#add-row').trigger('click');
                
                // 2. Focus ไปที่ช่องค้นหาของแถวใหม่
                setTimeout(function() {
                    let newRow = $('.lab-row:last');
                    let selectEl = newRow.find('.lab-select')[0];
                    if (selectEl && selectEl.tomselect) {
                        selectEl.tomselect.focus(); // Focus ที่ช่องค้นหาของ Tom Select ทันที
                    }
                }, 100); // เพิ่มเวลาเล็กน้อยเพื่อให้ DOM และ Tom Select พร้อมใช้งาน
            }
        } else if (e.which == 13) {
            // กรณีเป็น Enter แต่ไม่ใช่แถวสุดท้าย 
            // ให้เลื่อนไป focus ที่แถวถัดไป (จำลองการกด Tab)
            e.preventDefault();
            \$currentRow.next().find('.lab-select')[0].tomselect.focus();
        }
    }
});

    // ปรับปรุงฟังก์ชันเพิ่มแถวเดิมเล็กน้อยเพื่อให้ Focus ได้ง่ายขึ้น


    // คำนวณราคาทั้งหมด
    function calculateTotals() {
        let grandTotal = 0;
        \$('.lab-row').each(function() {
            let qty = parseFloat(\$(this).find('.qty-input').val()) || 0;
            let price = parseFloat(\$(this).find('.price-input').val()) || 0;
            let total = qty * price;
            \$(this).find('.line-total').val(total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            grandTotal += total;
        });
        \$('#grand-total').text(grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    }

    \$(document).on('input', '.qty-input, .price-input', calculateTotals);
    calculateTotals();
JS;

// เปิดใช้ datepicker สำหรับช่องวันที่นัดหมาย
$this->registerJs("$(function(){ if (typeof thaiDatepicker === 'function') thaiDatepicker('#healthscreen-appointment_date'); });", View::POS_END);

$this->registerJs($js, View::POS_END);
?>