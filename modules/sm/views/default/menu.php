<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="d-flex gap-2">
    <?= Html::a('<i class="fa-solid fa-chart-simple me-1"></i> Dashbroad', ['/sm'], ['class' => 'btn btn-light']) ?>
    <?php //  Html::a('<i class="fa-solid fa-box-open me-1"></i> ทะเบียนคุม', ['/sm/order'], ['class' => 'btn btn-light']) ?>
    <?php //  Html::a('<i class="fa-solid fa-box-open me-1"></i> ทะเบียนคุม', ['/sm/order'], ['class' => 'btn btn-light']) ?>
    <div class="btn-group">
       <span class="btn btn-light">
       <i class="fa-solid fa-gear"></i>
          ตั้งค่า
        </span>
        <button type="button" class="btn btn-warning dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"
            aria-expanded="false" data-bs-reference="parent">
            <i class="bi bi-caret-down-fill"></i>
        </button>
        <ul class="dropdown-menu">
            <li><?= Html::a('<i class="fa-solid fa-cash-register me-1"></i> ผู้แทนจำหน่าย', ['/sm/vendor'], ['class' => 'dropdown-item']) ?>
            <li><?= Html::a('<i class="fa-brands fa-product-hunt me-1"></i> วัสดุ', ['/sm/product', 'title' => 'ตั้งค่่าวัสดุ'], ['class' => 'dropdown-item']) ?>
            <li><?= Html::a('<i class="bi bi-box-fill me-1"></i> ทรัพย์สิน', ['/sm/asset-item', 'group' => 3, 'title' => 'ตั้งค่าครุภัณฑ์'], ['class' => 'dropdown-item']) ?>
            <li><?= Html::a('<i class="fa-solid fa-window-restore me-1"></i> ตั้งค่าหน่วยนับ', ['/sm/asset-unit'], ['id' => 'unit', 'class' => 'dropdown-item open-modal modal-lg']) ?>
            <li><?= Html::a('<i class="fa-solid fa-file-import me-1"></i> นำเข้า', ['/sm/vendor/import-csv'], ['class' => 'dropdown-item']) ?>
            </li>
        </ul>
    </div>

</div>

