<?php
use yii\helpers\Html;
use yii\widgets\Pjax;
    $this->title = 'หนังสือส่ง';

$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-paper-plane"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/dms/menu',['model' =>$searchModel]) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php  echo $this->render('@app/modules/dms/menu',['model' =>$searchModel,'active' => 'send']) ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'send','timeout' => 80000]); ?>


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
                <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/dms/documents/create','document_group' => $searchModel->document_group,'title' => '<i class="fa-solid fa-circle-plus"></i> '.$this->title], ['class' => 'btn btn-light shadow open-modal', 'data' => ['size' => 'modal-xxl']]) ?>
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

<?php  Pjax::end(); ?>