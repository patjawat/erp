<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetAudit $model */

$this->title = 'แก้ไขใบตรวจนับครุภัณฑ์';
$this->params['breadcrumbs'][] = ['label' => 'ตรวจนับพัสดุประจำปี', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->audit_no, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-1 mb-2 text-center text-lg-start">
  <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <i data-lucide="file-edit" class="me-2"></i>
    <?= Html::encode($model->audit_no) ?>
  </h4>
  <div class="text-muted small">แก้ไขใบตรวจนับครุภัณฑ์</div>
</div>
<?php $this->endBlock(); ?>

<div class="audit-update">
    <?= $this->render('_form', [
        'model'            => $model,
        'items'            => $items,
        'conditionOptions' => $conditionOptions,
        'statusOptions'    => $statusOptions,
    ]) ?>
</div>
