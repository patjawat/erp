<?php

use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;
use app\models\Categorise;
use app\modules\inventory\models\warehouse;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
?>
<div class="row">
    <div class="col-8">
        
    <div class="card">
        <div class="card-body">


<div class="stock-movement-form">

    <?php $form = ActiveForm::begin(); ?>
    <div class="row">
        <div class="col-6">
    <?php
        echo $form->field($model, 'from_warehouse_id')->widget(Select2::classname(), [
            'data' => ArrayHelper::map(warehouse::find()->all(), 'id', 'warehouse_name'),
            'options' => ['placeholder' => 'กรุณาเลือก'],
            'pluginOptions' => [
                'allowClear' => true,
                'dropdownParent' => '#main-modal',
            ],
        ])->label('เบิกจากคลัง');
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
])->label('เข้าคลัง');
?>
         </div>
    </div>


<?php
echo $form
    ->field($model, 'data_json[due_date]')
    ->widget(DateControl::classname(), [
        'type' => DateControl::FORMAT_DATE,
        'language' => 'th',
        'widgetOptions' => [
            'options' => ['placeholder' => 'ระบุวันที่ต้องการ ...'],
            'pluginOptions' => [
                'autoclose' => true
            ]
        ]
    ])
    ->label('วันที่ต้องการ');
?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'movement_type')->hiddenInput()->label(false) ?>

    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>

</div>
    </div>
    

    </div>
    <div class="col-4">
        <div id="viewCartShow">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-center">กำลังโหลด...</h6>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$storeProductUrl = Url::to(['/inventory/store/product']);
$getOrderUrl = Url::to(['/inventory/store/view-cart']);
$deleteItemUrl = Url::to(['/inventory/store/delete']);
$updateItemUrl = Url::to(['/inventory/store/update']);
$js = <<< JS

    getStoreProduct()
    getOrder()
    async function getStoreProduct()
    {
    await $.ajax({
        type: "get",
        url: "$storeProductUrl",
        dataType: "json",
        success: function (res) {
            $('#storeProductShow').html(res.content)
        }
    });
    }

    async function getOrder()
    {
    await $.ajax({
        type: "get",
        url: "$getOrderUrl",
        dataType: "json",
        success: function (res) {
            $('#viewOrder').html(res.content)
        }
    });
    }



    $("body").on("click", ".add-cart", function (e) {
    e.preventDefault();
    $.ajax({
        type: "get",
        url: $(this).attr('href'),
        dataType: "json",
        success: function (response) {
            getViewCar()
        //   $.pjax.reload({container:response.container, history:false});
        }
    });
});


        $("body").on("click", ".update-cart", function (e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: $(this).attr('href'),
            data: {},
            dataType: "json",
            success: function (res) {
                getViewCar()
            }
        });
        
    });
    $("body").on("click", ".delete-item-cart", function (e) {
    e.preventDefault();
    $.ajax({
        type: "get",
        url: $(this).attr('href'),
        dataType: "json",
        success: function (response) {
            getViewCar()
            // $.pjax.reload({container:response.container, history:false});
        }
    });
});
JS;
$this->registerJS($js, View::POS_END);

?>