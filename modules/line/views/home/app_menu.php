<?php
use yii\helpers\Url;
?>
<div class="p-2 mb-3">
        <h6 class="text-white">App Menu</h6>
        <div class="overflow-scroll d-flex flex-row borde-0 gap-4 mt-4"
            style="white-space: nowrap; max-width: 100%; height: 100px;">
            <div class="d-flex flex-column gap-2 border-0 text-white">
                <div class=" bg-secondary rounded-pill p-3 shadow border border-white">
                    <i class="fa-solid fa-screwdriver-wrench fs-1"></i>
                </div>
                <p class="text-center">แจ้งซ่อม</p>
            </div>
            <div class="d-flex flex-column gap-2 border-0 text-white">
                <a href="<?php echo Url::to('/line/leave/form-step')?>">
                    <div class=" bg-secondary rounded-pill p-3 shadow border border-white">
                        <i class="fa-solid fa-calendar-day fs-1"></i>
                    </div>
                    <p class="text-center">ขอลา</p>
                </a>
            </div>
            <div class="d-flex flex-column gap-2 border-0 text-white">
                <div class=" bg-secondary rounded-pill p-3 shadow border border-white">
                    <i class="fa-solid fa-car-side fs-1"></i>
                </div>
                <p class="text-center">จองรถ</p>
            </div>
            <div class="d-flex flex-column gap-2 border-0 text-white">
                <div class=" bg-secondary rounded-pill p-3 shadow border border-white">
                    <i class="fa-solid fa-person-chalkboard fs-1"></i>
                </div>
                <p class="text-center">ห้องประชุม</p>
            </div>

            <div class="d-flex flex-column gap-2 border-0 text-white">
                <div class=" bg-secondary rounded-pill p-3 shadow border border-white">
                    <i class="fa-solid fa-triangle-exclamation fs-1 ms-1"></i>
                </div>
                <p class="text-center">ความเสี่ยง</p>
            </div>

        </div>
    </div>