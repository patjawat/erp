<?php
use yii\helpers\Html;
use kartik\tree\TreeView;
use kartik\tree\TreeViewInput;
use yii\widgets\Pjax;


// use app\models\Product;
use app\modules\hr\models\Organization;
$this->title = "ระบบจัดการมาตรฐานกำหนดตำแหน่ง";

$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-diagram-3"></i> <?=$this->title;?>  
<?php $this->endBlock(); ?>
<style>
    .kv-tree-root.kv-root-heading {
    font-weight: 300;
}
.kv-detail-crumbs .kv-crumb-active {
    font-weight: 300;
}
.kv-detail-container {
    border-radius: 9px;
    background-color: white;
}
</style>
<?php Pjax::begin(['id' => 'hr-container','enablePushState' => true,'timeout' => 50000 ]); ?>

<div class="card">
    <div
        class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
        <div class="d-flex justify-content-start gap-2">
            <?=app\components\AppHelper::Btn([
                'url' => ['create'],
                'modal' => true,
                'size' => 'lg',
                ])?>
        </div>

        <div class="d-flex gap-2">

            <?php //  $this->render('_search', ['model' => $searchModel]); ?>

            <?=Html::a('<i class="bi bi-list-ul"></i>',['/hr/employees/index','view'=> 'list'],['class' => 'btn btn-outline-primary'])?>
            <?=Html::a('<i class="bi bi-grid"></i>',['/hr/employees/index','view'=> 'grid'],['class' => 'btn btn-outline-primary'])?>
            <?=Html::a('<i class="fa-solid fa-gear"></i>',['/hr/categorise','title' => 'การตั้งค่าบุคลากร'],['class' => 'btn btn-outline-primary open-modal','data' => ['size' => 'modal-md']])?>

        </div>

    </div>
</div>



<?php
$js = <<< JS


JS;
$this->registerJS($js);
?>
<?php Pjax::end();?>