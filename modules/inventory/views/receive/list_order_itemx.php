<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use app\modules\sm\models\Product;
use yii\widgets\Pjax;
?>


<?php Pjax::begin(['id' => 'inventory','enablePushState' => false]); ?>
<?php

?>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width:500px">
                        <?php echo $this->render('_search_product', ['model' => $searchModel]); ?>
                        </th>
                        <th class="text-center" style="width:80px">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <tr class="">
                        <td class="align-middle"><?php echo $item->Avatar(false);?></td>
                        <td class="align-middle gap-2">
                            <div class="d-flex justify-content-center gap-2">
                            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่ม', ['/inventory/receive/add-item', 'id' => $item->id, 'title' => '<i class="bi bi-ui-checks-grid"></i> เลือกรายการวัสดุเข้าคลัง'], ['class' => 'btn btn-sm btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-md']]) ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
        </div>
    </div>
</div>

<div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
    <?= yii\bootstrap5\LinkPager::widget([
        'pagination' => $dataProvider->pagination,
        'firstPageLabel' => 'หน้าแรก',
        'lastPageLabel' => 'หน้าสุดท้าย',
        'options' => [
            'listOptions' => 'pagination pagination-sm',
            'class' => 'pagination-sm',
        ],
    ]); ?>
</div>

<?php Pjax::End(); ?>