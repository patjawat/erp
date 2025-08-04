<?php
use yii\helpers\Url;
?>
<style>
.modal-body {
    background-color: #eeeff5;
}
</style>
<div class="container">


    <!-- <a href="<?php echo Url::to(['/me/booking-car/create','type' => 'general','title' => '<i class="fa-solid fa-car-side text-primary"></i> ขอใช้รถยนต์ทั่วไป'])?>"
    class="open-modal" data-size="modal-xl">
    <div class="card border border-3 border-primary sadow-sm hover-card">
        <div class="d-flex justify-content-center align-items-center bg-primary p-4 rounded-top">
            <i class="fa-solid fa-car-side fs-1 text-white"></i>
        </div>
        <div class="card-body">
            <h6 class="text-center">ขอใช้รถยนต์ทั่วไป</h6>
        </div>
    </div>
</a> -->
<a href="<?php echo Url::to(['/me/booking-car/create','type' => 'general','title' => '<i class="fa-solid fa-car-side text-primary"></i> ขอใช้รถยนต์ทั่วไป'])?>"
class="open-modal" data-size="modal-xl">
<div class="card mb-3 hover-card">
  <div class="row g-0">
    <div class="col-md-2">
    <div class="bg-primary p-3 rounded-pill">
                <i class="fa-solid fa-car-side fs-1 text-white"></i>
            </div>
    </div>
    <div class="col-md-8">
      <div class="card-body">
        <h5 class="card-title">ขอใช้รถยนต์ทั่วไป</h5>
      </div>
    </div>
  </div>
</div>
</a>

<a href="<?php echo Url::to(['/me/booking-car/create','type' => 'ambulance','title' => '<i class="fa-solid fa-truck-medical text-danger"></i> ขอใช้รถพยาบาล'])?>"
        class="open-modal" data-size="modal-xl">
<div class="card mb-3 hover-card-active">
  <div class="row g-0">
    <div class="col-md-2">
    <div class="bg-danger p-3 rounded-pill">
                <i class="fa-solid fa-car-side fs-1 text-white"></i>
            </div>
    </div>
    <div class="col-md-8">
      <div class="card-body">
        <h5 class="card-title">ขอใช้รถพยาบาล</h5>
      </div>
    </div>
  </div>
</div>
</a>

</div>