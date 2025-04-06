<?php
use yii\helpers\Html;
?>
<nav class="navbar navbar-expand-lg bg-body-tertiary mb-3 rounded">
  <div class="container-fluid">
    <!-- <span class="navbar-brand">เมนู</span> -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarText">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
            <?=Html::a('<i class="fa-solid fa-gauge-high"></i> Dashboard',['/booking/vehicle/dashboard'],['class' => 'nav-link active'])?>
        </li>
        <li class="nav-item">
            <?=Html::a('<i class="bi bi-ui-checks"></i> ทะเบียนจัดสรร',['/booking/vehicle/index'],['class' => 'nav-link'])?>
        </li>
        <li class="nav-item">
            <?=Html::a('<i class="fa-solid fa-car-on"></i> ภาระกิจ',['/booking/vehicle/work'],['class' => 'nav-link'])?>
        </li>
      </ul>
    </div>
  </div>
</nav>