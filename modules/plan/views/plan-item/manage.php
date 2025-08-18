<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\plan\models\Plan $plan */
/** @var app\modules\plan\models\PlanItem[] $items */

$this->title = "จัดการรายการแผน: " . $plan->title;
?>

<div class="plan-item-manage">
    <h3><?= Html::encode($this->title) ?></h3>

    <?php $form = ActiveForm::begin(); ?>

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
                        <td class="total"><?= number_format($item->total_price, 2) ?></td>
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

    <button type="button" class="btn btn-secondary" id="add-row">+ เพิ่มรายการ</button>
    <br><br>
    <?= Html::submitButton('บันทึก', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('ย้อนกลับ', ['/plan/plan/view', 'id' => $plan->id], ['class' => 'btn btn-light']) ?>

    <?php ActiveForm::end(); ?>
</div>

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

$(document).on("click", ".remove-row", function(){
    $(this).closest("tr").remove();
});

$(document).on("input", ".qty, .price", function(){
    let tr = $(this).closest("tr");
    let qty = parseFloat(tr.find(".qty").val()) || 0;
    let price = parseFloat(tr.find(".price").val()) || 0;
    tr.find(".total").text((qty * price).toFixed(2));
});
JS;
$this->registerJs($js);
?>
