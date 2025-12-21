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
$this->title = 'หนังประกาศ/นโยบาย ';

$this->params['breadcrumbs'][] = ['label' => 'งานสารบรรณ', 'url' => ['/dms/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
 <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-megaphone-icon lucide-megaphone">
            <path d="M11 6a13 13 0 0 0 8.4-2.8A1 1 0 0 1 21 4v12a1 1 0 0 1-1.6.8A13 13 0 0 0 11 14H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z" />
            <path d="M6 14a12 12 0 0 0 2.4 7.2 2 2 0 0 0 3.2-2.4A8 8 0 0 1 10 14" />
            <path d="M8 6v8" />
        </svg>
        <?= $this->title; ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/dms/menu', ['model' => $searchModel, 'active' => $searchModel->document_group]) ?>
<?php $this->endBlock(); ?>


<?php Pjax::begin(['id' => 'document','timeout' => 80000]); ?>


<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?= $this->render('@app/modules/dms/views/documents/_search', ['model' => $searchModel]); ?>
    </div>
</div>


<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white"> <i class="bi bi-ui-checks"></i> ทะเบียน<?php echo $this->title?>
                <span class="badge text-bg-light"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span>
                รายการ
            </h6>
            <div class="d-flex gap-3">
                 <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/dms/documents/create','document_group' => $searchModel->document_group,'document_type' => $searchModel->document_type, 'title' => '<i class="fa-solid fa-calendar-plus"></i> หนังสือส่ง'], ['class' => 'btn btn-light open-modal shadow', 'data' => ['size' => 'modal-xxl']]) ?>
            <span class="btn btn-success shadow export-document"><i
                    class="fa-regular fa-file-excel me-1"></i>ส่งออก</span>
            </div>
        </div>
    </div>
    <div class="card-body">
<?=$this->render('list_items', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);?>
    </div>
</div>

<?php Pjax::end(); ?>