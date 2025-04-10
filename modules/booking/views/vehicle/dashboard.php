<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\Pjax;
use yii\db\Expression;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\components\ThaiDateHelper;
use app\modules\booking\models\Vehicle;
/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ERP - ระบบจัดการรถยนต์';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<!-- <i class="bi bi-ui-checks"></i>-->
<i class="fa-solid fa-car fs-x1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
Dashboard
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('menu')?>
<?php $this->endBlock(); ?>




<div class="d-flex justify-content-between">
    </div>
    
    <div class="row">
        <div class="col-6">
           <?= $this->render('chart_general_type',['searchModel' => $searchModel])?>
           <?= $this->render('chart_department',['searchModel' => $searchModel])?>
           
        </div>
        <div class="col-6">
            <?=  $this->render('chart_ambulance_type',['searchModel' => $searchModel])?>
            <?= $this->render('chart_car',['searchModel' => $searchModel])?>
        </div>
    </div>

