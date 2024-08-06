<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="d-flex gap-2">
    <?= Html::a('<i class="fa-solid fa-chart-simple me-1"></i> เลือกคลัง', ['/inventory/warehouse/clear'], ['class' => 'btn btn-light']) ?>
    <?= Html::a('<i class="fa-solid fa-circle-down me-1 text-success"></i> ตรวจรับ', ['/inventory/receive'], ['class' => 'btn btn-light']) ?>
    <?php //  Html::a('<i class="fa-solid fa-circle-down me-1 text-success"></i> รับเข้า', ['/inventory/receive'], ['class' => 'btn btn-light']) ?>
    <?= Html::a('<i class="fa-solid fa-circle-up me-1 text-danger"></i> จ่ายออก', ['/inventory/stock-request'], ['class' => 'btn btn-light']) ?>
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
            <li><?= Html::a('<i class="fa-solid fa-people-group me-1 fs-5"></i>กรรมการตรวจรับ', ['/inventory/commitee'], ['class' => 'dropdown-item']) ?>
            <li><?= Html::a('<i class="fa-solid fa-file-import me-1"></i> นำเข้า', ['/sm/vendor/import-csv'], ['class' => 'dropdown-item']) ?>
            </li>
        </ul>
    </div>

</div>


