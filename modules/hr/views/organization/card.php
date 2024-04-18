<?php
use yii\helpers\Url;
?>
<div class="card mb-0" style="width:200px">
    <div class="card-body">
    <div class="dropdown float-end">
            <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="fa-solid fa-sliders"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right" style="">
             
                    <?=app\components\AppHelper::Btn([
                        'title' => '<i class="fa-regular fa-pen-to-square me-1"></i> เพิ่ม node',
                'url' => ['/hr/organization/create','id' => 1,'level' => 1],
                'modal' => true,
                'size' => 'lg',
                'class' => 'dropdown-item open-modal'
                ])?>
                  <?=app\components\AppHelper::Btn([
                        'title' => '<i class="fa-regular fa-pen-to-square me-1"></i> เพิ่ม Children',
                'url' => ['/hr/organization/create','id' => 1],
                'modal' => true,
                'size' => 'lg',
                'class' => 'dropdown-item open-modal'
                ])?>

                <a class="dropdown-item open-modal" href="<?=Url::to(["/hr/organization/update","id" => 1])?>"
                    data-size="modal-md"><i class="fa-solid fa-user-gear me-1"></i> ตั้งค่า</a>
            </div>
        </div>
        <div class="d-flex flex-column justify-content-center align-items-center">
            <img class="avatar rounded-circle"
                src="data:image/jpg;base64,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"
                width="30" alt="">
            <div class="flex-grow-1 overflow-hidden">
                <h6 class="card-title mb-2 pr-4 text-truncate">
                    <a href="/hr/organization/view?id=4084" class="text-dark">xxx </a>
                </h6>


            </div>
        </div>
        

    </div>


</div>