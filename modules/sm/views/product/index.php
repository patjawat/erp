<?php

use app\modules\sm\models\Product;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\bootstrap5\LinkPager;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'Products';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php Pjax::begin(['id' => 'sm-container', 'timeout' => 3000]); ?>
<style>
.custom-table {
    border-collapse: separate;
    border-spacing: 0;
}

.custom-table th:first-child,
.custom-table td:first-child {
    border-top-left-radius: .5rem;
    border-bottom-left-radius: .5rem;
    border-top-right-radius: .5rem;
    border-bottom-right-radius: .5rem;
}
</style>
<div class="row">
    <div class="col-xl-2 col-lg-3 col-md-3 col-sm-12">
        <div class="card" style="height:900px;">
            <div class="card-body ">
                <div class="d-flex justify-content-between">
                    <h4 class="card-title"><i class="bi bi-grid"></i> หมวดหมู่</h4>
                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" style="">
                            <?= Html::a('<i class="fa-solid fa-circle-plus me-1"></i> สร้างใหม่', ['update', 'id' => 1, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                            <?= Html::a('<i class="fa-regular fa-eye me-1 text-primary"></i> แสดง', ['update', 'id' => 1, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                        </div>
                    </div>
                </div>
                <?= $this->render('_search_left', ['model' => $searchModel]) ?>
            </div>
        </div>
    </div>
    <div class="col-10">
        <div class="card">
            <div class="card-body d-flex justify-content-between">
                <div>
                    <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/sm/product/create', 'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> เพิ่มวัสดุใหม่'], ['class' => 'btn btn-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                </div>
                <?php echo $this->render('_search', ['model' => $searchModel]); ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">



                <div class="table-responsive">
                    <table class="table table-striped custom-table">
                        <thead>
                        <th style="width:500px">รายการ</th>
                        <th class="text-center" style="width:80px">หน่วย</th>
                        <th class="text-end">ราคาต่อหน่วย</th>
                        <th class="text-center" style="width:80px">จำนวน</th>
                        <th class="text-end">จำนวนเงิน</th>
                        <th scope="col" style="width: 100px;">ดำเนินการ</th>
                        </thead>
                        <tbody>
                            <?php foreach ($dataProvider->getModels() as $model): ?>
                            <tr class="rounded">
                                <td scope="row">
                                    <?=$model->Avatar()?>
                                </td>
                                <td><?=(isset($model->data_json['unit']) ? $model->data_json['unit'] : '-')?></td>
                                <td>00.00</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center">
                    <div class="text-muted">
                        <?= LinkPager::widget([
                            'pagination' => $dataProvider->pagination,
                            'firstPageLabel' => 'หน้าแรก',
                            'lastPageLabel' => 'หน้าสุดท้าย',
                            'options' => [
                                'listOptions' => 'pagination pagination-sm',
                                'class' => 'pagination-sm',
                            ],
                        ]); ?>
                    </div>
                </div>


            </div>
        </div>


    </div>
</div>
<?php Pjax::end(); ?>