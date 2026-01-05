 <?php

    use yii\helpers\Url;
    ?>


         <div class="d-flex flex-row gap-3 bg-light p-1 rounded-3">
             <a href="<?= Url::to(['/approve-v2/default']) ?>" class="position-relative btn btn-sm d-flex align-items-center gap-2 rounded-3 tab-btn <?= $menu === 'index'  ? 'bg-white' : '' ?>">
                 <i data-lucide="file-text"></i>
                 ทั้งหมด
                 <!-- <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">+99</span> -->
             </a>
             <a href="<?= Url::to(['/approve-v2/leave']) ?>" class="btn d-flex align-items-center gap-2 px-3 rounded-3 tab-btn <?= $menu === 'leave'  ? 'bg-white' : '' ?>">
                 <i data-lucide="calendar"></i>
                 วันลา
             </a>

             <a href="<?= Url::to(['/approve-v2/vehicle']) ?>" class="position-relative btn btn-sm d-flex align-items-center gap-2 px-3 rounded-3 tab-btn <?= $menu === 'vehicle'   ? 'bg-white' : '' ?>">
                 <i data-lucide="car-front"></i>
                 ใช้รถ
                 <!-- <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">8</span> -->
             </a>


             <a href="<?= Url::to(['/approve-v2/purchase']) ?>" class="btn btn-sm d-flex align-items-center gap-2 px-3 rounded-3 tab-btn <?= $menu === 'maintenance'   ? 'bg-white' : '' ?>">
                 <i data-lucide="shopping-cart"></i>
                 จัดซื้อจัดจ้าง
             </a>

             <a href="<?= Url::to(['/approve-v2/main-stock']) ?>" class="btn btn-sm d-flex align-items-center gap-2 px-3 rounded-3 tab-btn <?= $menu === 'calibration'   ? 'bg-white' : '' ?>">
                 <i data-lucide="shopping-basket"></i>
                 เบิกวัสดุ
             </a>

             <a href="<?= Url::to(['/approve-v2/development']) ?>" class="btn btn-sm d-flex align-items-center gap-2 px-3 rounded-3 tab-btn <?= $menu === 'borrow'   ? 'bg-white' : '' ?>">
                 <i data-lucide="user-star"></i>
                 อบรม/ประชุม/ดูงาน
             </a>

             <a href="<?= Url::to(['/approve-v2/asset-move']) ?>" class="btn btn-sm d-flex align-items-center gap-2 px-3 rounded-3 tab-btn <?= $menu === 'move'   ? 'bg-white' : '' ?>">
                 <i data-lucide="arrow-left-right"></i>
                 เคลื่อนย้ายครุภัณฑ์
             </a>
         </div>
         
