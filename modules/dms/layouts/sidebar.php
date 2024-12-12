<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
$moduleId = Yii::$app->controller->module->id; 
?>
<style>
.sidebar .metismenu > li > a {
    color: #4b4b5a;
    display: flex
;
    align-items: center;
    padding: 1px 1px;
    font-size: 0.9375rem;
    font-weight: 400;
    position: relative;
    transition: all 0.4s;
    border-left: 3px solid transparent;
}
</style>
<aside class="sidebar">
    <div class="scroll-content" id="metismenu" data-scrollbar="true" tabindex="-1"
        style="overflow: hidden; outline: none;">
        <div class="scroll-content">
            
            <ul id="side-menu" class="metismenu list-unstyled">
                <li class="side-nav-title side-nav-item menu-title fs-6">
                <i class="bi bi-journal-text fs-4"></i>
                สารบรรณ</li>
               
                <li class="p-2 ps-4">
                    <a class="side-nav-link rounded <?=$moduleId == 'am' ? 'active' : null?>" href="<?= Url::to(['/dms/indbox']) ?>">
                        <i class="bi bi-folder-check fs-4"></i>
                        <div class="d-flex justify-content-between w-100">
                            <span> หนังสือรอรับ</span>
                            <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">1400</span>
                        </div>
                    </a>
                </li>
                <li class="p-2 ps-4">
                    <a class="side-nav-link rounded <?=$moduleId == 'am' ? 'active' : null?>" href="<?= Url::to(['/dms/received']) ?>">
                        <i class="bi bi-folder-check fs-4"></i>
                        <div class="d-flex justify-content-between w-100">
                            <span> หนังสือรับ</span>
                            <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">1400</span>
                        </div>
                    </a>
                </li>
                
                <li class="p-2 ps-4">
                    <a class="side-nav-link rounded <?=$moduleId == 'hr' ? 'active' : null?>" href="<?= Url::to(['/dms/sent']) ?>">
                    <i class="bi bi-folder-check fs-4"></i>
                    <div class="d-flex justify-content-between w-100">
                            <span> หนังสือส่ง</span>
                            <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold">130</span>
                        </div>
                    </a>
                </li>
              
                <?php if(Yii::$app->user->can('admin')):?>
                    <li class="p-2 ps-4">
                    <a class="side-nav-link" href="<?= Url::to(['/settings/company']) ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings fs-4 me-2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                        <span> การตั้งค่าระบบ</span>
                    </a>
                </li>
                <?php endif;?>
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