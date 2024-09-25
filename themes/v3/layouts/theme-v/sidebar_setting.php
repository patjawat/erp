<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

?>

<aside class="sidebar">
    <div class="scroll-content" id="metismenu" data-scrollbar="true" tabindex="-1"
        style="overflow: hidden; outline: none;">

        <div class="scroll-content">
            
            <ul id="side-menu" class="metismenu list-unstyled">

                <li class="side-nav-title side-nav-item menu-title fs-6"><i class="fa-solid fa-gear"></i> ตั้งค่าระบบหลัก</li>
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/settings/company']) ?>">
                    <i class="bi bi-building-fill-check"></i>
                        <span>ข้อมูลองค์กร</span>
                    </a>
                </li>
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/usermanager']) ?>">
                    <i class="fa-solid fa-user-gear"></i>
                        <span> ระบบจัดการผู้ใช้งาน</span>
                    </a>
                </li>

                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/settings/line-group']) ?>">
                    <i class="fa-brands fa-line"></i>
                        <span>LineNotify</span>
                    </a>
                </li>
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/settings/line-official']) ?>">
                    <i class="fa-brands fa-line"></i>
                        <span>LineOfficial</span>
                    </a>
                </li>

                <li class="side-nav-title side-nav-item menu-title  fs-6">ตั้งค่าบุคลากร</li>
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/settings/categorise']) ?>">
                    <i class="fa-solid fa-user-gear"></i>
                        <span> ตั้งค่าบุคคล</span>
                    </a>
                </li>
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/settings/categorise']) ?>">
                    <i class="fa-solid fa-user-gear"></i>
                        <span>ตั้งค่าตำแหน่งกลุ่มงาน</span>
                    </a>
                </li>
                <li class="side-nav-title side-nav-item menu-title  fs-6">ตั้งค่าทรัพย์สิน</li>
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/settings/asset']) ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-monitor-cog fs-4 me-2"><path d="M12 17v4"/><path d="m15.2 4.9-.9-.4"/><path d="m15.2 7.1-.9.4"/><path d="m16.9 3.2-.4-.9"/><path d="m16.9 8.8-.4.9"/><path d="m19.5 2.3-.4.9"/><path d="m19.5 9.7-.4-.9"/><path d="m21.7 4.5-.9.4"/><path d="m21.7 7.5-.9-.4"/><path d="M22 13v2a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7"/><path d="M8 21h8"/><circle cx="18" cy="6" r="3"/></svg>
                        <span> ตั้งค่าครุภัณฑ์</span>
                    </a>
                </li>
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/settings/material']) ?>">
                    <i class="bi bi-box fs-4"></i>
                        <span>ตั้งค่าพัสดุ</span>
                    </a>
                </li>
                
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/inventory']) ?>">
                    <i class="fa-solid fa-cubes-stacked fs-4"></i>
                        <span> คลัง</span>
                    </a>
                </li>
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/pm']) ?>">
                        <i class="bi bi-folder-check fs-4"></i>
                        <span> แผนงานและโครงการ</span>
                    </a>
                </li>
                <li>
             
              
        </div>
        <div class="scrollbar-track scrollbar-track-x" style="display: none;">
            <div class="scrollbar-thumb scrollbar-thumb-x" style="width: 250px; transform: translate3d(0px, 0px, 0px);">
            </div>
        </div>
        <div class="scrollbar-track scrollbar-track-y" style="display: block;">
            <div class="scrollbar-thumb scrollbar-thumb-y"
                style="height: 98.1478px; transform: translate3d(0px, 0px, 0px);"></div>
        </div>
    </div>
</aside>
<?php
$js = <<< JS

    JS;
$this->registerJS($js, View::POS_END);

?>