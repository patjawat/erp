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
$this->title = 'หนังคำสั่ง';

$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-flag fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/dms/menu', ['model' => $searchModel]) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/dms/menu', ['model' => $searchModel, 'active' => 'appointment']) ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'document', 'timeout' => 80000]); ?>

<div class="card">
    <div class="card-body  align-top align-items-center">
        <div class="d-flex justify-content-between align-top align-items-center">
            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่ม' . $this->title, ['/dms/documents/create', 'document_group' => $searchModel->document_group, 'document_type' => $searchModel->document_type, 'title' => '<i class="fa-solid fa-calendar-plus"></i> หนังสือส่ง'], ['class' => 'btn btn-primary open-modal shadow rounded-pill', 'data' => ['size' => 'modal-xxl']]) ?>
            <?= $this->render('@app/modules/dms/views/documents/_search', ['model' => $searchModel]); ?>
 <span class="btn btn-success rounded-pill shadow export-document"><i
                    class="fa-regular fa-file-excel me-1"></i>ส่งออก</span>
        </div>

        <?=$this->render('list_items', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);?>

    </div>
</div>


    <?php Pjax::end(); ?>