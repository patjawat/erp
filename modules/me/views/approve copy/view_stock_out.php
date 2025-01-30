<?php

use yii\web\View;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use yii\widgets\DetailView;
use app\components\UserHelper;

$warehouse = Yii::$app->session->get('warehouse');
$user = UserHelper::GetEmployee();
/* @var yii\web\View $this */
/* @var app\modules\inventory\models\StockEvent $model */

$this->title = 'เลขที่รับเข้า : '.$model->code;
$this->params['breadcrumbs'][] = ['label' => 'Stock Ins', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
yii\web\YiiAsset::register($this);
?>
<div class="row">
<div class="col-6">
<?php echo DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'label' => 'รหัสขอเบิก',
                            'value' => $model->code,
                        ],
                        [
                            'label' => 'จากคลัง',
                            'value' => $model->fromWarehouse->warehouse_name ?? '-',
                        ],
                        [
                            'label' => 'วันที่',
                            'value' => $model->viewCreatedAt(),
                        ],
                        [
                            'label' => 'สถานะคำขอ',
                            'format' => 'html',
                            'value' => $model->viewStatus(),
                        ],
                        [
                            'label' => 'มูลค่า',
                            'format' => 'html',
                            'value' => number_format($model->getTotalOrderPrice(),2),
                        ],
                    ],
                ]); ?>
    </div>
<div class="col-6">
 
<?php $form = ActiveForm::begin([
    'id' => 'form-confirm',
    'enableAjaxValidation' => true, // เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/me/approve/stock-confirm-validator'],
]);
    ?>

        <?php echo $form->field($model, 'data_json[checker_confirm]')->radioList(
            ['Y' => 'เห็นชอบ', 'N' => 'ไม่เห็นชอบ'],
            ['custom' => true, 'inline' => true])->label(false); ?>
        <?php echo $form->field($model, 'data_json[checker_comment]')->textArea(['style' => 'height:130px'])->label('หมายเหตุ'); ?>

    <div class="d-flex justify-content-center gap-3">
        <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary  rounded-pill shadow', 'id' => 'summit']); ?>
        <button type="button" class="btn btn-secondary rounded-pill shadow" data-bs-dismiss="modal">ยกเลิก</button>
    </div>


<?php ActiveForm::end(); ?>
   
</div>

</div>
<?php echo $this->render('@app/modules/inventory/views/stock-order/list_items',['model' => $model])?>
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
                            location.reload()
                            //   $.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                        }
                    }
                });
                return false;
            });

JS;
$this->registerJS($js, View::POS_END);
?>