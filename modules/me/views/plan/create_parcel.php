<?php

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanOrder $model */
/** @var array $items */
/** @var int $lockDept */
/** @var string $lockDeptName */

$this->title = 'จัดทำแผนพัสดุของหน่วยงาน';
$this->params['breadcrumbs'][] = ['label' => 'แผนหน่วยงาน', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'จัดทำแผนพัสดุ';
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body mb-0"><i class="fa-solid fa-box-open me-2"></i><?= $this->title ?></h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/me/menu', ['active' => 'plan']) ?>
<?php $this->endBlock(); ?>

<?= $this->render('@app/modules/plan/views/parcel/_form', [
    'model'        => $model,
    'items'        => $items,
    'scope'        => 'me',
    'lockDept'     => $lockDept,
    'lockDeptName' => $lockDeptName,
]) ?>
