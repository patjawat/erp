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
<div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>บันทึกรายการ LAB และค่าใช้จ่าย</h5>
        <span class="badge bg-light text-dark">พนักงาน: <?= Html::encode($model->emp_id) ?></span>
    </div>
    <div class="card-body">
        <?php $form = ActiveForm::begin(['id' => 'lab-form']); ?>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="lab-table">
                <thead class="table-light">
                    <tr>
                        <th style="width: 35%">รายการ Lab</th>
                        <th style="width: 15%">จำนวน</th>
                        <th style="width: 20%">ราคา/หน่วย</th>
                        <th style="width: 20%">ราคารวม</th>
                        <th class="text-center" style="width: 100px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody id="lab-tbody">
                    <?php foreach ($labItems as $i => $item): ?>
                        <tr class="lab-row">
                            <td>
                                <?= $form->field($item, "[$i]lab_code")->dropDownList($labList, ['prompt' => 'เลือกรายการ...', 'class' => 'form-select lab-select'])->label(false) ?>
                            </td>
                            <td>
                                <?= $form->field($item, "[$i]qty")->textInput(['type' => 'number', 'min' => 1, 'class' => 'form-control qty-input'])->label(false) ?>
                            </td>
                            <td>
                                <?= $form->field($item, "[$i]lab_price")->textInput(['type' => 'number', 'step' => '0.01', 'class' => 'form-control price-input'])->label(false) ?>
                            </td>
                            <td>
                                <input type="text" class="form-control-plaintext fw-bold line-total text-end pe-3" readonly value="0.00">
                            </td>
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
                            <h5 class="mb-0 fw-bold text-primary" id="grand-total">0.00</h5>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <button type="button" class="btn btn-outline-success btn-sm rounded-pill" id="add-row">
                <i class="fas fa-plus me-1"></i> เพิ่มรายการตรวจ
            </button>
            <div class="text-end">
                <?= Html::submitButton('บันทึกข้อมูลและสรุปยอด', ['class' => 'btn btn-primary px-5 shadow-sm rounded-pill']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
<?php
$this->registerJs("
    let rowCount = " . count($labItems) . ";

    // ฟังก์ชันคำนวณราคารวม
    function calculateTotals() {
        let grandTotal = 0;
        $('.lab-row').each(function() {
            let qty = parseFloat($(this).find('.qty-input').val()) || 0;
            let price = parseFloat($(this).find('.price-input').val()) || 0;
            let total = qty * price;
            $(this).find('.line-total').val(total.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            grandTotal += total;
        });
        $('#grand-total').text(grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    }

    // เรียกคำนวณครั้งแรก
    calculateTotals();

    // เพิ่มแถว
    $('#add-row').on('click', function() {
        let newRow = $('.lab-row:first').clone();
        newRow.find('input, select').val('');
        newRow.find('.qty-input').val(1); // ค่าเริ่มต้นเป็น 1
        newRow.find('.line-total').val('0.00');

        newRow.find('input, select').each(function() {
            let name = $(this).attr('name');
            if(name) $(this).attr('name', name.replace(/\[\d+\]/, '[' + rowCount + ']'));
            let id = $(this).attr('id');
            if(id) $(this).attr('id', id.replace(/-\d+-/, '-' + rowCount + '-'));
        });

        $('#lab-tbody').append(newRow);
        rowCount++;
        calculateTotals();
    });

    // ลบแถว
    $(document).on('click', '.remove-row', function() {
        if ($('.lab-row').length > 1) {
            $(this).closest('tr').remove();
            calculateTotals();
        }
    });

    // ตรวจจับการพิมพ์ (Input Event)
    $(document).on('input', '.qty-input, .price-input', function() {
        calculateTotals();
    });
");
?>