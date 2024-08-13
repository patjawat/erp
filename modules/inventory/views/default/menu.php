<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="d-flex gap-2">
    <?= Html::a('<i class="fa-solid fa-chart-simple me-1"></i> เลือกคลัง', ['/inventory/warehouse/clear'], ['class' => 'btn btn-light']) ?>
    <?= Html::a('<i class="fa-solid fa-circle-down me-1 text-success"></i> ตรวจรับ', ['/inventory/receive'], ['class' => 'btn btn-light']) ?>
    <?= Html::a('<i class="fa-solid fa-circle-down me-1 text-success"></i> เบิกคลัง', ['/inventory/store'], ['class' => 'btn btn-light']) ?>
    <?= Html::a('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-list-todo"><rect x="3" y="5" width="6" height="6" rx="1"/><path d="m3 17 2 2 4-4"/><path d="M13 6h8"/><path d="M13 12h8"/><path d="M13 18h8"/></svg> Srock Card', ['/inventory/stock-card'], ['class' => 'btn btn-light']) ?>
    <?php  Html::a('<i class="fa-solid fa-circle-up me-1 text-danger"></i> จ่ายออก', ['/inventory/stock-request'], ['class' => 'btn btn-light']) ?>
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


