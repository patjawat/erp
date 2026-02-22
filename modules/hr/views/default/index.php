<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'ภาพรวมบุคลากร';
$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/me']];
$this->params['breadcrumbs'][] = 'ภาพรวม';

?>
<?php Pjax::begin(['id' => 'hr-container', 'timeout' => 50000]); ?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
<i data-lucide="layout-grid"></i>  
        <span class="d-block">
            <?= $this->title; ?>
        </span>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/hr/menu', ['active' => 'overview'])
?>
<?php $this->endBlock(); ?>


<?= $this->render('employee_summary', [
    'dataProvider' => $dataProvider,
    'totalCount' => $totalCount ?? 0,
    'dataProviderGenderM' => $dataProviderGenderM,
    'dataProviderGenderW' => $dataProviderGenderW,
]) ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <p class="text-muted small mb-0">
            <strong>มุมมองผู้บริหาร:</strong>
            องค์กรมีบุคลากรทั้งหมด <strong><?= (int)($totalCount ?? 0) ?></strong> คน
            (ชาย <?= (int)($dataProviderGenderM->getTotalCount() ?? 0) ?> · หญิง <?= (int)($dataProviderGenderW->getTotalCount() ?? 0) ?>)
            กระจายใน <?= count($dataProviderWorkGroup->getModels()) ?> กลุ่มงาน
            และ <?= count($dataProviderPositionType->getModels()) ?> ประเภทการจ้าง
        </p>
    </div>
</div>

<div class="row mb-2">
    <div class="col-12">
        <h6 class="text-muted small fw-normal text-uppercase mb-0">โครงสร้างและประชากรบุคลากร</h6>
    </div>
</div>
<div class="row">
    <div class="col-lg-8 col-md-6 col-sm-12">

        <?= $this->render('position_group_type', [
            'dataProviderWorkGroup' => $dataProviderWorkGroup,
            'positionTypeLabels' => $positionTypeLabels ?? [],
        ]) ?>

        <?= $this->render('gender_chart', [
            'dataProviderGender' => $dataProviderGender,
            'totalCount' => $totalCount ?? 1,
        ]) ?>

        <?php $this->render('position_name', [
            'dataProviderPositionName' => $dataProviderPositionName
        ]) ?>
    </div>

    <div class="col-lg-4 col-md-6 col-sm-12">
        <?= $this->render('gender_pie_chart', [
            'dataProviderPositionType' => $dataProviderPositionType,
            'dataProviderGenderM' => $dataProviderGenderM,
            'dataProviderGenderW' => $dataProviderGenderW,
        ]) ?>
        <?= $this->render('generation_chart', [
            'dataProviderGenB' => $dataProviderGenB,
            'dataProviderGenX' => $dataProviderGenX,
            'dataProviderGenY' => $dataProviderGenY,
            'dataProviderGenZ' => $dataProviderGenZ,
            'dataProviderGenA' => $dataProviderGenA,
        ]) ?>
        <?= $this->render('position_type_chart', [
            'dataProviderPositionType' => $dataProviderPositionType
        ]) ?>
    </div>
</div>

</div>