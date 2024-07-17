
<?php
use app\modules\am\models\Asset;
use app\modules\hr\models\Employees;
use kartik\datecontrol\DateControl;
use kartik\form\ActiveField;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use kartik\widgets\DatePicker;
use kartik\widgets\DateTimePicker;
use unclead\multipleinput\MultipleInput;
use unclead\multipleinput\MultipleInputColumn;
// use wbraganca\dynamicform\DynamicFormWidget;
// use vivekmarakana\dynamicform\DynamicFormWidget;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\DetailView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Inventory $model */
$this->title = 'ราการขอซื้อ';
$this->params['breadcrumbs'][] = ['label' => 'Inventories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);

$employee = Employees::find()->where(['user_id' => Yii::$app->user->id])->one();

?>
<style>
.col-form-label {
    text-align: end;
}
</style>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>

<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/sm/views/default/menu') ?>
<?php $this->endBlock(); ?>
<?php Pjax::begin(['id' => 'sm-container']); ?>


<?php $form = ActiveForm::begin([
    'id' => 'form-order',
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'fieldConfig' => ['labelSpan' => 3, 'options' => ['class' => 'form-group mb-1 mr-2 me-2']]
]); ?>

<!-- ชื่อของประเภท -->

  <?= $model->upload('order_files') ?>


<?php ActiveForm::end(); ?>


<?php

$js = <<< JS

    JS;
$this->registerJS($js, View::POS_READY)
?>
<?php Pjax::end(); ?>

<!-- 
<div class="d-flex justify-content-between">

        <h3>รายการเพิ่มเติม</h3>
<div class="dropdown d-flex align-items-start">
    <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
aria-expanded="false">
<i class="fa-solid fa-ellipsis"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-right" style="">
            <?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['/sm/order/upload-file', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไขใบขอซื้อ : ' . $model->pr_number], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
            <?= Html::a('<i class="bx bx-trash me-1 text-danger"></i> ลบ', ['/sm/asset-type/delete', 'id' => $model->id], [
                'class' => 'dropdown-item  delete-item',
            ]) ?>
        </div>
    </div>
</div>


<div class="container mt-5">
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <div class="col">
            <div class="card">
                <img src="https://via.placeholder.com/150" class="card-img-top" alt="Item 1">
                <div class="card-body">
                    <h5 class="card-title">Item 1</h5>
                    <p class="card-text">Description for item 1.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <img src="https://via.placeholder.com/150" class="card-img-top" alt="Item 2">
                <div class="card-body">
                    <h5 class="card-title">Item 2</h5>
                    <p class="card-text">Description for item 2.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <img src="https://via.placeholder.com/150" class="card-img-top" alt="Item 3">
                <div class="card-body">
                    <h5 class="card-title">Item 3</h5>
                    <p class="card-text">Description for item 3.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <img src="https://via.placeholder.com/150" class="card-img-top" alt="Item 4">
                <div class="card-body">
                    <h5 class="card-title">Item 4</h5>
                    <p class="card-text">Description for item 4.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <img src="https://via.placeholder.com/150" class="card-img-top" alt="Item 5">
                <div class="card-body">
                    <h5 class="card-title">Item 5</h5>
                    <p class="card-text">Description for item 5.</p>
                </div>
            </div>
        </div>
    </div>
</div> -->


<?php
$js = <<< JS


    JS;
$this->registerJS($js)

?>