<?php

use app\components\UserHelper;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\web\View;

$warehouse = Yii::$app->session->get('warehouse');
$user = UserHelper::GetEmployee();
/* @var yii\web\View $this */
/* @var app\modules\inventory\models\StockEvent $model */

$this->title = 'เลขที่รับเข้า : '.$model->code;
$this->params['breadcrumbs'][] = ['label' => 'Stock Ins', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
yii\web\YiiAsset::register($this);
?>
<?php // if ($model->checker == $user->id) { ?>
<?php $form = ActiveForm::begin([
    'id' => 'form-confirm',
    'enableAjaxValidation' => true, // เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/me/approve/stock-confirm-validator'],
]);
    ?>

        <?php echo $form->field($model, 'data_json[checker_confirm]')->radioList(
            ['Y' => 'เห็นชอบ', 'N' => 'ไม่เห็นชอบ'],
            ['custom' => true, 'inline' => true])->label(false); ?>
        <?php echo $form->field($model, 'data_json[checker_comment]')->textArea()->label('หมายเหตุ'); ?>

    <div class="d-flex justify-content-center">
        <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']); ?>
    </div>


<?php ActiveForm::end(); ?>
<?php  // } else { ?>
<!-- <h6 class="text-center">ไม่อนุญาต</h6> -->
<?php // }?>
<?php

$js = <<< JS

\$('#form-confirm').on('beforeSubmit', function (e) {
                var form = \$(this);
                \$.ajax({
                    url: form.attr('action'),
                    type: 'post',
                    data: form.serialize(),
                    dataType: 'json',
                    success:  function (response) {
                        form.yiiActiveForm('updateMessages', response, true);
                        if(response.status == 'success') {
                            closeModal()
                            success()
                              $.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                        }
                    }
                });
                return false;
            });

JS;
$this->registerJS($js, View::POS_END);
?>