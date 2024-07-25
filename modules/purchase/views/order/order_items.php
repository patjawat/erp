<?php
use app\modules\purchase\models\Order;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
?>
<?php Pjax::begin(['id' => 'order_item']); ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-primary">
                    <tr>
                        <th style="width:500px"> 
                            <?= Html::a('<i class="fa-solid fa-circle-plus text-white"></i> เพิ่มรายการใหม่', ['/purchase/order/product-list', 'order_id' => $model->id, 'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> เพิ่มรายการใหม่'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal shadow', 'data' => ['size' => 'modal-lg']]) ?></th>
                        <th class="text-center" style="width:80px">หน่วย</th>
                        <th class="text-end">ราคาต่อหน่วย</th>
                        <th class="text-center" style="width:80px">จำนวน</th>
                        <th class="text-end">จำนวนเงิน</th>
                        <th class="text-center" scope="col" style="width: 120px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach ($model->ListOrderItems() as $item): ?>
                    <tr class="">
                        <td class="align-middle">
                            <?php
                                echo $item->product->Avatar();
                            try {
                            } catch (\Throwable $th) {
                                // throw $th;
                            }
                            ?>
                        </td>
                       
                        <td class="align-middle text-center">
                        <?php
                        try {
                            echo $item->product->data_json['unit'];
                        } catch (\Throwable $th) {

                        }
                        ?>
                        </td>
                        <td class="align-middle text-end fw-semibold">
                            <?php
                            try {
                                echo number_format($item->price, 2);
                            } catch (\Throwable $th) {

                            }
                            ?>
                        </td>
                        <td class="align-middle text-center">
                            <?= $item->qty ?>
                        </td>
                        <td class="align-middle text-end">
                            <div class="d-flex justify-content-end fw-semibold">
                                <?php
                                try {
                                    echo number_format(($item->qty * $item->price), 2);
                                } catch (\Throwable $th) {
                                    // throw $th;
                                }
                                ?>
                            </div>
                        </td>
                        <td class="align-middle">
                            <div class="d-flex justify-content-center gap-2">
                                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/purchase/order/update-item', 'id' => $item->id], ['class' => 'btn btn-sm btn-warning rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                <?= Html::a('<i class="fa-regular fa-trash-can"></i>', ['/purchase/order/delete-item', 'id' => $item->id], ['class' => 'btn btn-sm btn-danger rounded-pill delete-item']) ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                   
                </tbody>
            </table>
            <div class="row justify-content-end">
                <div class="col-8">
                         
                <div class="d-flex align-items-center bg-primary bg-opacity-10  p-2 rounded">

                    <?php $form = ActiveForm::begin([
                        'id' => 'form-order',
                        // 'type' => ActiveForm::TYPE_HORIZONTAL,
                        'fieldConfig' => ['labelSpan' => 3, 'options' => ['class' => 'form-group mb-1 mr-2 me-2']]
                    ]); ?>

<!-- https://www.myaccount-cloud.com/Article/Detail/147604/%E0%B8%A7%E0%B8%B4%E0%B8%98%E0%B8%B5%E0%B8%81%E0%B8%B2%E0%B8%A3%E0%B8%84%E0%B8%B3%E0%B8%99%E0%B8%A7%E0%B8%93-VAT-%E0%B8%99%E0%B8%AD%E0%B8%81-%E0%B9%81%E0%B8%A5%E0%B8%B0-VAT-%E0%B9%83%E0%B8%99 -->

<?= $form->field($model, 'data_json[vat]')->widget(Select2::classname(), [
    'data' => [
        '1' => 'ไม่มี VAT',
        '2' => 'VAT นอก',
        '3' => 'VAT ใน',
    ],
    'options' => ['placeholder' => 'เลือกภาษี ...', 'multiple' => true],
    'pluginOptions' => [
        'tags' => false,
        'tokenSeparators' => [',', ' '],
        'maximumInputLength' => 10
    ],
])->label('ภาษี') ?>
<?= $form->field($model, 'data_json[discount]')->textInput()->label('ส่วนลด (บาท)') ?>

<?php ActiveForm::end(); ?>
</div>


                </div>
                <div class="col-4">
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                <tr class="">
                                    <td>รวมเงิน</td>
                                    <td><span class="fw-semibold"><?= number_format($model->SumPo(), 2) ?></span>
                                    </td>
                                </tr>
                                <tr class="">
                                    <td>ราคาภาษี</td>
                                    <td><span class="fw-semibold"><?= number_format($model->SumPo(), 2) ?></span>
                                    </td>
                                </tr>
                                <tr class="">
                                    <td>ภาษีมูลค่าเพิ่ม</td>
                                    <td><span class="fw-semibold"><?= number_format($model->SumPo(), 2) ?></span>
                                    </td>
                                </tr>
                                <tr class="">
                                    <td>จำนวนเงินทั้งสิ้น</td>
                                    <td><span class="fw-semibold"><?= number_format($model->SumPo(), 2) ?></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                   
                </div>
            </div>
        </div>
<?php Pjax::end() ?>