
<?php
use yii\helpers\Url;
?>
<style>
    .modal-body {
    background-color: #eeeff5;
}
</style>
<div class="container">

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-2 g-3">
        <div class="col">
            <a href="<?php echo Url::to(['/me/booking-car/create','type' => 'general','title' => '<i class="fa-solid fa-car-side text-primary"></i> ขอใช้รถยนต์ทั่วไป'])?>" class="open-modal" data-size="modal-xl">
                <div class="card border border-3 border-primary sadow-sm hover-card">
                    <div class="d-flex justify-content-center align-items-center bg-primary p-4 rounded-top">
                        <i class="fa-solid fa-car-side fs-1 text-white"></i>
                    </div>
                    <div class="card-body">
                        <h6 class="text-center">ขอใช้รถยนต์ทั่วไป</h6>
                    </div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="<?php echo Url::to(['/me/booking-car/create','type' => 'ambulance','title' => '<i class="fa-solid fa-truck-medical text-danger"></i> ขอใช้รถพยาบาล'])?>" class="open-modal" data-size="modal-xl">
                <div class="card border-1 shadow-sm hover-card">
                    <div class="d-flex justify-content-center align-items-center bg-danger p-4 rounded-top">
                        <i class="fa-solid fa-truck-medical fs-1 text-white"></i>
                    </div>
                    <div class="card-body">
                        <h6 class="text-center">ขอใช้รถพยาบาล</h6>
                    </div>
                </div>
            </a>
        </div>
        </div>