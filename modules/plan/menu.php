<?php
use yii\helpers\Html;
?>
<li class="nav-item mt-1">
    <?php echo  Html::a('<i class="fa-solid fa-gauge me-1"></i> Dashboard <span class="badge rounded-pill badge-soft-primary text-primary fs-13"></span>',['/plan/dashboard'],['class' => 'nav-link ' . (isset($active) && $active == 'dashboard' ? 'active' : 'dashboard')])?>
</li>
<li class="nav-item mt-1">
    <?php echo  Html::a('<i class="fa-solid fa-chart-simple me-1"></i> ติดตามแผนรายจ่าย <span class="badge rounded-pill badge-soft-primary text-primary fs-13"></span>',['/plan/overview'],['class' => 'nav-link ' . (isset($active) && $active == 'overview' ? 'active' : '')])?>
</li>
<li class="nav-item mt-1">
    <?php echo  Html::a('<i class="fa-solid fa-dolly me-1"></i> แผนครุภัณฑ์ <span class="badge rounded-pill badge-soft-primary text-primary fs-13"></span>',['/plan/parcel'],['class' => 'nav-link ' . (isset($active) && $active == 'parcel' ? 'active' : '')])?>
</li>
<li class="nav-item mt-1">
    <?php echo  Html::a('<i class="fa-solid fa-user-plus me-1"></i> แผนคน <span class="badge rounded-pill badge-soft-primary text-primary fs-13"></span>',['/plan/personnel'],['class' => 'nav-link ' . (isset($active) && $active == 'personnel' ? 'active' : '')])?>
</li>
<li class="nav-item mt-1">
    <?php echo  Html::a('<i class="fa-solid fa-file-invoice me-1"></i> แผนคำขอรายจ่ายอื่น <span class="badge rounded-pill badge-soft-primary text-primary fs-13"></span>',['/plan/expenses'],['class' => 'nav-link ' . (isset($active) && $active == 'expenses' ? 'active' : '')])?>
</li>

<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle <?=(isset($active) && $active == 'setting' ? 'active' : '')?>" href="#"
        id="topnav-dashboard" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fa-solid fa-gear me-1"></i> ตั้งค่า
        <i class="bx bx-chevron-down"></i>
    </a>
    <div class="dropdown-menu" aria-labelledby="topnav-dashboard">
        <?=Html::a('<i class="fa-solid fa-caret-right me-2"></i> ประเภท',['/plan/plan-type'],['class' => 'dropdown-item'])?>
        <?=Html::a('<i class="fa-solid fa-caret-right me-2"></i> หมวดหมู่',['/plan/plan-category'],['class' => 'dropdown-item'])?>
        <?=Html::a('<i class="fa-solid fa-caret-right me-2"></i> รายการ',['/plan/plan-item'],['class' => 'dropdown-item'])?>
        <!-- <?=Html::a('<i class="fa-solid fa-caret-right me-2"></i> กลุ่มคำขอ',['/plan/plan-group'],['class' => 'dropdown-item'])?>
        <?=Html::a('<i class="fa-solid fa-caret-right me-2"></i> ประเภทงบ',['/plan/plan-budget-type'],['class' => 'dropdown-item'])?>
        <?=Html::a('<i class="fa-solid fa-caret-right me-2"></i> รายจ่าย ',['/plan/plan-item'],['class' => 'dropdown-item'])?> -->

    </div>
</li>
