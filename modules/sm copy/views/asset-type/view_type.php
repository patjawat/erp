<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;
use app\components\SiteHelper;
use app\components\CategoriseHelper;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Fsn $model */

$this->title = '<i class="fa-solid fa-gear"></i> การตั้งค่าครุภัณฑ์';
$this->params['breadcrumbs'][] = ['label' => 'Fsns', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
$category_id = Yii::$app->request->get('category_id');
?>
<?php Pjax::begin(['id' => 'sm-container','timeout' => 5000]); ?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-folder-check"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action');?>
<?= $this->render('../default/menu')?>
<?php $this->endBlock();?>

<div class="card">
    <div
        class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
        <h5><?=$model->title?></h5>
    <div class="d-flex gap-2">
                <?=  Html::a('<i class="fa-solid fa-chevron-left me-1"></i> ย้อนกลับ',['/sm/asset-type'],['class' => 'btn btn-light']) ?>
        </div>

    </div>
</div>

<div class="row">
    <div class="col-12">



        <?=$this->render('show/list', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);?>

    </div>



</div>


<?php Pjax::end(); ?>