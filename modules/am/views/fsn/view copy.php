<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;
use app\components\SiteHelper;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Fsn $model */

$this->title = 'การให้หมายเลข FSN';
$this->params['breadcrumbs'][] = ['label' => 'Fsns', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<?php Pjax::begin(['id' => 'am-container','timeout' => 5000]); ?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action');?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock();?>
<?php Pjax::begin(['id' => 'am-container', 'enablePushState' => true, 'timeout' => 5000]);?>


<div class="card">
    <div
        class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
        <div class="d-flex justify-content-start">
            <?=app\components\AppHelper::Btn([
                    'title' => '<i class="fa-solid fa-circle-plus"></i> สร้างใหม่',
                    'url' =>['/am/fsn/create2','type_code'=>$model->code,'id'=>Yii::$app->getRequest()->getQueryParam('id')],
                    'modal' => true,
                    'size' => 'lg',
            ])?>
        </div>

        <div class="d-flex gap-2">
            <?=Html::a('<i class="bi bi-list-ul"></i>',['view','id' => $model->id,'view'=> 'list'],['class' => 'btn btn-outline-primary'])?>
            <?=Html::a('<i class="bi bi-grid"></i>',['view','id' => $model->id,'view'=> 'grid'],['class' => 'btn btn-outline-primary'])?>
            <?=Html::a('<i class="fa-solid fa-gear"></i>', ['/am/fsn/group-setting'], ['class' => 'btn btn-outline-primary open-modal', 'data' => ['size' => 'modal-lg']])?>
        </div>

    </div>
</div>

<?php if(SiteHelper::getDisplay() == 'list'):?>
<?=$this->render('show_group/list', [
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
]);?>

<?php else:?>
<?=$this->render('show_group/grid', [
    'searchModel' => $searchModel,
    'dataProvider' => $dataProvider,
]);?>

<?php endif?>


<div class="fsn-view">
    <?php if($btn):?>
    <div class="card">
        <div class="card-body d-flex justify-content-between">
            <h4 class="card-title"><?= Html::encode($model->title) ?></h4>
            <div>
                <?= Html::a('ย้อนกลับ', ['index'], ['class' => 'btn btn-primary']) ?>
                <?=Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/am/fsn/create2','type_code'=>$model->code,'id'=>Yii::$app->getRequest()->getQueryParam('id')], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']])?>
            </div>
        </div>
    </div>
    <?php endif;?>
    <div class="container-fluid bg-trasparent my-4 p-3" style="position: relative">
        <div class="row row-cols-1 row-cols-xs-2 row-cols-sm-2 row-cols-lg-5 g-3">
            <?php foreach ($small_model as $fsnGroup): ?>
            <div class="col hp">
                <div class="card h-100 shadow-sm">
                    <?= Html::a(Html::img($searchModel->showLogoImg($fsnGroup->ref),['class' => 'card-img-top p-3']), ['view', 'id' => $fsnGroup->id]) ?>


                    <div class="card-body">
                        <div class="clearfix mb-3">
                            <span class="float-start badge rounded-pill bg-success">หมวด <?=($fsnGroup->code)?></span>

                            <span class="float-end"><a href="#" class="small text-muted text-uppercase aff-link">10
                                    รายการ</a></span>
                        </div>
                        <h5 class="card-title">
                            <?=Html::a($fsnGroup->title,['/am/fsn'])?>
                        </h5>

                        <div class="d-grid gap-2 my-4">

                            <div class="btn-group">
                                <?=Html::a('แก้ไขชนิดครุภัณฑ์',['/am/fsn/update2', 'id' => $fsnGroup->id], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']])?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach;?>
        </div>
    </div>
</div>
</div>


<?php Pjax::end(); ?>