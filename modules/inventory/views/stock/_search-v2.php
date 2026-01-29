<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

$cart = Yii::$app->cartSub;
$warehouse = Yii::$app->session->get('warehouse');
/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php $form = ActiveForm::begin([
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>
<div class="row gap-2">

    <div class="col-12">
        <?php

        echo $form->field($model, 'asset_type')->widget(Select2::classname(), [
            'data' => $model->ListProductType(),
            'options' => [
                'placeholder' => 'เลือกประเภทวัสดุ',
            ],
            'pluginOptions' => [
                'allowClear' => true,
            ],
            'pluginEvents' => [
                'select2:select' => "function(result) { 
                $(this).submit()
                }",
                'select2:unselect' => "function(result) { 
                    $(this).submit()
                    }",
            ],
        ])->label(false);
        ?>

    </div>
<div class="col-12">
    <div class="d-flex flex-row justify-content-center align-items-start gap-2">
        <div class="flex-grow-1">
            <?= $form->field($model, 'q', [
                'options' => ['class' => 'mb-0'] // เอา Margin bottom ออกเพื่อให้ขอบล่างเท่ากับปุ่ม
            ])->textInput(['placeholder' => 'ค้นหาชื่อหรือรหัสสินค้า...'])->label(false) ?>
        </div>
        
        <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', [
            'class' => 'btn btn-light text-nowrap' // text-nowrap ป้องกันตัวหนังสือขึ้นบรรทัดใหม่
        ]) ?>
    </div>
</div>

</div>


<?php if (isset($warehouse) && $warehouse['warehouse_type'] !== 'MAIN'): ?>
    <?= Html::a('<button type="button" class="btn btn-primary">
                                <i class="fa-solid fa-cart-plus"></i> ตะกร้า <span class="badge text-bg-danger" id="totalCount">' . $cart->getCount() . '</span>
                                </button>', ['/inventory/sub-stock/show-cart'], ['class' => 'brn btn-primary shadow open-modal', 'data' => ['size' => 'modal-xl']]) ?>
<?php endif; ?>


<?php ActiveForm::end(); ?>