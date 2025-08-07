<?php
use yii\web\View;
use yii\helpers\Html;
use app\models\Categorise;
use kartik\select2\Select2;
use yii\bootstrap5\ActiveForm;
use unclead\multipleinput\MultipleInput;
$warehouse = Yii::$app->session->get('sub-warehouse');

$assetItems = \yii\helpers\ArrayHelper::map(Categorise::find()->where(['name' => 'asset_item', 'group_id' => 4])->all(), 'code', 'title');
?>


<div class="card">
    <div class="card-body">
        <?php $form = ActiveForm::begin([
            'id' => 'form',
            // 'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
            // 'validationUrl' => ['/inventory/stock-in/create-validator']
        ]); ?>
        <h5 class="border-bottom pb-2 mb-3">
            <i class="fas fa-info-circle me-2"></i>ข้อมูลหลัก
        </h5>

        <!-- ส่วนข้อมูลหลัก -->
        <div class="row mb-4">
            <div class="col-md-3">
                <?= $form->field($model, 'code')->textInput(['readonly' => true]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'movement_date')->textInput()->label('วันที่') ?>
            </div>
              <div class="col-md-4">
                <?= $form->field($model, 'warehouse_id')->textInput()->label('คลัง') ?>
            </div>
        </div>

        <div class="row mb-4">
          
            <!-- <div class="col-md-4">
                <?php $form->field($model, 'data_json[issue_type]')->widget(Select2::class, [
                    'data' => [
                        'ทั่วไป' => 'เบิกทั่วไป',
                        'PO' => 'เบิกตามใบสั่งซื้อ (PO)',
                        'free' => 'เบิกของแถม / ของบริจาค',
                    ],
                    'options' => ['placeholder' => 'เลือกประเภทการเบิก...'],
                    'pluginOptions' => ['allowClear' => true],
                ]) ?>
            </div> -->
            <div class="col-md-4">
                <?= $form->field($model, 'data_json[remark]')->textInput(['placeholder' => 'หมายเหตุเพิ่มเติม']) ?>
            </div>
        </div>
        <div class="d-flex justify-content-end mb-3">

            <?= Html::button('<i class="bi bi-plus-circle"></i> เพิ่มรายการ', [
                'class' => 'btn btn-outline-primary',
                'onclick' => "$('.multiple-input').multipleInput('add')"
            ]) ?>
        </div>

        <?php
        echo $form->field($model, 'items')->widget(MultipleInput::class, [
            'max' => 10,
            'cloneButton' => false,
            'iconSource' => 'html',
            'iconMap' => [
                'html' => [
                    'remove' => '<i class="fa-solid fa-trash text-danger"></i>',
                    'add' => '<i class="fa-solid fa-plus text-success"></i>',
                    'clone' => '<i class="fa-solid fa-clone text-info"></i>', // ✅ เพิ่มบรรทัดนี้
                ],
            ],


            'columns' => [
                [
                    'name' => 'code',
                    'title' => 'รหัสวัสดุ',
                    'type'  => 'textInput',
                    'enableError' => true,
                    'options' => [
                        'placeholder' => 'กรอกรหัสสินค้า',
                    ],
                ],

                [
                    'name'  => 'asset_item',
                    'type'  => \kartik\select2\Select2::className(),
                    'title' => '    รายการวัสดุ',
                    'items' => $assetItems,
                    'options' => [
                        'data' =>  $assetItems,
                        'options' => [
                            'onchange' => <<< JS
                    $.post("pochta?cb_id=" + $(this).val(), function(data){
                        $("#subcat-{multiple_index_my_id}").val(data.pochta);
                    });
                    JS,
                            'prompt' => 'เลือกวัสดุที่ต้องการ'
                        ],
                    ],
                ],

                [
                    'name'  => 'qty',
                    'title' => 'จำนวน',
                    'type'  => 'textInput',
                ],
                // [
                //     'name' => 'unit',
                //     'type'  => 'textInput',
                //     'title' => 'หน่วย',
                //     'defaultValue' => 1,
                //     'enableError' => true,
                // ],
                [
                    'name' => 'lot_number',
                    'title' => 'ล็อต/ซีเรียล',
                    'defaultValue' => 1,
                    'enableError' => true,
                    'type'  => 'textInput',
                ],
                [
                    'name' => 'note',
                    'title' => 'หมายเหตุ',
                    'defaultValue' => 1,
                    'enableError' => true,
                    'type'  => 'textInput',
                ]
            ]
        ])->label(false);
        ?>

        <!-- Action Buttons -->
        <div class="form-group mt-3 text-center">
            <?= Html::submitButton('<i class="bi bi-check2-circle me-1"></i> บันทึก', ['class' => 'btn btn-primary px-4']) ?>
        </div>

        <?php ActiveForm::end(); ?>


    </div>
</div>

<?php
$ref = $model->ref;
$js = <<< JS

   handleFormSubmit('#form', null, async function(response) {
        // await location.reload();
    });

    $('body').on('click', '.multiple-input .remove-button', function(e) {
    e.preventDefault(); // ป้องกันการลบทันที

    const button = $(this);
    Swal.fire({
        title: 'แน่ใจหรือไม่?',
        text: 'คุณต้องการลบรายการนี้ใช่หรือไม่?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            button.closest('.multiple-input-list__item').remove(); // ลบแถว
        }
    });
});

    

JS;
$this->registerJS($js, View::POS_END);
?>