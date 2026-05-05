<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetAudit $model */

$this->title = 'สร้างใบตรวจนับครุภัณฑ์';
$this->params['breadcrumbs'][] = ['label' => 'ตรวจนับพัสดุประจำปี', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-1 mb-2 text-center text-lg-start">
  <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <i data-lucide="file-plus" class="me-2"></i>
    สร้างใบตรวจนับครุภัณฑ์
  </h4>
  <div class="text-muted small">เริ่มต้นการตรวจนับพัสดุประจำปีงบประมาณ</div>
</div>
<?php $this->endBlock(); ?>

<div class="audit-create">
    <?= $this->render('_form', [
        'model'            => $model,
        'items'            => $items,
        'conditionOptions' => $conditionOptions,
        'statusOptions'    => $statusOptions,
    ]) ?>
</div>
