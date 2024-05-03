<?php
use yii;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\base\ErrorException;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */

$this->title = 'ทะเบียนทรัพย์สิน';
$this->params['breadcrumbs'][] = ['label' => 'Assets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
$title = Yii::$app->request->get('title');
$group = Yii::$app->request->get('group');
?>

<?php $this->beginBlock('page-title'); ?>
<?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock(); ?>
<style>
.field-asset-q {
    margin-bottom: 0px !important;
}
</style>

<?php Pjax::begin(['id' => 'view-container','timeout' => 50000 ]); ?>

<div class="asset-view">
    <?php if($model->asset_group == 1):?>
    <?=$this->render('@app/modules/am/views/asset/asset_detail_group_1',['model' => $model])?>
    <?php endif?>

    <?php if($model->asset_group == 2):?>
    <?=$this->render('@app/modules/am/views/asset/asset_detail_group_2',['model' => $model])?>
    <?php endif?>

    <?php if($model->asset_group == 3):?>
    <?=$this->render('@app/modules/am/views/asset/asset_detail_group_3',['model' => $model
   
    ])?>

    <?= $model->asset_group == 3 ? $this->render('./asset_detail',['model' => $model,'searchModel' => $searchModel,
    'dataProvider' => $dataProvider]) :  ''?>

    <?php endif?>
</div>

<?php Pjax::end(); ?>