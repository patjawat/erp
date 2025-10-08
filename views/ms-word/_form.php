<?php

use app\modules\hr\models\Employees;
use iamsaint\datetimepicker\Datetimepicker;
use kartik\form\ActiveForm;

use yii\helpers\Html;
use yii\web\View;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */
$this->title = 'ราการขอซื้อ';
$this->params['breadcrumbs'][] = ['label' => 'Inventories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$employee = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();

?>
<style>
.col-form-label {
    text-align: end;
}
</style>


<?php $form = ActiveForm::begin([
    'id' => 'form-order',
    'fieldConfig' => ['labelSpan' => 3, 'options' => ['class' => 'form-group mb-1 mr-2 me-2']]
]); ?>

  <?=$form->field($model, 'set_date')->textInput()->label('ลงวันที่');
?>


<div class="form-group mt-3 d-flex justify-content-center">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> ยืนยัน', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>


<?php ActiveForm::end(); ?>



<?php
$js = <<< JS
    thaiDatepicker('#order-set_date')

    $("#main-modal").removeClass('modal-md modal-xl').addClass("modal-md")

        \$('#form-order').on('beforeSubmit', function (e) {
                var form = \$(this);
                $("#main-modal").modal("hide")
                  Swal.fire({
                    title: 'กำลังบันทึก...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
                \$.ajax({
                    url: form.attr('action'),
                    type: 'post',
                    data: form.serialize(),
                    dataType: 'json',
                    success: async function (response) {
                        form.yiiActiveForm('updateMessages', response, true);
                        if(response.status == 'success') {
                            await $("#main-modal").modal("show");
                            await $("#main-modal-label").html(response.title);
                            await $(".modal-body").html(response.content);
                            await $("#main-modal").removeClass('modal-md').addClass("modal-xl")
                            await Swal.close();
                        }
                    },
                    error: function (xhr) {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด!',
                        text: xhr.responseText || 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้'
                    });
                }
                });
                return false;
            });


    JS;
$this->registerJS($js,View::POS_END)
?>