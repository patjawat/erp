

<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use app\models\Categorise;
use app\modules\booking\models\Vehicle;


$this->registerCssFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css');
$this->registerJsFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$this->title = 'ระบบขอใช้ยานพาหนะ/ปฏิทินการใช้รถ';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar-day fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
ปฏิทินการใช้รถยนต์
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?=$this->render('menu',['active' => 'ambulance'])?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('navbar_menu'); ?>
<?php  echo $this->render('@app/modules/me/menu',['active' => 'ambulance']) ?>
<?php $this->endBlock(); ?>


<?=$this->render('@app/modules/booking/views/vehicle/carlendar_item',['vehicle_type' => 'ambulance']);?>