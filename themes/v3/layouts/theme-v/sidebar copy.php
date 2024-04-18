<?php
use yii\web\View;
use yii\helpers\Url;
?>
<aside class="sidebar">
    <div class="scroll-content" id="metismenu" data-scrollbar="true" tabindex="-1"
        style="overflow: hidden; outline: none;">
        <div class="scroll-content">
            <ul id="side-menu" class="metismenu list-unstyled">
                <li class="side-nav-title side-nav-item menu-title">Menu</li>
                <li class="">
                    <a href="<?=Url::to(['/'])?>" class="side-nav-link" aria-expanded="false">
                        <i class="bx bx-home-circle"></i>
                        <span> Dashboard</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul aria-expanded="false" class="nav-second-level mm-collapse" style="height: 0px;">
                        <li class="side-nav-item">
                            <a class="side-nav-link active" href="index.html"> Multi Purpose </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="dashboard2.html"> E-commerce </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="dashboard3.html"> Server Statistics </a>
                        </li>
                    </ul>
                </li>
                <!-- <li>
                    <a href="javascript:void(0);" class="side-nav-link" aria-expanded="false">
                        <i class="bx bx-layout"></i>
                        <span> Layouts</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul aria-expanded="false" class="nav-second-level mm-collapse">
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="layout-compact-side-menu.html"> Compact Sidebar </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="layout-dark-sidebar.html"> Dark Sidebar </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="layout-icon-sidebar.html"> Icon Sidebar </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="layout-box.html"> Box Layout </a>
                        </li>
                    </ul>
                </li> -->
                <li class="side-nav-title side-nav-item menu-title">Apps</li>
                <li>
                    <a class="side-nav-link" href="<?=Url::to(['/hr'])?>">
                        <i class="bi bi-people-fill fs-4"></i>
                        <span> บุคลากร</span>
                    </a>
                </li>
                <li>
                    <a class="side-nav-link" href="<?=Url::to(['/am'])?>">
                        <i class="bi bi-folder-check fs-4"></i>
                        <span> ทรัพย์สิน</span>
                    </a>
                </li>
                <li>
                    <a class="side-nav-link" href="<?=Url::to(['/sm'])?>">
                    <i class="bi bi-box fs-4"></i>
                        <span> พัสดุ</span>
                    </a>
                </li>
                
                <li>
                    <a class="side-nav-link" href="chat.html">
                        <i class="bi bi-folder-check fs-4"></i>
                        <span> แผนงานและโครงการ</span>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0);" class="side-nav-link" aria-expanded="false">
                        <i class="bx bx-task"></i>
                        <span> Tasks</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul aria-expanded="false" class="nav-second-level mm-collapse">
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
                </li>
                <li class="side-nav-title side-nav-item menu-title">Pages</li>

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
                </li>
                <li>
                    <a href="javascript:void(0);" class="side-nav-link" aria-expanded="false">
                        <i class="bx bx-file"></i>
                        <span> Utility</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul aria-expanded="false" class="nav-second-level mm-collapse">
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="utility-animation.html"> Animation </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="utility-activity.html"> Activity </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="utility-coming-soon.html"> Coming Soon </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="utility-faq.html"> FAQs </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="utility-fix-left.html"> Fix Left Sidebar </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="utility-fix-right.html"> Fix Right Sidebar </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="utility-gallery.html"> Gallery </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="utility-helperclasses.html"> Helper Classes </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="utility-lightbox.html"> Lightbox </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="utility-maintenance.html"> Maintenance </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="utility-pricing.html"> Pricing </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="utility-scrollbar.html"> Scrollbar </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="utility-search-result.html"> Search Result </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="utility-starterpage.html"> Starter Page </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="utility-timeline.html"> Timeline </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="utility-timeline-horizontal.html"> Timeline Horizontal </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="utility-treeview.html"> Tree View </a>
                        </li>
                    </ul>
                </li>
                
                
                
                
               
                
               
                <li>
                    <a href="javascript:void(0);" class="side-nav-link" aria-expanded="false">
                        <i class="bx bx-share-alt"></i>
                        <span> Multi Level</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul aria-expanded="false" class="nav-second-level mm-collapse">
                        <li class="side-nav-item">
                            <a href="javascript:void(0);" class="side-nav-link-a" aria-expanded="false"> Level 1 <span
                                    class="menu-arrow"></span></a>
                            <ul aria-expanded="false" class="nav-third-level mm-collapse">
                                <li>
                                    <a class="side-nav-link" href="javascript:void(0)"> Level 2 </a>
                                </li>
                                <li><a class="side-nav-link" href="javascript:void(0)"> Level 2 </a></li>
                            </ul>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="javascript:void(0)"> Level 1 </a>
                        </li>
                        <li class="side-nav-item">
                            <a class="side-nav-link" href="javascript:void(0)"> Level 1 </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="../documentation/index.html" target="_blank" class="side-nav-link" aria-expanded="false">
                        <i class="bx bx-file"></i>
                        <span> Documentation</span>
                    </a>
                </li>
            </ul>
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

// $('.side-nav-link').click(function (e) { 
//     e.preventDefault();
//     alert();
    
// });


JS;
$this->registerJS($js,View::POS_END);


?>