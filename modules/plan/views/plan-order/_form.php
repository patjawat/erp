<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var $model app\modules\plan\models\Plan */
/** @var $items app\modules\plan\models\PlanItem[] */

$form = ActiveForm::begin();
?>

<!-- ข้อมูลแผน -->
<div class="row">
    <div class="col-md-6">
        <?= $form->field($model,'plan_type')->dropDownList([
            'material'=>'แผนคำขอพัสดุ',
            'personnel'=>'แผนคำขอบุคลากร',
            'expense'=>'แผนคำขอค่าใช้สอย'
        ]) ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model,'title')->textInput(['maxlength'=>true]) ?>
    </div>
</div>

<?= $form->field($model,'description')->textarea(['rows'=>3]) ?>

<div class="row">
    <div class="col-md-6"><?= $form->field($model,'start_date')->input('date') ?></div>
    <div class="col-md-6"><?= $form->field($model,'end_date')->input('date') ?></div>
</div>

<div class="row">
    <div class="col-md-6"><?= $form->field($model,'budget_total')->input('number',['step'=>'0.01']) ?></div>
    <div class="col-md-6"><?= $form->field($model,'budget_used')->input('number',['step'=>'0.01']) ?></div>
</div>

<hr>
<h4>รายการในแผน</h4>

<table class="table table-bordered" id="item-table">
    <thead>
        <tr>
            <th>ชื่อรายการ</th>
            <th>จำนวน</th>
            <th>ราคาต่อหน่วย</th>
            <th>รวม</th>
            <th width="50">#</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($items): ?>
            <?php foreach ($items as $i => $item): ?>
                <tr>
                    <td><input type="text" name="items[<?= $i ?>][item_name]" value="<?= Html::encode($item->item_name) ?>" class="form-control"></td>
                    <td><input type="number" name="items[<?= $i ?>][quantity]" value="<?= $item->quantity ?>" class="form-control qty"></td>
                    <td><input type="number" step="0.01" name="items[<?= $i ?>][unit_price]" value="<?= $item->unit_price ?>" class="form-control price"></td>
                    <td class="total"><?= number_format($item->quantity * $item->unit_price,2) ?></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">ลบ</button></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td><input type="text" name="items[0][item_name]" class="form-control"></td>
                <td><input type="number" name="items[0][quantity]" class="form-control qty"></td>
                <td><input type="number" step="0.01" name="items[0][unit_price]" class="form-control price"></td>
                <td class="total">0.00</td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">ลบ</button></td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<button type="button" class="btn btn-secondary mb-3" id="add-row">+ เพิ่มรายการ</button>

<div class="d-grid d-sm-flex justify-content-sm-end gap-2 mt-4">
    <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::submitButton('<i class="fa-solid fa-floppy-disk me-1"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
let rowIndex = $("#item-table tbody tr").length;
$("#add-row").on("click", function(){
    let row = `<tr>
        <td><input type="text" name="items[\${rowIndex}][item_name]" class="form-control"></td>
        <td><input type="number" name="items[\${rowIndex}][quantity]" class="form-control qty"></td>
        <td><input type="number" step="0.01" name="items[\${rowIndex}][unit_price]" class="form-control price"></td>
        <td class="total">0.00</td>
        <td><button type="button" class="btn btn-danger btn-sm remove-row">ลบ</button></td>
    </tr>`;
    $("#item-table tbody").append(row);
    rowIndex++;
});

$(document).on("click",".remove-row", function(){ $(this).closest("tr").remove(); });
$(document).on("input",".qty, .price", function(){
    let tr = $(this).closest("tr");
    let qty = parseFloat(tr.find(".qty").val())||0;
    let price = parseFloat(tr.find(".price").val())||0;
    tr.find(".total").text((qty*price).toFixed(2));
});
JS;
$this->registerJs($js);
?>
