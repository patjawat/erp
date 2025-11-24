<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\models\Categorise;
use kartik\widgets\DepDrop;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use app\modules\am\components\AssetHelper;

/** @var $model app\modules\plan\models\Plan */
/** @var $items app\modules\plan\models\PlanItem[] */

$planItems = Categorise::find()
    ->alias('i')
    ->select(['i.code AS id', 'i.title AS name'])
    ->leftJoin('categorise t', 't.code = i.category_id')
    ->where(['i.name' => 'plan_item'])
    ->andWhere(['t.name' => 'plan_category'])
    ->andWhere('t.category_id = :category_id', [':category_id' => 'OPS'])
    ->asArray()
    ->all();

$listPlanItems = ArrayHelper::map($planItems, 'id', 'name');

$form = ActiveForm::begin([
    'id' => 'form',
    'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/plan/parcel/validator'],
    'fieldConfig' => ['options' => ['class' => 'form-group mb-2 mr-2 me-2']] // spacing form field groups
]);
?>

<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="card">
            <div class="card-body">
 <?= $form->field($model, 'plan_group_id')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'plan_category_id')->hiddenInput()->label(false) ?>
                <!-- ข้อมูลแผน -->
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, 'thai_year')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-9">
                        <?= $form->field($model, 'department_id')->widget(\kartik\tree\TreeViewInput::className(), [
                            'name' => 'department',
                            'id' => 'treeID',
                            'query' => app\modules\hr\models\Organization::find()->addOrderBy('root, lft'),
                            'value' => 1,
                            'headingOptions' => ['label' => 'รายชื่อหน่วยงาน'],
                            'rootOptions' => ['label' => '<i class="fa fa-building"></i>'],
                            'fontAwesome' => true,
                            'asDropdown' => true,
                            'multiple' => false,
                            'options' => ['disabled' => false],
                        ])->label('หน่วยงานภายในตามโครงสร้าง'); ?>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12">
                          <?php

                        echo $form->field($model, 'plan_item_id')->widget(Select2::classname(), [
                            'data' => $listPlanItems,
                            'options' => [
                                'id' => 'plan_category_id',
                                'placeholder' => 'เลือกรายการค่าใช้สอย',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ])->label('รายการค่าใช้สอย');
                        ?>
      

                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <?= $form->field($model, 'description')->textInput()->label('วัตถุประสงค์') ?>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12">
                        <?php

                        echo $form->field($model, 'plan_budget_type_id')->widget(Select2::classname(), [
                            'data' => $model->listBudgetType(),
                            'options' => [
                                'placeholder' => 'เลือกแหล่งของเงิน',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ])->label('แหล่งของเงิน');
                        ?>
                    </div>
                </div>


                <hr>
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6>แผนการใช้จ่าย</h6>
                        <div>
                            <button type="button" class="btn btn-primary" id="add-row"><i class="fa-solid fa-circle-plus"></i> เพิ่มรายการ</button>
                            <?php //  Html::a('<i class="bi bi-ui-checks"></i> เพิ่มรายการ', ['/plan/parcel/list-asset-item'], ['class' => 'btn btn-primary', 'id' => 'btn-show-asset']) ?>
                        </div>
                    </div>

                </div>
                <table class="table table-bordered" id="item-table">
                    <thead>
                        <tr>
                            <th>ชื่อรายการ</th>
                            <th width="150">จำนวน</th>
                            <th width="200">ราคาต่อหน่วย</th>
                            <th width="200" class="text-end">รวม</th>
                            <th width="50">#</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($items): ?>
                            <?php foreach ($items as $i => $item): ?>
                                <tr>
                                    <td><input type="text" name="items[<?= $i ?>][item_name]" value="<?= Html::encode($item->item_name) ?>" class="form-control"></td>
                                    <td><input type="number" name="items[<?= $i ?>][qty]" value="<?= $item->qty ?>" class="form-control qty"></td>
                                    <td><input type="number" step="0.01" name="items[<?= $i ?>][unit_price]" value="<?= $item->unit_price ?>" class="form-control price"></td>
                                    <td class="total text-end"><?= number_format($item->qty * $item->unit_price, 2) ?></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row">ลบ</button></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>

                        <?php endif; ?>
                    </tbody>
                </table>


                <?= $form->field($model, 'order_price')->textInput()->label('รวมเป็นจำนวนเงินทั้งสิ้น') ?>
                <hr>

                                    <div class="row">
                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_10')->input('number', ['step' => '0.01'])->label('ต.ค.') ?></div>
                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_11')->input('number', ['step' => '0.01'])->label('พ.ย.') ?></div>
                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_12')->input('number', ['step' => '0.01'])->label('ธ.ค.') ?></div>

                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_1')->input('number', ['step' => '0.01'])->label('ม.ค.') ?></div>
                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_2')->input('number', ['step' => '0.01'])->label('ก.พ.') ?></div>
                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_3')->input('number', ['step' => '0.01'])->label('มี.ค.') ?></div>

                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_4')->input('number', ['step' => '0.01'])->label('เม.ย.') ?></div>
                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_5')->input('number', ['step' => '0.01'])->label('พ.ค.') ?></div>
                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_6')->input('number', ['step' => '0.01'])->label('มิ.ย.') ?></div>
                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_7')->input('number', ['step' => '0.01'])->label('ก.ค.') ?></div>
                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_8')->input('number', ['step' => '0.01'])->label('ส.ค.') ?></div>
                        <div class="col-lg-2 col-md-3 col-sm-6"> <?= $form->field($model, 'month_9')->input('number', ['step' => '0.01'])->label('ก.ย.') ?></div>
                    </div>


                <div class="d-flex justify-content-center align-items-center mt-3">
                    <div></div>
                    <div class="d-flex gap-2">
                        <?= Html::submitButton('บันทึก', ['class' => 'btn btn-success']) ?>
                        <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-light']) ?>
                    </div>
                </div>
            </div>
        </div>


    </div>


</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS

// checkBtnAdd()
// function checkBtnAdd()
// {
//     if($('#planorder-price_ref').val() === 'MARKET'){
//                     $('.btn-disable').hide()
//                     $('#add-row').show()
//                     $('#btn-show-asset').hide()
//     }else{
//           $('.btn-disable').hide()
//                     $('#add-row').hide()
//                     $('#btn-show-asset').show()
//     }
// }


$('#form').on('beforeSubmit', function (e) {
    e.preventDefault();
    let form = $(this);

    let valid = true;
        let message = "";
    $("#item-table tbody tr").each(function(index, row){
        // ดึง input แต่ละช่อง
        let item_name = $(row).find("input[name*='[item_name]']");
        let qty       = $(row).find("input[name*='[qty]']");
        let price     = $(row).find("input[name*='[unit_price]']");

        // reset class ก่อน
        item_name.removeClass("is-invalid");
        qty.removeClass("is-invalid");
        price.removeClass("is-invalid");

        // ตรวจสอบค่า
        if((item_name.val() || "").trim() === ""){
            valid = false;
            item_name.addClass("is-invalid");
        }
        if((qty.val() || "").trim() === ""){
            valid = false;
            qty.addClass("is-invalid");
        }
        if((price.val() || "").trim() === ""){
            valid = false;
            price.addClass("is-invalid");
        }
    });


        // if(!valid){
        //     e.preventDefault(); // หยุดการ submit
        //     return false;
        // }


    Swal.fire({
        title: 'ยืนยันการบันทึก?',
        text: "คุณต้องการบันทึกข้อมูลหรือไม่?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, บันทึกเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                beforeSend: async function () {

                },
                success: async function (response) {
                    if (response.status === 'success') {
                        closeModal();
                        await Swal.fire({
                            title: 'บันทึกสำเร็จ!',
                            text: 'ข้อมูลของคุณถูกบันทึกเรียบร้อยแล้ว',
                            icon: 'success',
                            timer: 2000, // ปิดอัตโนมัติใน 3 วินาที
                            confirmButtonText: 'ตกลง'
                        });
                        window.location.href = response.url; // Redirect to the provided URL
                    }
                },
            });
        }
    });

    return false;
});


