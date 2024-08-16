<?php

use app\modules\am\models\Asset;
use kartik\datecontrol\DateControl;
use kartik\form\ActiveField;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\widgets\DatePicker;
use kartik\widgets\DateTimePicker;
use unclead\multipleinput\MultipleInput;
use unclead\multipleinput\MultipleInputColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */
$this->title = 'ราการขอซื้อ';
$this->params['breadcrumbs'][] = ['label' => 'Inventories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php Pjax::begin(['id' => 'inventory', 'enablePushState' => true, 'timeout' => 5000]);?>
<?php $form = ActiveForm::begin([
    'id' => 'form-item',
]); ?>
<?php
//  $data = Yii::$app->session->get('order');
?>

<?=Html::a('ย้อนกลับ',['/inventory/withdraw/product-list','category_id' => $model->category_id],['class' => 'btn btn-primary rounded-pill shadow'])?>
<div class="card border border-primary mt-3">
    <div class="card-body">
        <?php echo  $item->product->Avatar()?>
        <?php
    // echo "<pre>";
    // print_r($product);
    // echo "</pre>";
        ?>
    </div>
</div>

        <div class="row">
            <div class="col-12">
                <?= $form->field($model, 'data_json[amount_withdrawal]')->textInput()->label('จำนวน'); ?>
                <?= $form->field($model, 'asset_item')->hiddenInput()->label(false); ?>
                <?= $form->field($model, 'category_id')->hiddenInput()->label(false); ?>
                <?= $form->field($model, 'name')->hiddenInput()->label(false); ?>
            </div>
        </div>

<div class="d-grid gap-2">
    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
</div>

</div>
</div>
<?php ActiveForm::end(); ?>

<?php
$js = <<< JS


    $(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
    $(".modal-dialog").addClass("modal-md");

        \$('#form-item').on('beforeSubmit', function (e) {
                var form = \$(this);
                \$.ajax({
                    url: form.attr('action'),
                    type: 'post',
                    data: form.serialize(),
                    dataType: 'json',
                    success: async function (response) {
                        form.yiiActiveForm('updateMessages', response, true);
                        if(response.status == 'success') {
                            $("#main-modal").modal("show");
                            $("#main-modal-label").html(response.title);
                            $(".modal-body").html(response.content);
                            success()
                            loadOrderItem()
                            try {
                                // loadRepairHostory()
                            } catch (error) {
                                
                            }
                            // await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                            // await  \$.pjax.reload({ container:'#order', history:false,replace: false,timeout: false});                               
                        }
                    }
                });
                return false;
            });


    JS;
$this->registerJS($js)
?>
<?php Pjax::end(); ?>