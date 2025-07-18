<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */

$this->title = 'ฟอร์มบันทึกข้อมูลครุภัณฑ์';
$this->params['breadcrumbs'][] = ['label' => 'จัดการทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนทรัพย์สิน', 'url' => ['/am/asset']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('../default/menu',['active' => 'asset'])?>
<?php $this->endBlock(); ?>



    <?= $this->render('_form', [
        'model' => $model,
        
    ]) ?>

