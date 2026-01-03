<?php

use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="d-flex gap-2">
    <!-- <a href="<?= Url::to(['/hr/default/index']) ?>" class="btn <?= $active !== 'dashboard' ? 'btn-outline-primary' : 'btn-primary' ?>">
<i data-lucide="layout-grid"></i>  
        ภาพรวม
    </a> -->




    <div class="btn-group">
    <a href="<?= Url::to(['/booking/vehicle/calendar']) ?>" class="btn <?= $active !== 'official' ? 'btn-outline-primary' : 'btn-primary' ?>">
       <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-car-icon lucide-car"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><path d="M9 17h6"></path><circle cx="17" cy="17" r="2"></circle></svg>
        รถยนต์ทั่วไป
    </a>
        <button type="button" class="btn <?= $active !== 'official' ? 'btn-primary' : 'btn-secondary' ?> dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down-icon lucide-chevron-down"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <ul class="dropdown-menu" style="">
              <li>
                <a class="dropdown-item" href="<?= Url::to(['/booking/vehicle/index']) ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right-icon lucide-chevron-right"><path d="m9 18 6-6-6-6"/></svg>
                ทะเบียนการจอง
                </a>
            </li>
              <li>
                <a class="dropdown-item" href="<?= Url::to(['/booking/vehicle/work-official']) ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right-icon lucide-chevron-right"><path d="m9 18 6-6-6-6"/></svg>
                ทะเบียนการจัดสรร
                </a>
            </li>
        </ul>
    </div>


    <div class="btn-group">
        <a href="<?= Url::to(['/booking/vehicle/calendar-ambulance']) ?>" class="btn <?= $active !== 'ambulance' ? 'btn-outline-primary' : 'btn-primary' ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ambulance-icon lucide-ambulance">
            <path d="M10 10H6" />
            <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2" />
            <path d="M19 18h2a1 1 0 0 0 1-1v-3.28a1 1 0 0 0-.684-.948l-1.923-.641a1 1 0 0 1-.578-.502l-1.539-3.076A1 1 0 0 0 16.382 8H14" />
            <path d="M8 8v4" />
            <path d="M9 18h6" />
            <circle cx="17" cy="18" r="2" />
            <circle cx="7" cy="18" r="2" />
        </svg>
        รถฉุกเฉิน
    </a>
        <button type="button" class="btn <?= $active !== 'ambulance' ? 'btn-primary' : 'btn-secondary' ?> dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down-icon lucide-chevron-down"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <ul class="dropdown-menu" style="">
            <li>
                <a class="dropdown-item" href="<?= Url::to(['/booking/vehicle/ambulance']) ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right-icon lucide-chevron-right"><path d="m9 18 6-6-6-6"/></svg>
                    ทะเบียนการจอง
                </a>
            </li>
              <li>
                <a class="dropdown-item" href="<?= Url::to(['/booking/vehicle/work-ambulance']) ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right-icon lucide-chevron-right"><path d="m9 18 6-6-6-6"/></svg>
                    ทะเบียนการจัดสรร
                </a>
            </li>

        </ul>
    </div>


    <!-- <div class="dropdown">
        <button class="btn <?= $active !== 'setting' ? 'btn-outline-primary' : 'btn-primary' ?> dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings-icon lucide-settings">
                <path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915" />
                <circle cx="12" cy="12" r="3" />
            </svg>
            <span class="d-none d-sm-inline">ตั้งค่า</span>
        </button>

        <ul class="dropdown-menu w-100" aria-labelledby="dropdownMenuButton1" style="">
            <li>
                <a href="#" id="download-button" class="dropdown-item">
                    <i class="fa-solid fa-file-export me-1"></i>ส่งออก</a>
            </li>
            <li>
                <?= Html::a('<i class="fa-solid fa-user-tag me-1"></i> การตั้งค่าบุคลากร', ['/hr/categorise', 'title' => 'การตั้งค่าบุคลากร'], ['class' => 'btn btn-outline-primary open-modal dropdown-item', 'data' => ['size' => 'modal-md']]) ?>
            </li>
            <li>
                <?= Html::a('<i class="fa-solid fa-user-tag me-1"></i> การกำหนดตำแหน่ง', ['/hr/position', 'title' => 'การตั้งค่าบุคลากร'], ['class' => 'btn btn-outline-primary open-modal-x dropdown-item', 'data' => ['size' => 'modal-md']]) ?>
            </li>

        </ul>
    </div> -->
</div>