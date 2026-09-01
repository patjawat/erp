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

    <a href="<?= Url::to(['/hr/workforce-frame/index']) ?>"
       aria-label="กรอบอัตรากำลัง"
       class="btn <?= $active === 'workforce-frame' ? 'btn-primary' : 'btn-outline-primary' ?> d-inline-flex align-items-center gap-2"
       data-pjax="0">
        <i data-lucide="users-round" width="16" height="16" aria-hidden="true"></i>
        <span class="d-none d-sm-inline">กรอบอัตรากำลัง</span>
    </a>

    <?php if (Yii::$app->user->can('hr') || Yii::$app->user->can('admin')): ?>
    <a href="<?= Url::to(['/hr/workforce/index']) ?>"
       aria-label="งาน HRD"
       class="btn <?= in_array($active, ['workforce', 'idp', 'training-roadmap', 'jd', 'appraisal', 'exit', 'talent', 'core'], true) ? 'btn-primary' : 'btn-outline-primary' ?> d-inline-flex align-items-center gap-2"
       data-pjax="0">
        <i data-lucide="briefcase-business" width="16" height="16" aria-hidden="true"></i>
        <span class="d-none d-sm-inline">งาน HRD</span>
    </a>
    <?php endif; ?>

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
                    '<span class="d-flex align-items-center gap-2"><i data-lucide="layers-3" width="15" height="15" class="text-body-secondary" aria-hidden="true"></i> ระดับตำแหน่ง</span>',
                    ['/hr/categorise', 'name' => 'position_level', 'title' => 'ระดับตำแหน่ง'],
                    [
                        'class' => 'dropdown-item open-modal',
                        'data' => ['size' => 'modal-lg'],
                    ]
                ) ?>
            </li>
            <li>
                <?= Html::a(
                    '<span class="d-flex align-items-center gap-2"><i data-lucide="badge-check" width="15" height="15" class="text-body-secondary" aria-hidden="true"></i> ความเชี่ยวชาญ</span>',
                    ['/hr/categorise', 'name' => 'expertise', 'title' => 'ความเชี่ยวชาญ'],
                    [
                        'class' => 'dropdown-item open-modal',
                        'data' => ['size' => 'modal-lg'],
                    ]
                ) ?>
            </li>
            <li><hr class="dropdown-divider my-1"></li>
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
                    '<span class="d-flex align-items-center gap-2"><i data-lucide="signpost" style="width:15px;height:15px" class="text-muted"></i> TRM Template</span>',
                    ['/hr/training-roadmap/templates'],
                    ['class' => 'dropdown-item', 'data-pjax' => '0']
                ) ?>
            </li>

        </ul>
    </div>
</div>
