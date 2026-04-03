<?php

use yii\helpers\Html;
?>




  <div class="row g-4 mt-1">

    <!-- ทรัพย์สินทั้งหมด -->
    <div class="col-md-3">
      <div class="card">
        <div class="card-body py-3">
          <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
            <div class="fw-bold fs-4">
              <?= (int) $equipStats['total'] ?> รายการ
            </div>
            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-pill">
              <i data-lucide="package"></i>
            </div>
          </div>
          <span class="text-muted">ทรัพย์สินทั้งหมด</span>
        </div>
      </div>
    </div>

    <!-- สภาพดี -->
    <div class="col-md-3">
      <div class="card">
        <div class="card-body py-3">
          <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
            <div class="fw-bold fs-4">
              <?= (int) $equipStats['good'] ?> รายการ
            </div>
            <div class="bg-success bg-opacity-10 text-success p-3 rounded-pill">
              <i data-lucide="check-circle"></i>
            </div>
          </div>
          <span class="text-muted">สภาพดี</span>
        </div>
      </div>
    </div>

    <!-- ชำรุด / รอซ่อม -->
    <div class="col-md-3">
      <div class="card">
        <div class="card-body py-3">
          <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
            <div class="fw-bold fs-4">
              <?= (int) $equipStats['damaged'] ?> รายการ
            </div>
            <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-pill">
              <i data-lucide="wrench"></i>
            </div>
          </div>
          <span class="text-muted">ชำรุด / รอซ่อม</span>
        </div>
      </div>
    </div>

    <!-- รวมราคาแรกรับ -->
    <div class="col-md-3">
      <div class="card">
        <div class="card-body py-3">
          <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
            <div class="fw-bold fs-4">
              <?= Html::encode(number_format($equipStats['total_value'], 2)) ?> บาท
            </div>
            <div class="bg-info bg-opacity-10 text-info p-3 rounded-pill">
              <i data-lucide="banknote"></i>
            </div>
          </div>
          <span class="text-muted">รวมราคาแรกรับ</span>
        </div>
      </div>
    </div>
  </div>
