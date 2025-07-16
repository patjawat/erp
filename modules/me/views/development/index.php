<?php
use yii\helpers\Html;
use app\components\UserHelper;
/** @var yii\web\View $this */
$this->title = 'อบรม/ประชุม/ดูงาน';
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-briefcase fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
ทะเบียน<?=$this->title?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?=$this->render('menu')?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('navbar_menu'); ?>
<?php  echo $this->render('@app/modules/me//menu',['active' => 'development']) ?>
<?php $this->endBlock(); ?>


<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?=$this->render('_search', ['model' => $searchModel,'type' => 'development'])?>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">

    <div class="d-flex justify-content-between">
                <h6 class="text-white">
                    <i class="bi bi-ui-checks"></i> ทะเบียน<?=$this->title?>
                    <span
                        class="badge bg-light"><?php echo number_format($dataProvider->getTotalCount(), 0) ?></span>
                    รายการ
                </h6>
                <?=Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่',['/me/development/create','title' => '<i class="bi bi-mortarboard-fill me-2"></i>แบบฟอร์มบันทึกข้อมูลการพัฒนาบุคลากร'],['class' => 'btn btn-light rounded-pill shadow open-modal-x','data' => ['size' => 'modal-xl']])?>
            </div>
    </div>
    <div class="card-body">
        <div class="mb-5">
            <?=$this->render('@app/modules/hr/views/development/list',[
        'dataProvider' => $dataProvider,
        'searchModel' => $searchModel,
        ])?>
        </div>
    </div>
</div>