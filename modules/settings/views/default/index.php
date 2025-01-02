<?php
use yii\helpers\Url;
?>
<style>
.hover-card {
    border: 2px solid transparent !important;
    /* Ensures border is initially present but invisible */
    transition: border-color 0.3s ease, transform 0.3s ease;
    /* Smooth transition for both border and scale */
}

.hover-card:hover {
    border-color: #007bff !important;
    /* Change the border color on hover */
    transform: scale(1.04);
    /* Optional: Slightly scale the card */
}
</style>
<div class="container">

    <div class="row row-cols-1 row-cols-sm-6 row-cols-md-6 g-3 mt-5">
        <div class="col">
            <a href="<?php echo Url::to(['/settings/company'])?>">
                <div class="card border-0 shadow-sm hover-card">
                    <div class="d-flex justify-content-center align-items-center bg-secondary p-4 rounded-top">
                        <i class="fa-solid fa-house-medical-flag fs-1 text-white"></i>
                    </div>
                    <div class="card-body">
                        <h6 class="text-center">ข้อมูลองค์กร</h6>
                    </div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="<?php echo Url::to(['/setting'])?>">
                <div class="card border-0 shadow-sm hover-card">
                    <div class="d-flex justify-content-center align-items-center bg-secondary p-4 rounded-top">
                        <i class="fas fa-palette fs-1 text-white"></i>
                    </div>
                    <div class="card-body">
                        <h6 class="text-center">ตั้งค่าสี</h6>
                    </div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="<?php echo Url::to(['/usermanager'])?>">
                <div class="card border-0 shadow-sm hover-card">
                    <div class="d-flex justify-content-center align-item-center bg-secondary p-4 rounded-top">
                        <i class="fa-solid fa-user-gear fs-1 text-white"></i>
                    </div>
                    <div class="card-body">
                        <h6 class="text-center">ผู้ใช้งาน</h6>
                    </div>
                </div>

            </a>
        </div>
        <div class="col">
            <a href="<?php echo Url::to(['/settings/line-group'])?>">
                <div class="card border-0 shadow-sm hover-card">
                    <div class="d-flex justify-content-center align-item-center bg-secondary p-4 rounded-top">
                        <i class="fa-brands fa-line fs-1 text-white"></i>
                    </div>
                    <div class="card-body">
                        <h6 class="text-center">LineNotify</h6>
                    </div>
                </div>
            </a>
        </div>

        <div class="col">
            <a href="<?php echo Url::to(['/settings/line-official'])?>">
                <div class="card border-0 shadow-sm hover-card">
                    <div class="d-flex justify-content-center align-item-center bg-secondary p-4 rounded-top">
                        <i class="fa-brands fa-line fs-1 text-white"></i>
                    </div>
                    <div class="card-body">
                        <h6 class="text-center">LineOfficial</h6>
                    </div>
                </div>
            </a>
        </div>

        <div class="col">
            <a href="<?php echo Url::to(['/settings/line-official'])?>">
                <div class="card border-0 shadow-sm hover-card">
                    <div class="d-flex justify-content-center align-item-center bg-secondary p-4 rounded-top">
                        <i class="fa-solid fa-clipboard-user fs-1 text-white"></i>
                    </div>
                    <div class="card-body">
                        <h6 class="text-center"> ตั้งค่าบุคคล</h6>
                    </div>
                </div>
            </a>
        </div>

        
    </div>


</div>