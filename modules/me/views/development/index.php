<?php
use yii\helpers\Html;
use app\components\UserHelper;
/** @var yii\web\View $this */
$this->title = 'อบรม/ประชุม/ดูงาน';
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-briefcase fs-1 text-white"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
ทะเบียน<?=$this->title?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?=$this->render('menu')?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
        <h6><i class="bi bi-ui-checks"></i> <?=$this->title?></h6>
        <?=Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่ม'.$this->title,['/me/development/create','title' => '<i class="bi bi-mortarboard-fill me-2"></i>แบบฟอร์มบันทึกข้อมูลการพัฒนาบุคลากร'],['class' => 'btn btn-primary rounded-pill shadow open-modal-x','data' => ['size' => 'modal-xl']])?>
    </div>
           <?=$this->render('_search', ['model' => $searchModel,'type' => 'development'])?>
<div class="mb-5">
    <?=$this->render('@app/modules/hr/views/development/list',[
        'dataProvider' => $dataProvider,
        'searchModel' => $searchModel,
        ])?>
</div>
    </div>
</div>