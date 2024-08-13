<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;
use app\components\SiteHelper;
use app\components\CategoriseHelper;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Fsn $model */
$title =  Yii::$app->request->get('title');
$this->title =  $title;
$this->params['breadcrumbs'][] = ['label' => 'Fsns', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<?php $this->beginBlock('page-title');?>
<i class="bi bi-folder-check"></i> ตั้งค่าทรัพย์สิน
<?php $this->endBlock();?>

<?php Pjax::begin(['id' => 'am-container', 'enablePushState' => true, 'timeout' => 5000]);?>
<?php // $model->name?>

<div class="card">
    <div class="card-body d-flex flex-lg-row flex-md-row flex-sm-column flex-sx-column justify-content-lg-between justify-content-md-between justify-content-sm-center">
            <h5><?=$title?></h5>
        <div class="d-flex gap-2">
            <div class="btn-group">
                <?=Html::a('<i class="fa-solid fa-box-open me-1"></i> หมวดหมู่ทรัพย์สิน',['/am/asset-item/setting'],['class' => 'btn btn-light'])?>
                <button type="button" class="btn btn-warning dropdown-toggle dropdown-toggle-split"
                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                    <i class="bi bi-caret-down-fill"></i>
                </button>
                <ul class="dropdown-menu">
                    <?php foreach(CategoriseHelper::Categorise('asset_group') as $assetGroup):?>
                    <li><?=Html::a('<i class="bi bi-check2-circle me-1 text-primary"></i>'.$assetGroup->title,['/am/asset-item/view','id' => $assetGroup->id,'title' => 'หมวด'.$assetGroup->title], ['class' => 'dropdown-item'])?>
                    </li>
                    <?php endforeach;?>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    </li>
                    <li><?=Html::a('<i class="fa-solid fa-gear me-2"></i> ตั้งค่าทรัพย์สิน',['/am/asset-item/setting','name' => 'asset_group'],['class' => ' dropdown-item'])?>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</div>

<div class="row">
    <div class="col-9">
<div class="card mb-0">
    <div class="card-body">

    <div class="d-flex justify-content-start">
            <?=app\components\AppHelper::Btn([
                    'title' => '<i class="fa-solid fa-circle-plus"></i> สร้าง'.$model->title,
                    'url' =>['/am/asset-item/create','type_code'=>$model->code,'name' => 'asset_item','code' => $model->code,'title' => 'สร้าง'.$model->title,'id'=>Yii::$app->getRequest()->getQueryParam('id')],
                    'modal' => true,
                    'size' => 'lg',
            ])?>
        </div>
    </div>
</div>


        <?=$this->render('show/list', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);?>

    </div>

    <div class="col-3">

        <div class="card mb-0">
            <div class="card-body">
                <?=app\components\AppHelper::Btn([
                    'title' => '<i class="fa-solid fa-circle-plus"></i> สร้างประเภท',
                    'url' =>['/am/asset-item/create','name' => 'asset_type','category_id'  => $model->code ],
                    'modal' => true,
                    'size' => 'lg'])?>
            </div>
        </div>


        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <tbody>
                    <?php foreach($dataProviderGroup->getModels() as $assetType):?>
                    <tr class="">
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <?php if($assetType->code == $model->code):?>
                                    <i class="fa-regular fa-folder-open fs-2 text-primary"></i>
                                    <?php else:?>
                                    <i class="bi bi-folder-check fs-2"></i>
                                    <?php endif;?>
                                </div>
                                <div class="flex-grow-1 ms-3">

                                    <?=Html::a($assetType->title,['/am/asset-item/view-type','id' => $assetType->id,'name' => $assetType->name,'title' => $assetType->title])?><br>
                                    อายุการใช้งาน
                                    <label class="badge rounded-pill text-primary-emphasis bg-success-subtle me-1">
                                        <?=$assetType->code?>
                                    </label>
                                    ปี | ค่่าสื่อม
                                    <label class="badge rounded-pill text-primary-emphasis bg-danger-subtle me-1">
                                        <?=$assetType->code?>
                                    </label>
                                </div>
                            </div>
                        </td>
                        <td style="width: 90px;">
                            <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $assetType->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-sm btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                            <?= Html::a('<i class="fa-regular fa-trash-can"></i>', ['delete', 'id' => $assetType->id], [
                                'class' => 'btn btn-sm btn-danger',
                                'data' => [
                                    'confirm' => 'Are you sure you want to delete this item?',
                                    'method' => 'post',
                                ],
                            ]) ?>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>
    </div>

</div>





<?=DetailView::widget([
    'model' => $model,
    'attributes' => [
        [
            'label' => 'ชื่อรายการ',
            'value' => function ($model) {
                return $model->title;
            },
        ],
        [
            'label' => 'รหัส',
            'value' => function ($model) {
                return $model->code;
            },
        ],
    ],
])?>
<?=Html::img($model->showImg(), ['class' => '', 'style' => 'max-width:100%'])?>
<hr>
<div class="d-flex gap-2  justify-content-center">
    <?=Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>แก้ไข', ['/am/asset-item/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-warning  open-modal', 'data' => ['size' => 'modal-md']])?>
    <?=Html::a('<i class="fa-solid fa-trash me-1"></i>ลบ', ['/am/asset-item/delete', 'id' => $model->id], [
    'class' => 'btn btn-danger  delete-item',
])?>
</div>

<?php Pjax::end();?>