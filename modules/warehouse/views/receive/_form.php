<?php

use app\models\Categorise;
use app\modules\purchase\models\Order;
use app\modules\warehouse\models\warehouse;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\warehouse\models\Order $model */
/** @var yii\widgets\ActiveForm $form */
// $listOpOrder = ArrayHelper::map(Order::find()->where(['name' => 'order'])->all(), 'id', 'po_number');
$listOpOrder = ArrayHelper::map(Categorise::find()->all(), 'id', 'title');

?>
<style>
.col-form-label {
    text-align: end;
}
</style>

<table class="table table-striped-columns">
    <tbody>
        <tr class="">
            <td class="text-end" style="width:150px;">เลขที่สั่งซื้อ</td>
            <td class="fw-semibold"><?php echo $model->category_id ?></td>
            <td class="text-end">ผู้ดำเนินการ</td>
            <td> <?php // $model->getUserReq()['avatar'] ?></td>
        </tr>
        <tr class="">
            <td class="text-end">ผู้จำหน่าย</td>
            <td>
                <?php
                    try {
                        echo $model->data_json['product_type_name'];
                    } catch (\Throwable $th) {
                    }
                ?></td>
            <td class="text-end">วันที่ขอซื้อ</td>
            <td> <?php // echo Yii::$app->thaiFormatter->asDateTime($model->created_at, 'medium') ?>
            </td>

        </tr>
        <tr class="">
            <td class="text-end">เหตุผล</td>
            <td><?php // isset($model->data_json['comment']) ? $model->data_json['comment'] : '' ?>
            </td>
            <td class="text-end">วันที่ต้องการ</td>
            <td> <?php // isset($model->data_json['due_date']) ? Yii::$app->thaiFormatter->asDate($model->data_json['due_date'], 'medium') : '' ?>
            </td>
        </tr>


    </tbody>
</table>

<div
                class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded mb-3 d-flex justify-content-between">
                <h5><i class="fa-solid fa-circle-info text-primary"></i> การบันทึกรับเข้า</h5>
                <div class="dropdown float-end">
                    <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" style="">

                    </div>
                </div>
            </div>
<div class="order-form">

    <?php $form = ActiveForm::begin([
        'id' => 'rc',
        'fieldConfig' => ['options' => ['class' => 'form-group mb-1']]
    ]); ?>
    <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'po_number')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'category_id')->hiddenInput()->label(false) ?>

    <div class="row">

        <div class="col-6">

        <?php
            echo $form
                ->field($model, 'data_json[to_stock_date]')
                ->widget(DateControl::classname(), [
                    'type' => DateControl::FORMAT_DATE,
                    'language' => 'th',
                    'widgetOptions' => [
                        'options' => ['placeholder' => 'ระบุวันรับเข้าคลัง ...'],
                        'pluginOptions' => [
                            'autoclose' => true
                        ]
                    ]
                ])
                ->label('วันรับเข้าคลัง');
        ?>
        <?php
            echo $form
                ->field($model, 'data_json[checked_date]')
                ->widget(DateControl::classname(), [
                    'type' => DateControl::FORMAT_DATE,
                    'language' => 'th',
                    'widgetOptions' => [
                        'options' => ['placeholder' => 'ระบุวันที่กรรมการคลังตรวจรับ ...'],
                        'pluginOptions' => [
                            'autoclose' => true
                        ]
                    ]
                ])
                ->label('วันที่กรรมการคลังตรวจรับ');
        ?>
        </div>
        <div class="col-6">
            <?php
                echo $form->field($model, 'to_warehouse_id')->widget(Select2::classname(), [
                    'data' => ArrayHelper::map(warehouse::find()->all(), 'id', 'warehouse_name'),
                    'options' => ['placeholder' => 'กรุณาเลือก'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label('คลัง');
            ?>
        </div>
    </div>
    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
    </div>
    <?php ActiveForm::end(); ?>


    <?php
        $ref = $model->ref;
        $js = <<< JS

                            \$('#form-rc').on('beforeSubmit', function (e) {
                                var form = \$(this);
                                \$.ajax({
                                    url: form.attr('action'),
                                    type: 'post',
                                    data: form.serialize(),
                                    dataType: 'json',
                                    success: async function (response) {
                                        form.yiiActiveForm('updateMessages', response, true);
                                        if(response.status == 'success') {
                                            closeModal()
                                            success()
                                            await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                                        }
                                    }
                                });
                                return false;
                            });
            JS;
        $this->registerJS($js)
    ?>