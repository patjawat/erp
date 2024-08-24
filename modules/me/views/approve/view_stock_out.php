<?php

use app\components\AppHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\form\ActiveForm;
use app\modules\inventory\models\StockEvent;
use yii\widgets\Pjax;
$warehouse = Yii::$app->session->get('warehouse');
/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEvent $model */

$this->title = 'เลขที่รับเข้า : ' . $model->code;
$this->params['breadcrumbs'][] = ['label' => 'Stock Ins', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php Pjax::begin(['id' => 'inventory']); ?>
<div class="row">
    <div class="col-6">
        <div class="card">
            <div class="card-body">
                <div class="text-truncate">
                    <?=$model->CreateBy('ผู้ขอเบิก')['avatar']?>
                </div>
                <hr>
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'code',
                        'order_status',
                        'name',
                    ],
                ]) ?>

            </div>
        </div>

    </div>

    <div class="col-6">
        <?php $form = ActiveForm::begin([
    'id' => 'form-confirm',
    'enableAjaxValidation' => true, //เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/me/approve/stock-confirm-validator'],
])
?>
        <div class="card">
            <div class="card-body">
                <div class="text-truncate">
                    <?=  $model->viewChecker('หัวหน้าตรวจสอบ')['avatar'] ?>
                </div>
                <hr>
                <?=$form->field($model, 'data_json[checker_confirm]')->radioList(
                            ['Y' => 'อนุมัติ', 'N' => 'ไม่อนุมัติ'],
                            ['custom' => true, 'inline' => true]
                        )->label(false);
                    ?>
                <?= $form->field($model, 'data_json[checker_comment]')->textArea()->label('หมายเหตุ') ?>
            </div>
            <div class="card-footer d-flex justify-content-between">

                <h6>การอนุมัติ</h6>
                <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
            </div>


        </div>
        <?php ActiveForm::end(); ?>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6><i class="bi bi-ui-checks"></i> ขอเบิกวัสดุ <span
                            class="badge rounded-pill text-bg-primary"><?=count($model->getItems())?> </span> รายการ
                    </h6>
                </div>
                <table class="table table-striped mt-3">
                    <thead class="table-primary">
                        <tr>
                            <th>ชื่อรายการ</th>
                            <th class="text-center">หน่วย</th>
                            <th class="text-center">จำนวนเบิก</th>

                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        <?php foreach ($model->getItems() as $item): ?>
                        <tr class="<?=$item->order_status == 'pending' ? 'bg-warning-subtle' : ''?>">
                            <td class="align-middle">
                                <?php
                            try {
                                echo $item->product->Avatar();
                            } catch (\Throwable $th) {}
                            ?>
                            </td>

                            <td class="align-middle text-center">
                                <?=isset($item->product->data_json['unit']) ? $item->product->data_json['unit'] : '-'?>
                            </td>
                            <td class="align-middle text-center"><?= $item->data_json['req_qty']?></td>
                        </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>

            </div>
        </div>
        <div class="form-group mt-3 d-flex justify-content-center">
            <?php // $model->isPending() >= 1 ? Html::a('<i class="bi bi-check2-circle"></i> บันทึก',['/inventory/stock-out/confirm-order','id' => $model->id],['class' => 'btn btn-primary rounded-pill shadow confirm-order']) : ''?>
        </div>
    </div>

</div>

<?php

$js = <<< JS

\$('#form-confirm').on('beforeSubmit', function (e) {
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
                            try {
                                loadApproveStock()
                            } catch (error) {
                                
                            }
                            // await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                        }
                    }
                });
                return false;
            });

JS;
$this->registerJS($js)
?>

<?php Pjax::end()?>