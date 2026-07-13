<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="d-flex align-items-center gap-2">
    <a href="<?= Url::to(['/hr/default/dashboard']) ?>"
       class="btn <?= ($active === 'overview' || $active === 'dashboard') ? 'btn-primary' : 'btn-outline-primary' ?> d-inline-flex align-items-center gap-2">
        <i data-lucide="layout-grid" width="16" height="16"></i>
        <span class="d-none d-sm-inline">ภาพรวม</span>
    </a>

    <a href="<?= Url::to(['/hr/employees']) ?>"
       class="btn <?= $active === 'employees' ? 'btn-primary' : 'btn-outline-primary' ?> d-inline-flex align-items-center gap-2">
        <i data-lucide="users" width="16" height="16"></i>
        <span class="d-none d-sm-inline">ทะเบียนบุคลากร</span>
    </a>

    <a href="<?= Url::to(['/hr/organization/diagram']) ?>"
       class="btn <?= $active === 'organization' ? 'btn-primary' : 'btn-outline-primary' ?> d-inline-flex align-items-center gap-2">
        <i data-lucide="network" width="16" height="16"></i>
        <span class="d-none d-sm-inline">ผังโครงสร้างองค์กร</span>
    </a>

    <a href="<?= Url::to(['/hr/elearning']) ?>"
       class="btn <?= $active === 'elearning' ? 'btn-primary' : 'btn-outline-primary' ?> d-inline-flex align-items-center gap-2">
        <i data-lucide="graduation-cap" width="16" height="16"></i>
        <span class="d-none d-sm-inline">E-learning</span>
    </a>

    <div class="dropdown">
        <button class="btn <?= $active === 'setting' ? 'btn-primary' : 'btn-outline-secondary' ?> dropdown-toggle d-inline-flex align-items-center gap-2"
                type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
            <i data-lucide="settings" width="16" height="16"></i>
            <span class="d-none d-sm-inline">ตั้งค่า</span>
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="dropdownMenuButton1">
            <?php if (Yii::$app->user->can('hr') || Yii::$app->user->can('admin')): ?>
            <li>
                <?= Html::a(
                    '<span class="d-flex align-items-center gap-2"><i data-lucide="monitor-play" style="width:15px;height:15px" class="text-muted"></i> จัดการระบบ E-learning</span>',
                    ['/hr/elearning-admin/index'],
                    ['class' => 'dropdown-item']
                ) ?>
            </li>
            <li><hr class="dropdown-divider my-1"></li>
            <?php endif; ?>
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

        </ul>
    </div>
</div>
