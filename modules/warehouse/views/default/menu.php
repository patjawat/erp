<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="d-flex gap-2">
    <?= Html::a('<i class="fa-solid fa-chart-simple me-1"></i> เลือกคลัง', ['/warehouse/warehouse'], ['class' => 'btn btn-light']) ?>
    <?= Html::a('<i class="fa-solid fa-chart-simple me-1"></i> รับสินค้า', ['/warehouse/receive'], ['class' => 'btn btn-light']) ?>
    <button class="btn btn-light" onclick="openTour()">
        <span>แนะนำ</span>
    </button><!-- end btn -->
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
            <li><?= Html::a('<i class="fa-solid fa-cash-register me-1"></i> คลัง', ['/warehouse/branch'], ['class' => 'dropdown-item']) ?>
            <li><?= Html::a('<i class="fa-solid fa-file-import me-1"></i> นำเข้า', ['/sm/vendor/import-csv'], ['class' => 'dropdown-item']) ?>
            </li>
        </ul>
    </div>

</div>


