<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

?>
<div class="row justify-content-md-center">

    <div class="col-md-auto">
        <h1 class="text-center"><i class="fa-solid fa-user-check"></i> <span class="text-success">ลงทะเบียนสำเร็จ</span>
        </h1>
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
               'username',
               'fullname',
               'email'
            ],
            ]) ?>
<?=Html::a('หน้าหลัก',['/auth/index'],['class' => 'btn btn-block btn-primary','style' => 'margin-right: 5px;'])?> | 
<?=Html::a('Login',['auth/sigin'],['class' => 'btn btn-block btn-success'])?>

    </div>
</div>