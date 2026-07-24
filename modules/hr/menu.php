<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="d-flex align-items-center gap-2">
    <a href="<?= Url::to(['/hr/default/dashboard']) ?>"
       aria-label="ภาพรวม"
       class="btn <?= ($active === 'overview' || $active === 'dashboard') ? 'btn-primary' : 'btn-outline-primary' ?> d-inline-flex align-items-center gap-2">
        <i data-lucide="layout-grid" width="16" height="16" aria-hidden="true"></i>
        <span class="d-none d-sm-inline">ภาพรวม</span>
    </a>

    <a href="<?= Url::to(['/hr/employees']) ?>"
       aria-label="ทะเบียนบุคลากร"
       class="btn <?= $active === 'employees' ? 'btn-primary' : 'btn-outline-primary' ?> d-inline-flex align-items-center gap-2">
        <i data-lucide="users" width="16" height="16" aria-hidden="true"></i>
        <span class="d-none d-sm-inline">ทะเบียนบุคลากร</span>
    </a>

    <a href="<?= Url::to(['/hr/organization/diagram']) ?>"
       aria-label="ผังโครงสร้างองค์กร"
       class="btn <?= $active === 'organization' ? 'btn-primary' : 'btn-outline-primary' ?> d-inline-flex align-items-center gap-2">
        <i data-lucide="network" width="16" height="16" aria-hidden="true"></i>
        <span class="d-none d-sm-inline">ผังโครงสร้างองค์กร</span>
    </a>

    <div class="dropdown">
        <button class="btn <?= in_array($active, ['setting', 'training-roadmap'], true) ? 'btn-primary' : 'btn-outline-secondary' ?> dropdown-toggle d-inline-flex align-items-center gap-2"
                type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false" aria-label="ตั้งค่า">
            <i data-lucide="settings" width="16" height="16" aria-hidden="true"></i>
            <span class="d-none d-sm-inline">ตั้งค่า</span>
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="dropdownMenuButton1">
            <li>
                <a href="#" id="download-button" class="dropdown-item d-flex align-items-center gap-2">
                    <i data-lucide="download" width="15" height="15" class="text-muted"></i>
                    ส่งออกข้อมูล
                </a>
            </li>
            <li><hr class="dropdown-divider my-1"></li>
            <!-- <li>
                <?= Html::a(
                    '<span class="d-flex align-items-center gap-2"><i data-lucide="tag" style="width:15px;height:15px" class="text-muted"></i> การตั้งค่าบุคลากร</span>',
                    ['/hr/categorise', 'title' => 'การตั้งค่าบุคลากร'],
                    ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]
                ) ?>
            </li>
            <li>
                <?= Html::a(
                    '<span class="d-flex align-items-center gap-2"><i data-lucide="briefcase" style="width:15px;height:15px" class="text-muted"></i> การกำหนดตำแหน่ง</span>',
                    ['/hr/position', 'title' => 'การตั้งค่าบุคลากร'],
                    ['class' => 'dropdown-item open-modal-x', 'data' => ['size' => 'modal-md']]
                ) ?>
            </li> -->
            <li>
                <?= Html::a(
                    '<span class="d-flex align-items-center gap-2"><i data-lucide="sliders-horizontal" style="width:15px;height:15px" class="text-muted"></i> ข้อมูลหลักพนักงาน</span>',
                    ['/hr/employee-master', 'title' => 'ตั้งค่าข้อมูลหลักพนักงาน'],
                    ['class' => 'dropdown-item', 'data' => ['pjax' => false]]
                ) ?>
            </li>
            <li>
                <?= Html::a('<i class="bi bi-file-earmark-text me-1"></i> Template คำอธิบายงาน (JD)', ['/jd/template/index'], ['class' => 'dropdown-item']) ?>
            </li>
            <li>
                <?= Html::a(
                    '<span class="d-flex align-items-center gap-2"><i data-lucide="signpost" style="width:15px;height:15px" class="text-muted"></i> Training Roadmap</span>',
                    ['/hr/training-roadmap/index'],
                    ['class' => 'dropdown-item', 'data-pjax' => '0']
                ) ?>
            </li>

        </ul>
    </div>
</div>
