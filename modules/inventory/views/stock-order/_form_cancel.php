<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\helpers\Url;
use kartik\select2\Select2;
use iamsaint\datetimepicker\Datetimepicker;
use yii\web\View;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEvent $model */
/** @var yii\widgets\ActiveForm $form */

?>

<div class="stock-in-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form',
        // 'action' => ['/inventory/stock-order/cancel-order','id' => $model->id],
        'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
        'validationUrl' => ['/inventory/stock-order/cancel-order-validator'],
    ]); ?>

    <?= $form->field($model, 'data_json[cancel_note]')->textArea(['rows' => 5])->label('ระบุเหตุผล');?>
    <?= $form->field($model, 'order_status')->textInput()->label(false);?>
    <div class="form-group mt-3 d-flex justify-content-center">
        <span class="btn btn-primary rounded-pill shadow form-submit"><i class="bi bi-check2-circle"></i> บันทึก</span>
        <?php Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<?php
$js = <<< JS

$('.form-submit').click(function (e) { 
    e.preventDefault();
    var title = 'ยืนยัน';
    var text = 'ยกเลิกคำขอเบิก';
    Swal.fire({
            title: title,
            text: text,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "ใช่, ยืนยัน!",
            cancelButtonText: "ยกเลิก",
            }).then( (result) => {
                if (result.value == true) {
                        console.log('Submit');
                        $('#form').submit();
                        
                }
        });
    return false;

});
// $('#form').on('beforeSubmit',  function (e) {
//         e.preventDefault();
       
//          Swal.fire({
//             title: title,
//             text: text,
//             icon: "warning",
//             showCancelButton: true,
//             confirmButtonColor: "#3085d6",
//             cancelButtonColor: "#d33",
//             confirmButtonText: "ใช่, ยืนยัน!",
//             cancelButtonText: "ยกเลิก",
//             }).then( (result) => {
//                 if (result.value == true) {
//                     $.ajax({
//                     type: "post",
//                     url: $(this).attr('action'),
//                     dataType: "json",
//                     success: function (response) {
//                         if (response.status == "success") {
//                             alert('succes')
//                         // location.reload();
//                         // await  $.pjax.reload({container:response.container, history:false,url:response.url});
//                         }
//                     },
                
//                 });
//             }
//         });
//     return false;
// });


JS;
$this->registerJS($js,View::POS_END)
?>