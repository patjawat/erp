<?php
use yii\helpers\Html;
$this->title = 'สลิป' . ($types[$row['period_type']] ?? 'เงินเดือน');
$this->beginBlock('page-title'); ?><div class="d-flex align-items-center gap-2"><i class="bi bi-receipt fs-4" aria-hidden="true"></i><h4 class="mb-0"><?= Html::encode($this->title) ?></h4></div><?php $this->endBlock();
$this->beginBlock('page-action'); echo $this->render('@app/modules/finance/menu', ['active' => 'payroll']); $this->endBlock();
?>
<div class="d-flex justify-content-end gap-2 mb-3 d-print-none"><?= Html::a('กลับรายการ', ['payroll-runs', 'period_id' => $row['payroll_period_id']], ['class' => 'btn btn-outline-secondary']) ?><button class="btn btn-primary" type="button" onclick="window.print()"><i class="bi bi-printer me-1" aria-hidden="true"></i>พิมพ์สลิป</button></div>
<?= $this->render('_payslip_content', ['row' => $row, 'types' => $types, 'organization' => $organization, 'issuerName' => $issuerName]) ?>
<?php $this->registerCss('.payslip{max-width:900px}.payslip td{font-variant-numeric:tabular-nums}@media print{header,nav,.breadcrumb,.page-title,.main-footer,.d-print-none{display:none!important}.content-wrapper,.content{margin:0!important;padding:0!important}.payslip{border:0!important;box-shadow:none!important;max-width:none}}'); ?>
