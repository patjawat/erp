<?php

use app\modules\purchase\models\Order;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Order $model */
/** @var yii\widgets\ActiveForm $form */
$listPqNumber = ArrayHelper::map(Order::find()->where(['name' => 'order'])->all(), 'id', 'pq_number');
?>

<?php Pjax::begin(['id' => 'order-container']); ?>
<?php //  $this->render('../default/menu2') ?>

<div class="card">
    <div class="card-body">
        <h6><i class="fa-solid fa-cart-shopping"></i> ใบสั่งซื้อสินค้า (PO)</h6>
    </div>
</div>

<div>


    <div class="card">
        <div class="card-body">
            <div class="mt-4">

                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#basic-data">ใบสั่งซื้อ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#detail">ทะเบียนคุม</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#note-attachment">ใบขอซื้อ</a>
                    </li>
                </ul>
                <div class="tab-content mt-3">
                    <div class="tab-pane active" id="basic-data">

                        <?php $form = ActiveForm::begin([
                            'type' => ActiveForm::TYPE_HORIZONTAL,
                            'fieldConfig' => ['labelSpan' => 4, 'options' => ['class' => 'form-group mb-1 mr-2 me-2']]
                        ]); ?>

                        <div class="row">
                            <div class="col-4">

                                <?php
                                    echo $form->field($model, 'id')->widget(Select2::classname(), [
                                        'data' => $listPqNumber,
                                        'options' => ['placeholder' => 'กรุณาเลือก'],
                                        'pluginOptions' => [
                                            'allowClear' => true,
                                        ],
                                        'pluginEvents' => [
                                            'select2:select' => "function(result) {
                                            var data = \$(this).select2('data')[0]
                                            \$('#asset-data_json-purchase_text').val(data.text)
                                            // getId(\$(this).val())
                                           window.location.href = '/purchase/po-order/update?id='+\$(this).val()
                                        }",
                                        ]
                                    ])->label('ทะเบียนคุม');
                                ?>
                                <?=
                                    $form->field($model, 'data_json[supplier1]')->widget(Select2::classname(), [
                                        'data' => $model->ListVendor(),
                                        'options' => ['placeholder' => 'กรุณาเลือก'],
                                        'pluginOptions' => [
                                            'allowClear' => true,
                                        ],
                                    ])->label('ชื่อผู้ขาย');
                                ?>
                                <?= $form->field($model, 'data_json[contact_name]')->textInput()->label('ผู้ติดต่อ') ?>
                                <?= $form->field($model, 'data_json[supplier]')->textInput()->label('ใบเสนอราคาเลขที่') ?>

                            </div>
                            <div class="col-4">

                                <?= $form->field($model, 'data_json[want_use_day]')->textInput()->label('ต้องการภายใน (วัน)') ?>
                                <?= $form->field($model, 'data_json[po_credit]')->textInput()->label('ครดิต (วัน)') ?>
                                <?= $form->field($model, 'data_json[supplier]')->textInput()->label('วันที่เอกสาร') ?>
                                <?= $form->field($model, 'data_json[supplier]')->textInput()->label('วันที่เอกสาร') ?>

                            </div>
                            <div class="col-4">
                                <?= $form->field($model, 'data_json[supplier]')->textInput()->label('ต้องการภายใน (วัน)') ?>
                                <?= $form->field($model, 'data_json[supplier]')->textInput()->label('ครดิต (วัน)') ?>
                                <?= $form->field($model, 'data_json[supplier]')->textInput()->label('วันที่เอกสาร') ?>
                                <?= $form->field($model, 'data_json[supplier]')->textInput()->label('วันที่เอกสาร') ?>

                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                    <h5 class="mt-4">รายการสินค้า</h5>
                    <div>
                        <button type="button" class="btn btn-primary">
                            <i class="fa-solid fa-circle-plus"></i> เลือกสินค้า
                        </button>
                        <button type="button" class="btn btn-primary">
                            <i class="fa-solid fa-angle-down"></i> แทรกสินค้า
                        </button>

                    </div>
                </div>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>รหัสสินค้า</th>
                            <th>ชื่อสินค้า</th>
                            <th>หน่วยนับ</th>
                            <th>คลัง</th>
                            <th>ที่เก็บ</th>
                            <th>จำนวน</th>
                            <th>ราคา/หน่วย</th>
                            <th>ส่วนลด</th>
                            <th>จำนวนเงิน</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (Order::find()->where(['category_id' => $model->id])->all() as $item): ?>
                        <tr>
                            <td>1</td>
                            <td>IC6108-028</td>
                            <td><?= $item->product->title ?></td>
                            <td>UNT-06</td>
                            <td>001</td>
                            <td>01</td>
                            <td>5.00</td>
                            <td>3,190.00</td>
                            <td>@50</td>
                            <td>15,700.00</td>
                        </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>
                <div class="row">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <table class="table">
                            <tr>
                                <td>รวมเงิน</td>
                                <td>41,950.00</td>
                            </tr>
                            <tr>
                                <td>ส่วนลดการค้า</td>
                                <td>10%</td>
                            </tr>
                            <tr>
                                <td>เงินก่อนภาษีการค้า</td>
                                <td>37,755.00</td>
                            </tr>
                            <tr>
                                <td>ราคาภาษี</td>
                                <td>37,755.00</td>
                            </tr>
                            <tr>
                                <td>ภาษีมูลค่าเพิ่ม</td>
                                <td>2,642.85</td>
                            </tr>
                            <tr>
                                <td>จำนวนเงินทั้งสิ้น</td>
                                <td>40,397.85</td>
                            </tr>
                        </table>
                    </div>
                </div>






                    </div>
                    <!-- End Tabs1 -->
                    <div class="tab-pane active" id="detail">
                        sss
                        <?php echo $this->render('../pr-order/view', ['model' => $model]) ?>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button class="btn btn-primary">Preview</button>
                    <button class="btn btn-success">Save</button>
                    <button class="btn btn-danger">Close</button>
                </div>
            </div>

        </div>
    </div>

</div>



<?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>


<div class="form-group">
    <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php

$js = <<< JS

    \$('#order-id').on("select2:unselect", function (e) { 
        console.log("select2:unselect", e);
        window.location.href ='/purchase/po-order/create'
    });
    // function getId(id){
    //     window.location.href = Url::to(['/purchase/po-order/create'])
    // }
    JS;
$this->registerJS($js)
?>

<?php Pjax::end() ?>