<?php

/**
 * @var yii\web\View $this
 */

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'ตั้งค่าระบบ';
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('page-title'); ?>
<?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
ยินดีต้อนรับ โปรแกรมบริหารองค์กร
<?php $this->endBlock(); ?>


<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/settings/views/menu',['active' => 'color']) ?>
<?php $this->endBlock(); ?>


<style>
.menu-box>li {
    width: 150px;
}

.menu-box>li :hover {
    background-color: #d8dff2;
    border-radius: 10px;
}
</style>

<?= $this->render('color') ?>
