<?php

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanOrder $model */
/** @var app\modules\plan\models\PlanOrderItem[] $items */
/** @var int $lockDept */
/** @var string $lockDeptName */
/** @var array<int,string> $departmentOptions */
/** @var app\modules\plan\models\PlanOrderRevision|null $baselineRevision */
/** @var string|null $returnUrl */

$this->title = 'แก้ไขแผนบุคลากรของหน่วยงาน';
$this->params['breadcrumbs'][] = $returnUrl
    ? ['label' => 'แผนบุคลากรรวม', 'url' => $returnUrl]
    : ['label' => 'แผนหน่วยงาน', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'แก้ไขแผนบุคลากร';
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body mb-0"><i class="fa-solid fa-user-group me-2"></i><?= $this->title ?></h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $returnUrl
    ? $this->render('@app/modules/plan/menu', ['active' => 'personnel'])
    : $this->render('@app/modules/me/menu', ['active' => 'plan']) ?>
<?php $this->endBlock(); ?>

<?= $this->render('_form_personnel', [
    'model'        => $model,
    'items'        => $items,
    'lockDept'     => $lockDept,
    'lockDeptName' => $lockDeptName,
    'departmentOptions' => $departmentOptions,
    'baselineRevision' => $baselineRevision,
    'returnUrl' => $returnUrl,
]) ?>
