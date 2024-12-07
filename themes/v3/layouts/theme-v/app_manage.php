<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\components\UserHelper;
?>
<div class="d-none d-lg-inline-flex ms-2 dropdown" data-aos="zoom-in" data-aos-delay="100">
    <button data-bs-toggle="dropdown" aria-haspopup="true" type="button" id="page-header-app-dropdown"
        aria-expanded="false" class="btn header-item notify-icon">
        <i class="fa-solid fa-bars-progress"></i>
    </button>
    <div aria-labelledby="page-header-app-dropdown" class="dropdown-menu-lg dropdown-menu-right dropdown-menu"
        style="width: 600px;">
        <div class="px-lg-2">
            <h6 class="text-center mt-3"><i class="fa-solid fa-bars-progress"></i> ระบบงาน</h6>
            <div class="row p-3">
                <div class="col-4 mt-1">
                    <a href="<?=Url::to(['/helpdesk/general']);?>">
                        <div
                            class="d-flex flex-column align-items-center justify-content-center bg-light p-4 rounded-2">
                            <i class="fa-solid fa-screwdriver-wrench fs-2"></i>
                            <div>งานซ่อมบำรุง</div>
                        </div>
                    </a>
                </div>
                <div class="col-4 mt-1">
                    <a href="<?=Url::to(['/helpdesk/computer']);?>">
                        <div
                            class="d-flex flex-column align-items-center justify-content-center bg-light p-4 rounded-2">
                            <i class="fa-solid fa-computer fs-2"></i>
                            <div>ศูนย์คอมพิวเตอร์</div>
                        </div>
                    </a>
                </div>
                <div class="col-4 mt-1">
                    <a href="<?=Url::to(['/helpdesk/medical']);?>">
                        <div
                            class="d-flex flex-column align-items-center justify-content-center bg-light p-4 rounded-2">
                            <i class="fa-solid fa-briefcase-medical fs-2"></i>
                            <div>ศูนย์เครื่องมือแพทย์</div>
                        </div>
                    </a>
                </div>
                <div class="col-4 mt-1">
                    <a href="<?=Url::to(['/hr/leave']);?>">
                        <div
                            class="d-flex flex-column align-items-center justify-content-center bg-light p-4 rounded-2">
                            <i class="fa-solid fa-briefcase-medical fs-2"></i>
                            <div>ระบบลา</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>