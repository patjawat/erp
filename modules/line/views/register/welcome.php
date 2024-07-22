<?php
use yii\helpers\Html;
?>

<div class="d-flex justify-content-center mt-5">
    <h4 class="text-center mb-3 text-primary"><?=$this->title?></h4>

    <div class="line-profile">
        <div class="row d-flex justify-content-center align-items-center">
            <div class="col-8">
                <?=Html::img('@web/images/welcome.png',['class' => 'w-100'])?>
            </div>
        </div>
        <div class="text-center">
            <h2 class="mt-3" id="displayName">ยินดีด้วยคุณ</h2>
            <h4 class="my-3 " id="displayName">คุณลงทะเบียนสำเร็จ !</h4>
            <div class="d-grid gap-2 p-5">
                <button class="btn btn-primary rounded-pill shadow d-flex justify-content-center align-items-center gap-2 fs-2"><i class="bi bi-check-circle"></i> ตกลง</button>
            </div>
        </div>
    </div>
</div>