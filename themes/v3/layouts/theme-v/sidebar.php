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
                <li class="side-nav-title side-nav-item menu-title">Menu</li>
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
                    <a class="side-nav-link" href="<?= Url::to(['/me/store']) ?>">
                    <i class="fa-solid fa-cart-plus"></i>
                        <span> เบิกวัสดุอุปกรณ์</span>
                    </a>
                </li>

                <li>
                    <a class="side-nav-link" href="<?= Url::to(['/me/purchase']) ?>">
                    <i class="fa-solid fa-bag-shopping"></i>
                        <span>ขอซื้อ-ขอจ้าง</span>
                    </a>
                </li>

                <li class="side-nav-title side-nav-item menu-title">Module</li>
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
                    <a class="side-nav-link" href="<?= Url::to(['/inventory/warehouse']) ?>">
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
             
                    <!-- <a href="#collapseExample" class="side-nav-link" aria-expanded="false" data-bs-toggle="collapse"  aria-controls="collapseExample">
                        <i class="bx bx-task"></i>
                        <span> Tasks</span>
                        <span class="menu-arrow"><i class="fa-solid fa-angle-up"></i></span>
                    </a>
                    <ul aria-expanded="collapseExample" class="nav-second-level collapse" id="collapseExample">
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="task-list.html"> Task List </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="kanban-board.html"> Kanban Board </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="task-overview.html"> Task Overview </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="task-create.html"> Create Task </a>
                        </li>
                    </ul>
                </li> -->
                <!-- <li class="side-nav-title side-nav-item menu-title">Pages</li>

                <li>
                    <a href="javascript:void(0);" class="side-nav-link" aria-expanded="false">
                        <i class="bx bx-user-circle"></i>
                        <span> Authentication</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul aria-expanded="false" class="nav-second-level mm-collapse">
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="auth-login.html">Login </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="auth-login-basic.html"> Login 2 </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="auth-login-full.html"> Login 3 </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="auth-signup.html"> Register </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="auth-signup-basic.html"> Register 2 </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="auth-signup-full.html"> Register 3 </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="auth-recover.html"> Recover Password </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="auth-recover-basic.html"> Recover Password 2</a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="auth-recover-full.html"> Recover Password 3</a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="auth-lockscreen.html"> Lock Screen </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="auth-confirmation.html"> Confirmation Screen </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="auth-400.html"> 400 </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="auth-404.html"> 404 </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="auth-500.html"> 500 </a>
                        </li>
                    </ul>
                </li> -->
              
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