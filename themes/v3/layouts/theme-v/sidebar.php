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
                <li class="side-nav-title side-nav-item menu-title fs-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layout-list"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/><path d="M14 4h7"/><path d="M14 9h7"/><path d="M14 15h7"/><path d="M14 20h7"/></svg>    
                Menu</li>
                <li class="">
                    <a href="<?= Url::to(['/']) ?>" class="side-nav-link" aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-gauge"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg> <span>&nbsp;Main Dashboard</span>
                        <!-- <span class="menu-arrow"></span> -->
                    </a>
                    <!-- <ul aria-expanded="false" class="nav-second-level mm-collapse" style="height: 0px;">
                        <li class="side-nav-item">
                            <a class="side-nav-link active" href="index.html"> Multi Purpose </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="dashboard2.html"> E-commerce </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="dashboard3.html"> Server Statistics </a>
                        </li>
                    </ul> -->
                </li>
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/me']) ?>">
                    <i class="fa-solid fa-user-tie"></i>
                        <span> My Dashboard</span>
                    </a>
                </li>
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/me/repair']) ?>">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                        <span> แจ้งซ่อม</span>
                    </a>
                </li>

                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/me/purchase']) ?>">
                    <i class="fa-solid fa-bag-shopping"></i>
                        <span>ขอซื้อ-ขอจ้าง</span>
                    </a>
                </li>

                <li class="side-nav-title side-nav-item menu-title fs-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layout-list"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/><path d="M14 4h7"/><path d="M14 9h7"/><path d="M14 15h7"/><path d="M14 20h7"/></svg>    
                Module</li>
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/hr']) ?>">
                        <i class="bi bi-people-fill fs-4"></i>
                        <span> บุคลากร</span>
                    </a>
                </li>
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/am']) ?>">
                        <i class="bi bi-folder-check fs-4"></i>
                        <span> ทรัพย์สิน</span>
                    </a>
                </li>
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/sm']) ?>">
                    <i class="bi bi-box fs-4"></i>
                        <span> พัสดุ</span>
                    </a>
                </li>
                
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/inventory']) ?>">
                    <i class="fa-solid fa-cubes-stacked fs-4"></i>
                        <span> คลัง</span>
                    </a>
                </li>
                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/settings']) ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings fs-4 me-2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                        <span> การตั้งค่าระบบ</span>
                    </a>
                </li>
                <!-- <li>
                    <a class="side-nav-link" href="<?= Url::to(['/pm']) ?>">
                        <i class="bi bi-folder-check fs-4"></i>
                        <span> แผนงานและโครงการ</span>
                    </a>
                </li> -->
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