<?php
use yii\helpers\Html;
use app\components\UserHelper;
/** @var yii\web\View $this */
$this->title = 'อบรม/ประชุม/ดูงาน';
$this->params['breadcrumbs'][] = ['label' => 'บริการ', 'url' => ['/me']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-2  text-primary-gradient">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-check-icon lucide-clipboard-check"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/></svg>
        <?= $this->title?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex gap-2">
    <?= $this->render('@app/components/ui/btnReturn')?>
</div>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('navbar_menu'); ?>
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
                <?=Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่',['/me/development/create','title' => '<i class="bi bi-mortarboard-fill me-2"></i>แบบฟอร์มบันทึกข้อมูลการพัฒนาบุคลากร'],['class' => 'btn btn-light shadow open-modal-x','data' => ['size' => 'modal-xl']])?>
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