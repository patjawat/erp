<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\components\AppHelper;
use app\modules\dms\models\Documents;
/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = $title;

$this->params['breadcrumbs'][] = $this->title;

$dataFile = Yii::getAlias('@app/doc_receive/data.json');
$jsonCount = 0;
if (file_exists($dataFile)) {
    $jsonData = file_get_contents($dataFile);
    $jsonArray = json_decode($jsonData, true);
    if (is_array($jsonArray)) {
        $jsonCount = count($jsonArray);
    }
}
?>
<?php $this->beginBlock('page-title'); ?>
<?php if($searchModel->document_group == 'receive'):?>
<i class="fa-solid fa-download"></i></i> <?= $this->title; ?>
<?php endif; ?>
<?php if($searchModel->document_group == 'send'):?>
<i class="fa-solid fa-paper-plane text-danger"></i></i><?= $this->title; ?>
<?php endif; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/dms/menu',['model' =>$searchModel]) ?>
<?php $this->endBlock(); ?>

<?php if($jsonCount > 0):?>
<?php $this->beginBlock('action'); ?>
<?= Html::a('<i class="fa-regular fa-circle-down"></i> หนังสือรอรับ <span class="badge text-bg-primary">'.$jsonCount.'</span>', ['/dms/doc-receive'], ['class' => 'btn btn-primary shadow rounded-pill', 'class' => 'btn btn-warning shadow rounded-pill animate__animated animate__headShake animate__infinite']);?>
<?php $this->endBlock(); ?>
<?php endif;?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php  echo $this->render('@app/modules/dms/menu',['model' =>$searchModel,'active' => $action]) ?>
<?php $this->endBlock(); ?>

<?php  Pjax::begin(['id' => 'document','timeout' => 80000]); ?>

<div class="card">
    <div class="card-body d-flex justify-content-between align-top align-items-center">
        <?= Html::a('<i class="fa-solid fa-circle-plus"></i> ออกเลข'.$this->title, ['/dms/documents/create','document_group' => $searchModel->document_group], ['class' => 'btn btn-primary shadow rounded-pill open-modal', 'data' => ['size' => 'modal-xxl']]) ?>
        <?= $this->render('@app/modules/dms/views/documents/_search', ['model' => $searchModel]); ?>
            <span class="btn btn-success rounded-pill shadow export-document"><i
                    class="fa-regular fa-file-excel me-1"></i>ส่งออก</span>


    </div>
</div>
<?=$this->render('list_items', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);?>
<?php  Pjax::end(); ?>