if($('#planorder-price_ref').val() == '')
{
    $('.btn-disable').show()
    $('#add-row').hide()
    $('#btn-show-asset').hide()
}


$('#btn-show-asset').click(function (e) { 
    e.preventDefault();
     beforLoadModal();

    $.ajax({
        type: "get",
        url: $(this).attr('href'),
        data: {
            asset_group_id:$('#asset_group_id').val(),
            asset_type_id: $('#asset_type_id').val(),
            asset_category_id: $('#asset_category_id').val(),
        },
        dataType: "json",
        success: function (response) {
      $("#main-modal").modal("show");
      $("#main-modal-label").html(response.title);
      $(".modal-body").html(response.content);
      $(".modal-footer").html(response.footer);
      $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl modal-xxl");
      $(".modal-dialog").addClass('modal-xxl');
      $(".modal-content").addClass("card-outline card-primary");
    },
     error: function (xhr) {
      $("#main-modal-label").html("เกิดข้อผิดพลาด");
      $(".modal-body").html(
        '<h5 class="text-center"><i class="fa-solid fa-triangle-exclamation text-danger"></i> ไม่อนุญาต</h5>'
      );
      $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl modal-xxl");
      $(".modal-dialog").addClass("modal-md");
    
    },
  });
});

let rowIndex = $("#item-table tbody tr").length;
$("#add-row").on("click", function(){
    let row = `<tr>
        <td><input type="text" name="items[\${rowIndex}][item_name]" class="form-control"></td>
        <td><input type="number" name="items[\${rowIndex}][qty]" class="form-control qty"></td>
        <td><input type="number" step="0.01" name="items[\${rowIndex}][unit_price]" class="form-control price"></td>
        <td class="total text-end">0.00</td>
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




$(document).ready(function() {
    function calculateTotal() {
        let total = 0;
        // loop input ทุกช่องที่เป็น month_1 .. month_12
        $("input[id^='planorder-month_']").each(function() {
            let val = parseFloat($(this).val()) || 0;
            total += val;
        });
        $("#planorder-order_price").val(total.toFixed(2)); // ใส่ค่าผลรวมลงไป
    }

    // ฟัง event เวลา keyup หรือเปลี่ยนค่า
    $(document).on("input", "input[id^='planorder-month_']", function() {
        calculateTotal();
    });
});



JS;
$this->registerJs($js);
?>  