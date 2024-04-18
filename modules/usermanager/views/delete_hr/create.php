<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\usermanager\models\User */

$this->title = 'เพิ่มข้อมูลแพทย์ใหม่';
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
