<?php

use app\modules\am\models\Fsn;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\am\models\FsnSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ประเภทครุภัณฑ์';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-action');?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock();?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'am-container', 'enablePushState' => true, 'timeout' => 5000]);?>

<div class="card">
    <div
        class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
        <div class="d-flex justify-content-start">
            <?=app\components\AppHelper::Btn([
                    'title' => '<i class="fa-solid fa-circle-plus"></i> สร้างใหม่',
                    'url' =>['/am/fsn/create'],
                    'modal' => true,
                    'size' => 'lg',
            ])?>
        </div>

        <div class="d-flex gap-2">
            <?=Html::a('<i class="bi bi-list-ul"></i>',['index','view'=> 'list'],['class' => 'btn btn-outline-primary'])?>
            <?=Html::a('<i class="bi bi-grid"></i>',['index','view'=> 'grid'],['class' => 'btn btn-outline-primary'])?>
            <?=Html::a('<i class="fa-solid fa-gear"></i>', ['/am/fsn/group-setting'], ['class' => 'btn btn-outline-primary open-modal', 'data' => ['size' => 'modal-lg']])?>
        </div>

    </div>
</div>

<?php if(app\components\SiteHelper::getDisplay() == 'list'):?>
<?=$this->render('show/list', [
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
]);?>

<?php else:?>
<?=$this->render('show/grid', [
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
]);?>
<?php endif;?>


<?php Pjax::end(); ?>