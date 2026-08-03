<?php

/** @var yii\web\View $this */
/** @var app\modules\plan\models\PlanOrder $model */
/** @var app\modules\hr\models\Organization[] $orgs */

$this->title = 'แก้ไขแผนของหน่วยงาน';
$this->params['breadcrumbs'][] = ['label' => 'แผนหน่วยงาน', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body mb-0"><i class="fa-solid fa-pen-to-square me-2"></i><?= $this->title ?></h4>
<?php $this->endBlock(); ?>

<?= $this->render('_form', ['model' => $model, 'orgs' => $orgs]) ?>
