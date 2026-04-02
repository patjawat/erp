<?php
use yii\helpers\Html;
?>

<div class="container-fluid my-4">

  <div class="row g-4">

    <!-- ทรัพย์สินทั้งหมด -->
    <div class="col-md-3">
      <div class="dashboard-card dashboard-card--blue">

        <div class="d-flex align-items-center gap-2 mb-2">
          <i data-lucide="package"></i>
          <span class="dashboard-card__title">ทรัพย์สินทั้งหมด</span>
        </div>

        <div class="dashboard-card__value">
        <?= (int) $equipStats['total'] ?> รายการ
        </div>

      </div>
    </div>

    <!-- สภาพดี -->
    <div class="col-md-3">
      <div class="dashboard-card dashboard-card--green">

        <div class="d-flex align-items-center gap-2 mb-2">
          <i data-lucide="check-circle"></i>
          <span class="dashboard-card__title">สภาพดี</span>
        </div>

        <div class="dashboard-card__value">
        <?= (int) $equipStats['good'] ?> รายการ
        </div>

      </div>
    </div>

    <!-- ชำรุด / รอซ่อม -->
    <div class="col-md-3">
      <div class="dashboard-card dashboard-card--orange">

        <div class="d-flex align-items-center gap-2 mb-2">
          <i data-lucide="wrench"></i>
          <span class="dashboard-card__title">ชำรุด / รอซ่อม</span>
        </div>

        <div class="dashboard-card__value">
        <?= (int) $equipStats['damaged'] ?> รายการ
        </div>

      </div>
    </div>

    <!-- รวมราคาแรกรับ -->
    <div class="col-md-3">
      <div class="dashboard-card dashboard-card--purple">

        <div class="d-flex align-items-center gap-2 mb-2">
          <i data-lucide="banknote"></i>
          <span class="dashboard-card__title">รวมราคาแรกรับ</span>
        </div>

        <div class="dashboard-card__value">
        <?= Html::encode(number_format($equipStats['total_value'], 2)) ?> บาท
        </div>

      </div>
    </div>

  </div>

</div>
