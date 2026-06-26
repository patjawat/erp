<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;
use app\modules\inventoryV2\models\StockOrder;
use app\modules\inventoryV2\models\Warehouse;

/** @var yii\web\View $this */
/** @var app\modules\inventoryV2\models\RequisitionSearch $searchModel */

// คลังที่จ่ายของ — เฉพาะ warehouse_type=MAIN
$mainWarehouses = \yii\helpers\ArrayHelper::map(
    Warehouse::find()
        ->where(['warehouse_type' => 'MAIN'])
        ->andWhere(['or', ['delete' => null], ['delete' => '']])
        ->orderBy('warehouse_name')
        ->all(),
    'id',
    'warehouse_name'
);

$statusOpts = StockOrder::optsStatusLabels();
?>

<div class="card shadow-sm mb-3 req-search-card">
    <div class="card-body py-3">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'fieldConfig' => ['options' => ['class' => 'form-group mb-0']],
            'options' => ['data-pjax' => 0],
        ]); ?>
        <?= $form->field($searchModel, 'sub_warehouse_id')->hiddenInput()->label(false) ?>

        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-6 col-lg-3">
                <?= $form->field($searchModel, 'order_no')->textInput([
                    'placeholder' => 'เลขที่เอกสาร',
                    'autocomplete' => 'off',
                ])->label(false) ?>
            </div>

            <div class="col-6 col-md-3 col-lg-2">
                <?= $this->render('@app/components/ui/_date_filter', ['form' => $form, 'model' => $searchModel, 'label' => false]) ?>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <?= $this->render('@app/components/ui/_date_start', ['form' => $form, 'model' => $searchModel, 'label' => false]) ?>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <?= $this->render('@app/components/ui/_date_end', ['form' => $form, 'model' => $searchModel, 'label' => false]) ?>
            </div>

            <div class="col-6 col-md-3 col-lg-3">
                <?= $form->field($searchModel, 'main_warehouse_id')->widget(Select2::classname(), [
                    'data' => $mainWarehouses,
                    'options' => ['placeholder' => 'คลังที่จ่ายของ'],
                    'pluginOptions' => ['allowClear' => true, 'width' => '100%'],
                ])->label(false) ?>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <?= $this->render('@app/components/ui/input_emp', [
                    'form' => $form,
                    'model' => $searchModel,
                    'fieldName' => 'q_requester_emp_id',
                    'placeholder' => 'ค้นหา ผู้ขอเบิก',
                    'label' => false,
                ]) ?>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <?= $this->render('@app/components/ui/input_emp', [
                    'form' => $form,
                    'model' => $searchModel,
                    'fieldName' => 'q_approver_emp_id',
                    'placeholder' => 'ค้นหา ผู้อนุมัติใบเบิก',
                    'label' => false,
                ]) ?>
            </div>

            <div class="col-6 col-md-4 col-lg-3">
                <?= $form->field($searchModel, 'status')->widget(Select2::classname(), [
                    'data' => $statusOpts,
                    'options' => ['placeholder' => 'สถานะทั้งหมด'],
                    'pluginOptions' => ['allowClear' => true, 'width' => '100%'],
                ])->label(false) ?>
            </div>

            <div class="col-6 col-md-auto col-lg-auto ms-lg-auto d-flex gap-2">
                <?= Html::submitButton('<i class="bi bi-search me-1"></i> ค้นหา', ['class' => 'btn btn-primary flex-grow-1']) ?>
                <?= Html::a('<i class="bi bi-eraser me-1"></i> ล้าง', ['index'], ['class' => 'btn btn-outline-secondary flex-grow-1']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<style>
.req-search-card .form-control,
.req-search-card .select2-selection {
    font-size: 0.88rem;
}
.req-search-card .avatar-form {
    margin-top: 0;
}
.req-search-card .field-requisitionsearch-order_no,
.req-search-card .field-requisitionsearch-status,
.req-search-card .field-requisitionsearch-main_warehouse_id,
.req-search-card .field-requisitionsearch-sub_warehouse_id,
.req-search-card .field-requisitionsearch-date_filter,
.req-search-card .field-requisitionsearch-date_start,
.req-search-card .field-requisitionsearch-date_end,
.req-search-card .field-requisitionsearch-q_requester_emp_id,
.req-search-card .field-requisitionsearch-q_approver_emp_id {
    margin-bottom: 0;
}
</style>
