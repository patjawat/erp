<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use app\components\AppHelper;
use app\components\SiteHelper;
use app\modules\am\models\Asset;

$this->title = 'ทะเบียนที่ดิน';
$this->params['breadcrumbs'][] = ['label' => 'ทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check fs-1"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('../default/menu',['active' => 'asset'])?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <?= Html::a('<i class="fa-solid fa-circle-plus"></i> ลงทะเบียนคุภัณฑ์', ['/am/land/create'], ['class' => 'btn btn-primary rounded-pill shadow']) ?>
                <?= $this->render('_search', ['model' => $searchModel]); ?>
            </div>
    </div>
</div>

  <?php if(SiteHelper::getDisplay() == 'list'):?>

        <?=$this->render('@app/modules/am/views/asset/show/list', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);?>

        <?php else:?>
        <hr>
        <?=$this->render('@app/modules/am/views/asset/show/grid', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);?>

        <?php endif?>
