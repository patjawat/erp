<?php
use yii;
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\base\ErrorException;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */

$this->title = 'ทะเบียนทรัพย์สิน';
$this->params['breadcrumbs'][] = ['label' => 'Assets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
$title = Yii::$app->request->get('title');
$group = Yii::$app->request->get('group');
?>

<?php $this->beginBlock('page-title'); ?>
<?=$this->title;?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../default/menu')?>
<?php $this->endBlock(); ?>
<style>
.field-asset-q {
    margin-bottom: 0px !important;
}
</style>
<div class="card">
    <div
        class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
        <div class="d-flex justify-content-start">
            <?=app\components\AppHelper::Btn([
                    'title' => '<i class="fa-solid fa-circle-plus"></i> ลงทะเบียน'.$title,
                    'url' =>['select-type','group' => $group,'title' => $title],
                    'modal' =>true,
                    'size' => 'sm',
            ])?>
            <?php if($group):?>
            <?=app\components\AppHelper::Btn([
                    'title' => '<i class="fa-solid fa-circle-plus"></i> ลงทะเบียน'.$title,
                    'url' =>['create','group' => $group,'title' => $title],
                    'model' =>true,
                    'size' => 'lg',
            ])?>
            <?php else:?>
            <!-- <a class="btn btn-outline-warning text-primary" href="#" data-pjax="0" onclick="return alert('กรุณาเลือกประเภททรัพย์สินก่อนสร้างรายการใหม่')"><i class="fa-solid fa-circle-exclamation text-danger"></i> เลือกประเภททรัพย์สินเพื่อสร้างรายการ</a> -->
            <?php endif;?>
        </div>

        <div class="d-flex gap-2">
            <?= $this->render('_search', ['model' => $model]); ?>
            <span class="filter-asset btn btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="top"
                data-bs-custom-class="custom-tooltip" data-bs-title="เลือกเงื่อนไขของการค้นหาเพิ่มเติม...">
                <i class="fa-solid fa-filter"></i>
            </span>
            <?=Html::a('<i class="bi bi-list-ul"></i>',['/am/asset/','view'=> 'list'],['class' => 'btn btn-outline-primary'])?>
            <?=Html::a('<i class="bi bi-grid"></i>',['/am/asset/','view'=> 'grid'],['class' => 'btn btn-outline-primary'])?>
            <?=Html::a('<i class="fa-solid fa-file-import me-1"></i>',['/am/asset/import-csv'],['class' => 'btn btn-outline-primary','title' => 'นำเข้าข้อมูลจากไฟล์ .csv',
            'data' => [
                'bs-placement' => 'top',
                'bs-toggle' => 'tooltip',
                ]])?>
        </div>

    </div>
</div>


<div class="asset-view">
    <?php if($model->asset_group == 1):?>
    <?=$this->render('@app/modules/am/views/asset/asset_detail_group_1',['model' => $model])?>
    <?php endif?>

    <?php if($model->asset_group == 2):?>
    <?=$this->render('@app/modules/am/views/asset/asset_detail_group_2',['model' => $model])?>
    <?php endif?>

    <?php if($model->asset_group == 3):?>
    <?=$this->render('@app/modules/am/views/asset/asset_detail_group_3',['model' => $model])?>

    <?= $model->asset_group == 3 ? $this->render('./asset_detail',['model' => $model]) :  ''?>

    <?php endif?>
</div>