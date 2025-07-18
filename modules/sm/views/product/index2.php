<?php

use app\modules\sm\models\Product;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'Products';
$this->params['breadcrumbs'][] = $this->title;
?>
       <?php Pjax::begin(['id' => 'sm-container']); ?>
<div class="row">
    <div class="col-3">
        <div class="card" style="height: 1400px;">
            <div class="card-body ">
                <h4 class="card-title"><i class="bi bi-grid"></i> หมวดหมู่</h4>
                <?= $this->render('_search_left', ['model' => $searchModel]) ?>
            </div>
        </div>
    </div>
    <div class="col-9">
        <div class="card">
            <div class="card-body d-flex justify-content-between">
                <div>
                    <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/sm/product/create', 'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> เพิ่มวัสดุใหม่'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                </div>
                <?php echo $this->render('_search', ['model' => $searchModel]); ?>
            </div>
        </div>
        <div class="row">
            <?php foreach ($dataProvider->getModels() as $model): ?>
                <div class="col-lg-3 col-md-3 col-sm-6">
                <div class="card">
                    <!-- <img class="card-img-top" src="https://angular.spruko.com/vexel/preview/assets/images/shop/1.png" alt="Title" /> -->
                        <?= Html::img($model->ShowImg(), ['class' => ' card-img-top ', 'style' => 'max-width:100%;height:280px;max-height: 280px;']) ?>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex flex-column">
                                <span class="badge text-bg-primary "><?= $model->productType->title ?></span>
                            </div>
                            <div class="dropdown float-end">
                                <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-ellipsis"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข', ['/sm/product/update', 'id' => $model->id,'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไขเพิ่มสินค้า/บริการ'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                <?= Html::a('<i class="fa-solid fa-trash"></i> ลบ', ['/sm/asset-type/delete', 'id' => $model->id], [
                                    'class' => 'dropdown-item  delete-item',
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <?= Html::a($model->title, ['view', 'id' => $model->id], ['class' => 'h5 text-truncate']) ?>
                        <span class=""><?= isset($model->data_json['unit']) ? $model->data_json['unit'] : '-' ?></span>
                        </div>
                        <!-- <span>คงเหลือ 10 ชิ้น</span> -->

                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php Pjax::end(); ?>