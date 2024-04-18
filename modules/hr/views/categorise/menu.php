<?php
use yii\helpers\Html;
?>
<div
        class="d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center mb-3">
        <div class="d-flex justify-content-start gap-2">
            <?=app\components\AppHelper::Btn([
                'url' => ['create','name' => $name,'title' => $title],
                'modal' => true,
                'size' => 'lg',
                ])?>
        </div>

        <div class="d-flex gap-2">
            <?=Html::a('<i class="bi bi-house"></i> ย้อนกลับ',['/hr/categorise','title' => 'การตั้งค่าบุคลากร'],['class' => 'btn btn-outline-primary open-modal','data' => ['size' => 'modal-md']])?>
        </div>
    </div>