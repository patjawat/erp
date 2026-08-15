<?php

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanOrder $model */
/** @var app\modules\plan\models\PlanOrderItem[] $items */
/** @var int $lockDept */
/** @var string $lockDeptName */
/** @var array<int,string> $departmentOptions */

$this->title = 'จัดทำแผนบุคลากรของหน่วยงาน';
$this->params['breadcrumbs'][] = ['label' => 'แผนหน่วยงาน', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'จัดทำแผนบุคลากร';
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body mb-0"><i class="fa-solid fa-user-group me-2"></i><?= $this->title ?></h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/me/menu', ['active' => 'plan']) ?>
<?php $this->endBlock(); ?>

<?= $this->render('_form_personnel', [
    'model'        => $model,
    'items'        => $items,
    'lockDept'     => $lockDept,
    'lockDeptName' => $lockDeptName,
    'departmentOptions' => $departmentOptions,
]) ?>
