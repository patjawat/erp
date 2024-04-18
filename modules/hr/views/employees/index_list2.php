<?php
use yii\helpers\Html;
?>

<div class="card">
    <div class="card-body">
        filter
    </div>
</div>






hr









<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-3"><h6>ชื่อ-นามสกุล</h6></div>
            <div class="col-3">	<h6>ประเภท</h6></div>
            <div class="col-3"></div>
            <div class="col-3"></div>
        </div>


    </div>
</div>

<?php foreach($dataProvider->getModels() as $model):?>

<div class="card">
    <div class="card-body p-2">
        <div class="row">
            <div class="col-3">
            <?=$model->getAvatar()?>


            </div>
            <div class="col-3"><?=isset($model->positionType->title) ? ($model->positionType->title) : '-'?></div>
            <div class="col-3"></div>
            <div class="col-3"></div>
        </div>


    </div>
</div>

<?php endforeach?>