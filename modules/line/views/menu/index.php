<?php
use yii\helpers\Url;
use yii\bootstrap5\Html;
use app\components\UserHelper;
use yii\web\HtmlResponseFormatter;
$me = UserHelper::GetEmployee();
?>

<style>
.icon-menu {
    font-size: 100px;
}
</style>
<div class="page-title-box">
    <div class="container-fluid">

        <div class="d-flex justify-content-between mt-5">
            <div class="page-title">

                <?php if($me):?>

                <div class="d-flex gap-2">
                    <?=Html::img($me->ShowAvatar(), ['class' => 'avatar avatar-md me-0'])?>
                    <div class="avatar-detail">
                        <h5 class="mb-1 text-white"><?php echo $me->fullname?></h5>
                        <p class="text-muted mb-0 fs-13"><?php echo $me->positionName()?></p>
                    </div>
                </div>
                <?php endif;?>
            </div>
            <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#staticBackdrop"
                aria-controls="staticBackdrop">
                <i class="fa-solid fa-bars fs-1"></i>
            </button>

        </div>
    </div>
</div>




<button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#staticBackdrop"
    aria-controls="staticBackdrop">
    Toggle static offcanvas
</button>

<div class="offcanvas offcanvas-start" data-bs-backdrop="static" tabindex="-1" id="staticBackdrop"
    aria-labelledby="staticBackdropLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="staticBackdropLabel">เมนูหลัก</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="p-2">

            <div class="row row-cols-2 row-cols-sm-2 row-cols-md-2 g-4">
                <div class="col">
                    <a href="<?php echo Url::to(['/settings/company'])?>">
                        <div class="card border-0 border-primary shadow-lg">
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
                        <div class="card border-0 border-primary shadow-lg">
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
                    <a href="<?php echo Url::to(['/usermanager/user'])?>">
                        <div class="card border-0 border-primary shadow-lg">
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
                        <div class="card border-0 border-primary shadow-lg">
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
                        <div class="card border-0 border-primary shadow-lg">
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
                    <a href="<?php echo Url::to(['/settings/line-messaging'])?>">
                        <div class="card border-0 border-primary shadow-lg">
                            <div class="d-flex justify-content-center align-item-center bg-secondary p-4 rounded-top">
                                <i class="fa-brands fa-line fs-1 text-white"></i>
                            </div>
                            <div class="card-body">
                                <h6 class="text-center">Messaging API</h6>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="<?php echo Url::to(['/hr/categorise','title'=>'การตั้งค่าบุคลากร'])?>">
                        <div class="card border-0 border-primary shadow-lg">
                            <div class="d-flex justify-content-center align-item-center bg-secondary p-4 rounded-top">
                                <i class="fa-solid fa-clipboard-user fs-1 text-white"></i>
                            </div>
                            <div class="card-body">
                                <h6 class="text-center"> บุคลากร</h6>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="col">
                    <a href="<?php echo Url::to(['/am/setting'])?>">
                        <div class="card border-0 border-primary shadow-lg">
                            <div class="d-flex justify-content-center align-item-center bg-secondary p-4 rounded-top">
                                <i class="bi bi-folder-check fs-1 text-white"></i>
                            </div>
                            <div class="card-body">
                                <h6 class="text-center"> ทรัพย์สิน</h6>
                            </div>
                        </div>
                    </a>
                </div>

            </div>


        </div>
    </div>
</div>