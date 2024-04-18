<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\usermanager\models\User */

$this->title = $model->fullname;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="user-view">
<div>
 <?= Html::a('<i class="fas fa-user-plus"></i> เพิ่มใหม่', ['create'], ['class' => 'btn btn-primary']) ?>
</div>
<br>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'username',
            'fullname',
            'doctor_id',
            'email',
        ],
    ]) ?>

</div>
