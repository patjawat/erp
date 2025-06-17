<?php
use yii\helpers\Url;
$this->title = "การตั้งค่าระบบ";
?>

<?php $this->beginBlock('page-title'); ?>
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings fs-4 me-2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>
<?= $this->title; ?>
<?php $this->endBlock(); ?>




<div class="container">

    <div class="row row-cols-1 row-cols-sm-6 row-cols-md-6 g-3">
        <div class="col">
            <a href="<?php echo Url::to(['/settings/company'])?>">
                <div class="card border-0 shadow-sm hover-card">
                    <div class="d-flex justify-content-center align-items-center bg-secondary p-4 rounded-top">
                        <i class="fa-solid fa-house-medical-flag fs-1 text-white"></i>
                    </div>
                    <div class="card-body">
                        <h6 class="text-center text-primary">ข้อมูลองค์กร</h6>
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
                        <h6 class="text-center text-primary">ตั้งค่าสี</h6>
                    </div>
                </div>
            </a>
        </div>
        <div class="col">
            <a href="<?php echo Url::to(['/usermanager/user'])?>">
                <div class="card border-0 shadow-sm hover-card">
                    <div class="d-flex justify-content-center align-item-center bg-secondary p-4 rounded-top">
                        <i class="fa-solid fa-user-gear fs-1 text-white"></i>
                    </div>
                    <div class="card-body">
                        <h6 class="text-center text-primary">ผู้ใช้งาน</h6>
                    </div>
                </div>

            </a>
        </div>
        <!-- <div class="col">
            <a href="<?php  Url::to(['/settings/line-group'])?>">
                <div class="card border-0 shadow-sm hover-card">
                    <div class="d-flex justify-content-center align-item-center bg-secondary p-4 rounded-top">
                        <i class="fa-brands fa-line fs-1 text-white"></i>
                    </div>
                    <div class="card-body">
                        <h6 class="text-center text-primary">LineMsg</h6>
                    </div>
                </div>
            </a>
        </div> -->
        <div class="col">
            <a href="<?php echo Url::to(['/settings/telegram'])?>">
                <div class="card border-0 shadow-sm hover-card">
                    <div class="d-flex justify-content-center align-item-center bg-secondary p-4 rounded-top">
                        <i class="fa-brands fa-telegram fs-1 text-white"></i>
                    </div>
                    <div class="card-body">
                        <h6 class="text-center text-primary">Telegram</h6>
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
                        <h6 class="text-center text-primary">LineOfficial</h6>
                    </div>
                </div>
            </a>
        </div>

        <div class="col">
            <a href="<?php echo Url::to(['/hr/categorise','title'=>'การตั้งค่าบุคลากร'])?>">
                <div class="card border-0 shadow-sm hover-card">
                    <div class="d-flex justify-content-center align-item-center bg-secondary p-4 rounded-top">
                        <i class="fa-solid fa-clipboard-user fs-1 text-white"></i>
                    </div>
                    <div class="card-body">
                        <h6 class="text-center text-primary"> บุคลากร</h6>
                    </div>
                </div>
            </a>
        </div>

        <div class="col">
            <a href="<?php echo Url::to(['/am/setting'])?>">
                <div class="card border-0 shadow-sm hover-card">
                    <div class="d-flex justify-content-center align-item-center bg-secondary p-4 rounded-top">
                        <i class="bi bi-folder-check fs-1 text-white"></i>
                    </div>
                    <div class="card-body">
                        <h6 class="text-center text-primary"> ทรัพย์สิน</h6>
                    </div>
                </div>
            </a>
        </div>
       
    </div>


</div>