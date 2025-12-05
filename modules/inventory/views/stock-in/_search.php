<?php
use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEventSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>
<style>
    .right-setting {
        width: 500px !important;
    }
</style>

<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>
<div class="row">
    <div class="col-2">
         <?= $form->field($model, 'asset_type_id')->widget(Select2::classname(), [
            'data' => $model->ListAssetOnWarehouse(),
            'options' => ['placeholder' => 'เลือกประเภทวัสดุ'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>
    </div>
    <div class="col-2">
        <?= $this->render('@app/components/ui/_date_start', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-2">
        <?= $this->render('@app/components/ui/_date_end', ['form' => $form, 'model' => $model, 'label' => false]) ?>
    </div>
    <div class="col-lg-5 col-md-5 col-sm-12">
        <?= $form->field($model, 'order_status')->widget(Select2::classname(), [
            'data' => $model->listStatus(),
            'options' => ['placeholder' => 'สถานะทั้งหมด'],
            'pluginOptions' => [
                'allowClear' => true,
                // 'width' => '150px',
            ],
        ])->label(false); ?>
    </div>

    <div class="col-lg-1 col-md-1 col-sm-12">
        <?= Html::submitButton('<i class="bi bi-search"></i>', ['class' => 'btn btn-primary']) ?>
        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter"
            aria-expanded="false" aria-controls="collapseFilter">
            <i class="fa-solid fa-filter"></i>
        </button>
    </div>
</div>


<div class="row mt-2">
    <div class="col-4">
        <?= $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา'])->label(false) ?>
    </div>
    <div class="col-2">
        <?= $form->field($model, 'po_number')->textInput(['placeholder' => 'เลขที่สั่งซื้อ'])->label(false) ?>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-12">
        <?= $form->field($model, 'vendor_id')->widget(Select2::classname(), [
            'data' => $model->ListVendor(),
            'options' => ['placeholder' => 'เลือกผู้จำหน่าย'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label(false);
        ?>
    </div>
</div>
<div class="collapse mt-3" id="collapseFilter">

</div>
<?php ActiveForm::end(); ?>
