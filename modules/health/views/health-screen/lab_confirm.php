<?php
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;

$labList = ['CBC' => 'Complete Blood Count', 'UA' => 'Urinalysis', 'FBS' => 'Fasting Blood Sugar']; // ตัวอย่าง
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


<?= $this->render('patient_profile', ['model' => $model]) ?>
<div class="card shadow-sm border-0">
    <div class="card-header text-white d-flex justify-content-between">
        <h5 class="mb-0">ลงผลการตรวจห้องปฏิบัติการ (LAB)</h5>
        <span>พนักงาน: <?= $model->emp_id ?></span>
    </div>
    <div class="card-body">
        <?php $form = ActiveForm::begin(['id' => 'lab-form']); ?>
        
        <table class="table table-bordered align-middle" id="lab-table">
            <thead class="bg-light">
                <tr>
                    <th style="width: 40%">รายการ Lab</th>
                    <th>ผลการตรวจ (Result)</th>
                    <th>ราคา</th>
                    <th>สถานะ</th>
                    <th style="width: 50px;"></th>
                </tr>
            </thead>
            <tbody id="lab-tbody">
                <?php foreach ($labItems as $i => $item): ?>
                    <tr class="lab-row">
                        <td>
                            <?= $form->field($item, "[$i]lab_code")->dropDownList($labList, ['prompt' => 'เลือกรายการ...'])->label(false) ?>
                        </td>
                        <td>
                            <?= $form->field($item, "[$i]lab_result")->textInput(['placeholder' => 'ใส่ผลตรวจ'])->label(false) ?>
                        </td>
                        <td>
                            <?= $form->field($item, "[$i]lab_price")->textInput(['type' => 'number'])->label(false) ?>
                        </td>
                        <td>
                            <?= $form->field($item, "[$i]lab_status")->dropDownList(['Normal' => 'ปกติ', 'Abnormal' => 'ผิดปกติ'])->label(false) ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-outline-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <button type="button" class="btn btn-success btn-sm mb-3" id="add-row">
            <i class="fas fa-plus"></i> เพิ่มรายการตรวจ
        </button>

        <div class="text-end border-top pt-3">
            <?= Html::submitButton('บันทึกข้อมูลทั้งหมด', ['class' => 'btn btn-primary px-5']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
// JavaScript สำหรับเพิ่ม/ลบแถวแบบ Dynamic
$this->registerJs("
    let rowCount = " . count($labItems) . ";
    
    $('#add-row').on('click', function() {
        let newRow = $('.lab-row:first').clone(); // คัดลอกแถวแรก
        newRow.find('input, select').val(''); // ล้างค่าในช่อง
        
        // เปลี่ยน Index ใน name ของทุกฟิลด์ในแถวใหม่
        newRow.find('input, select').each(function() {
            let name = $(this).attr('name');
            if(name) {
                $(this).attr('name', name.replace(/\[\d+\]/, '[' + rowCount + ']'));
            }
            let id = $(this).attr('id');
            if(id) {
                $(this).attr('id', id.replace(/-\d+-/, '-' + rowCount + '-'));
            }
        });
        
        $('#lab-tbody').append(newRow);
        rowCount++;
    });

    $(document).on('click', '.remove-row', function() {
        if ($('.lab-row').length > 1) {
            $(this).closest('tr').remove();
        } else {
            alert('ต้องมีอย่างน้อย 1 รายการ');
        }
    });
");
?>