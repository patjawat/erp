<?php
use yii\helpers\Html;
use yii\widgets\Pjax;
    $this->title = 'หนังสือส่ง';

$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-download"></i></i> <?= $this->title; ?>
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
    <div class="card-body d-flex justify-content-between align-top align-items-center">
        <?= Html::a('<i class="fa-solid fa-circle-plus"></i> ออกเลข'.$this->title, ['/dms/documents/create','document_group' => $searchModel->document_group, 'title' => '<i class="fa-solid fa-calendar-plus"></i> หนังสือส่ง'], ['class' => 'btn btn-primary shadow rounded-pill open-modal', 'data' => ['size' => 'modal-xxl']]) ?>
        <?php  echo $this->render('@app/modules/dms/views/documents/_search', ['model' => $searchModel]); ?>
       <span class="btn btn-success rounded-pill shadow export-document"><i
                    class="fa-regular fa-file-excel me-1"></i>ส่งออก</span>
    </div>
</div>


<?=$this->render('list_items', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);?>

<?php  Pjax::end(); ?>