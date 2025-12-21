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

$this->params['breadcrumbs'][] = ['label' => 'งานสารบรรณ', 'url' => ['/dms/dashboard']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-signature-icon lucide-signature">
            <path d="m21 17-2.156-1.868A.5.5 0 0 0 18 15.5v.5a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1c0-2.545-3.991-3.97-8.5-4a1 1 0 0 0 0 5c4.153 0 4.745-11.295 5.708-13.5a2.5 2.5 0 1 1 3.31 3.284" />
            <path d="M3 21h18" />
        </svg>
        <?= $this->title; ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/dms/menu', ['model' => $searchModel, 'active' => $searchModel->document_group]) ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'document', 'timeout' => 80000]); ?>


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
            <h6 class="text-white"> <i class="bi bi-ui-checks"></i> ทะเบียน<?php echo $this->title ?>
                <span class="badge text-bg-light"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span>
                รายการ
            </h6>
            <div class="d-flex gap-3">
                <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/dms/documents/create', 'document_group' => $searchModel->document_group, 'document_type' => $searchModel->document_type, 'title' => '<i class="fa-solid fa-calendar-plus"></i> หนังสือส่ง'], ['class' => 'btn btn-light open-modal shadow', 'data' => ['size' => 'modal-xxl']]) ?>
                <span class="btn btn-success shadow export-document"><i
                        class="fa-regular fa-file-excel me-1"></i>ส่งออก</span>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?= $this->render('list_items', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]); ?>

    </div>
</div>


<?php Pjax::end(); ?>