<?php

use yii\helpers\Html;
?>
<div class="row g-3 mt-1 mb-3">

    <!-- ทรัพย์สินทั้งหมด -->
    <div class="col-6 col-xl-3">
      <div class="card h-100 border shadow-sm">
        <div class="card-body py-3">
          <div class="d-flex align-items-center justify-content-between gap-2">
            <div class="d-flex flex-column gap-3">
              <span class="fw-bold fs-3"><?= number_format((int) $equipStats['total'], 0) ?></span> 
              <span class="text-primary-emphasis">ทรัพย์สินทั้งหมด (รายการ)</span>
            </div>
            <div class="bg-primary-subtle text-primary-emphasis p-3 rounded-circle">
              <i data-lucide="package"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- สภาพดี -->
    <div class="col-6 col-xl-3">
      <div class="card h-100 border shadow-sm">
        <div class="card-body py-3">
          <div class="d-flex align-items-center justify-content-between gap-2">
            <div class="d-flex flex-column gap-3">
              <span class="fw-bold fs-3"><?= number_format((int) $equipStats['good'], 0) ?></span> 
              <span class="text-success-emphasis">สภาพดี (รายการ)</span>
            </div>
            <div class="bg-success-subtle text-success-emphasis p-3 rounded-circle">
              <i data-lucide="check-circle"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ชำรุด / รอซ่อม -->
    <div class="col-6 col-xl-3">
      <div class="card h-100 border shadow-sm">
        <div class="card-body py-3">
          <div class="d-flex align-items-center justify-content-between gap-2">
            <div class="d-flex flex-column gap-3">
              <span class="fw-bold fs-3"><?= number_format((int) $equipStats['damaged'], 0) ?></span> 
              <span class="text-warning-emphasis">ชำรุด / รอซ่อม (รายการ)</span>
            </div>
            <div class="bg-warning-subtle text-warning-emphasis p-3 rounded-circle">
              <i data-lucide="wrench"></i>
            </div>  
          </div>
        </div>
      </div>
    </div>

    <!-- รวมราคาแรกรับ -->
    <div class="col-6 col-xl-3">
      <div class="card h-100 border shadow-sm">
        <div class="card-body py-3">
          <div class="d-flex align-items-center justify-content-between gap-2">
            <div class="d-flex flex-column gap-3">
              <span class="fw-bold fs-3"><?= Html::encode(number_format($equipStats['total_value'], 2)) ?></span> 
              <span class="text-info-emphasis">รวมราคาแรกรับ (บาท)</span>
            </div>
            <div class="bg-info-subtle text-info-emphasis p-3 rounded-circle">
              <i data-lucide="banknote"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>
