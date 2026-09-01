<?php
use yii\helpers\Html;
$this->title = 'สลิปของฉัน';
?>
<div class="d-flex justify-content-end gap-2 mb-3 d-print-none"><?= Html::a('กลับข้อมูลเงินเดือน', ['/profile', 'name' => 'payroll'], ['class' => 'btn btn-outline-secondary']) ?><button class="btn btn-primary" type="button" onclick="window.print()"><i class="bi bi-printer me-1"></i>พิมพ์</button></div>
<?= $this->render('@app/modules/finance/views/payroll/_payslip_content', ['row' => $row, 'types' => $types, 'organization' => $organization, 'issuerName' => $issuerName]) ?>
<?php $this->registerCss('@media print{header,nav,.breadcrumb,.page-title,.main-footer,.d-print-none{display:none!important}.content-wrapper,.content{margin:0!important;padding:0!important}.payslip-preview-sheet{max-width:none!important;border:0!important;box-shadow:none!important}}'); ?>
