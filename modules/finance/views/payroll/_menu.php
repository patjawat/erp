<?php

use yii\helpers\Html;
use yii\helpers\Url;

$active = $active ?? 'overview';
$monthlyActive = in_array($active, ['monthly-income', 'monthly-expense'], true);
$compensationActive = in_array($active, ['compensation-income', 'compensation-expense'], true);
?>
<nav class="nav nav-pills flex-wrap gap-2 mb-4" aria-label="เมนูเงินเดือน">
    <a class="nav-link <?= $active === 'overview' ? 'active' : '' ?>" href="<?= Url::to(['/finance/payroll']) ?>">
        <i class="bi bi-people me-1" aria-hidden="true"></i>ทะเบียนเงินเดือน
    </a>

    <div class="dropdown">
        <button class="nav-link dropdown-toggle <?= $monthlyActive ? 'active' : '' ?>" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-calendar-month me-1" aria-hidden="true"></i>รายเดือน
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item <?= $active === 'monthly-income' ? 'fw-semibold text-primary' : '' ?>" <?= $active === 'monthly-income' ? 'aria-current="page"' : '' ?> href="<?= Url::to(['/finance/payroll/employee-items', 'group' => 'monthly_pay']) ?>"><i class="bi bi-plus-circle me-2" aria-hidden="true"></i>รายการรับ</a></li>
            <li><a class="dropdown-item <?= $active === 'monthly-expense' ? 'fw-semibold text-primary' : '' ?>" <?= $active === 'monthly-expense' ? 'aria-current="page"' : '' ?> href="<?= Url::to(['/finance/payroll/employee-items', 'group' => 'deduction', 'scope' => 'monthly']) ?>"><i class="bi bi-dash-circle me-2" aria-hidden="true"></i>รายการจ่าย</a></li>
        </ul>
    </div>

    <div class="dropdown">
        <button class="nav-link dropdown-toggle <?= $compensationActive ? 'active' : '' ?>" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-cash-coin me-1" aria-hidden="true"></i>ค่าตอบแทน
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item <?= $active === 'compensation-income' ? 'fw-semibold text-primary' : '' ?>" <?= $active === 'compensation-income' ? 'aria-current="page"' : '' ?> href="<?= Url::to(['/finance/payroll/employee-items', 'group' => 'compensation']) ?>"><i class="bi bi-plus-circle me-2" aria-hidden="true"></i>รายการรับ</a></li>
            <li><a class="dropdown-item <?= $active === 'compensation-expense' ? 'fw-semibold text-primary' : '' ?>" <?= $active === 'compensation-expense' ? 'aria-current="page"' : '' ?> href="<?= Url::to(['/finance/payroll/employee-items', 'group' => 'deduction', 'scope' => 'compensation']) ?>"><i class="bi bi-dash-circle me-2" aria-hidden="true"></i>รายการจ่าย</a></li>
        </ul>
    </div>

    <a class="nav-link <?= $active === 'ot' ? 'active' : '' ?>" href="<?= Url::to(['/finance/payroll', 'section' => 'ot']) ?>">
        <i class="bi bi-clock-history me-1" aria-hidden="true"></i>ข้อมูลเบิก OT
    </a>
    <a class="nav-link <?= $active === 'reports' ? 'active' : '' ?>" href="<?= Url::to(['/finance/payroll/payroll-runs']) ?>">
        <i class="bi bi-file-earmark-bar-graph me-1" aria-hidden="true"></i>รายการเงินเดือน
    </a>
    <a class="nav-link <?= $active === 'certificate' ? 'active' : '' ?>" href="<?= Url::to(['/finance/payroll', 'section' => 'certificate']) ?>">
        <i class="bi bi-file-earmark-check me-1" aria-hidden="true"></i>ใบรับรองเงินเดือน
    </a>
</nav>
