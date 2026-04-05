<?php

use yii\helpers\Html;
?>




  <div class="row g-4 mt-1">

    <!-- ทรัพย์สินทั้งหมด -->
    <div class="col-md-3">
      <div class="card">
        <div class="card-body py-2">
          <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
            <div class="d-flex flex-column gap-3">
              <span class="fw-bold fs-3"><?= (int) $equipStats['total'] ?></span> 
              <span class="text-primary">ทรัพย์สินทั้งหมด (รายการ)</span>
            </div>
            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-pill">
              <i data-lucide="package"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- สภาพดี -->
    <div class="col-md-3">
      <div class="card">
        <div class="card-body py-2">
          <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
            <div class="d-flex flex-column gap-3">
              <span class="fw-bold fs-3"><?= (int) $equipStats['good'] ?></span> 
              <span class="text-success">สภาพดี (รายการ)</span>
            </div>
            <div class="bg-success bg-opacity-10 text-success p-3 rounded-pill">
              <i data-lucide="check-circle"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ชำรุด / รอซ่อม -->
    <div class="col-md-3">
      <div class="card">
        <div class="card-body py-2">
          <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
            <div class="d-flex flex-column gap-3">
              <span class="fw-bold fs-3"><?= (int) $equipStats['damaged'] ?></span> 
              <span class="text-warning">ชำรุด / รอซ่อม (รายการ)</span>
            </div>
            <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-pill">
              <i data-lucide="wrench"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- รวมราคาแรกรับ -->
    <div class="col-md-3">
      <div class="card">
        <div class="card-body py-2">
          <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
            <div class="d-flex flex-column gap-3">
              <span class="fw-bold fs-3"><?= Html::encode(number_format($equipStats['total_value'], 2)) ?></span> 
              <span class="text-info">รวมราคาแรกรับ (บาท)</span>
            </div>
            <div class="bg-info bg-opacity-10 text-info p-3 rounded-pill">
              <i data-lucide="banknote"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
