<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeavePermission $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Leave Permissions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="row d-flex justify-content-center">
<div class="col-6">
    
<div class="card">

    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h4 class="card-title"><?=$model->title?></h4>
            <?=Html::a('สร้างใหม่',['/leave/leave-permission/create'],['class' => 'btn btn-sm btn-primary shadow rounded-pill open-modal','data' => ['size' => 'modal-md']])?>
        </div>
        <p class="card-text">Text</p>
    </div>
</div>

</div>
</div>


