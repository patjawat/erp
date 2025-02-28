<?php
use yii\helpers\Html;
?>
<div class="d-flex gap-2">
    <?=Html::a('<i class="fa-solid fa-gauge-high me-1"></i> Dashboard',['/hr'],['class' => 'btn btn-light'])?>
    <?=Html::a('<i class="fa-solid fa-user-tag me-1"></i> ทะเบียนบุคลากร',['/hr/employees'],['class' => 'btn btn-light'])?>

    <div class="dropdown  btn btn-light">
        <a href="javascript:void(0)" class="dropdown-toggle me-0" data-bs-toggle="dropdown"
            aria-expanded="false">
            <i class="fa-solid fa-gear"></i> การตั้งค่า
        </a>
        <div class="dropdown-menu dropdown-menu-right">
            <?=Html::a('<i class="fa-solid fa-user-tag me-1"></i> การตั้งค่าบุคลากร',['/hr/categorise','title' => 'การตั้งค่าบุคลากร'],['class' => 'btn btn-outline-primary open-modal dropdown-item','data' => ['size' => 'modal-md']])?>
            <?=Html::a('<i class="fa-solid fa-user-tag me-1"></i> การกำหนดตำแหน่ง',['/hr/position','title' => 'การตั้งค่าบุคลากร'],['class' => 'btn btn-outline-primary open-modal-x dropdown-item','data' => ['size' => 'modal-md']])?>
            <?=Html::a('<i class="fa-solid fa-file-import me-1"></i> นำเข้า CSV',['/hr/employees/import-csv'],['class' => 'dropdown-item btn btn-outline-primary'])?>

        </div>
    </div>
</div>