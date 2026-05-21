<?php

use yii\helpers\Html;
use app\widgets\datepicker\DatepickerThai;
use app\modules\purchaseV2\models\PurchaseRequest;

?>

<?= Html::beginForm(['index'], 'get', [
    'id' => 'purchase-request-search-form',
    'class' => 'row g-2 align-items-end',
]) ?>

<?= Html::activeHiddenInput($model, 'status_group') ?>
<?= Html::activeHiddenInput($model, 'status') ?>

<div class="col-12 col-xl-6">
    <div class="input-group border rounded-3 overflow-hidden">
        <span class="input-group-text bg-white border-0 text-muted">
            <i data-lucide="search"></i>
        </span>
        <?= Html::activeTextInput($model, 'q', [
            'class' => 'form-control border-0 shadow-none',
            'placeholder' => 'ค้นหา เลขที่เอกสาร, ผู้ขอ, รายการ...',
        ]) ?>
    </div>
</div>

<div class="col-12 col-sm-6 col-xl-2">
    <?= Html::activeDropDownList($model, 'department_id', PurchaseRequest::listDepartments(), [
        'class' => 'form-select rounded-3',
        'prompt' => 'ทุกหน่วยงาน',
        'data-autosubmit' => '1',
    ]) ?>
</div>

<div class="col-12 col-sm-6 col-xl-2">
    <?= Html::activeDropDownList($model, 'budget_type_code', PurchaseRequest::listBudgetTypes(), [
        'class' => 'form-select rounded-3',
        'prompt' => 'ทุกประเภทงบ',
        'data-autosubmit' => '1',
    ]) ?>
</div>

<div class="col-6 col-sm-4 col-xl-1">
    <div class="input-group border rounded-3 overflow-hidden">
        <?= DatepickerThai::widget([
            'model' => $model,
            'attribute' => 'date_start',
            'options' => [
                'class' => 'form-control border-0 shadow-none',
                'placeholder' => 'dd/mm/yyyy',
                'data-autosubmit' => '1',
            ],
        ]) ?>
        <span class="input-group-text bg-white border-0 text-muted">
            <i data-lucide="calendar"></i>
        </span>
    </div>
</div>

<div class="col-6 col-sm-4 col-xl-1">
    <?= Html::a('<i data-lucide="rotate-ccw" class="me-1"></i> ล้าง', ['index'], [
        'class' => 'btn btn-outline-secondary rounded-3 fw-semibold w-100',
    ]) ?>
</div>

<?= Html::endForm() ?>

<?php
$js = <<<JS
(function () {
    const form = document.getElementById('purchase-request-search-form');
    if (!form) {
        return;
    }

    form.querySelectorAll('[data-autosubmit="1"]').forEach(function (el) {
        el.addEventListener('change', function () {
            form.submit();
        });
    });
})();
JS;
$this->registerJs($js);
?>
