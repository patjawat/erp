<?php
use yii\helpers\Html;
use yii\widgets\Pjax;

?>
<?php Pjax::begin(['id' => 'order_item']); ?>

<div class="card">
    <div class="card-body">


        <div class="table-responsive" style="height:800px">
            <table class="table table-primary">
                <thead>
                    <tr>
                        <th class="fw-semibold">สถานะ</th>
                        <th class="fw-semibold">ผู้ขอซื้อ</th>
                        <th class="fw-semibold">เลขทะเบียนคุม</th>
                        <th class="fw-semibold">เลขที่สั่งซื้อ (PO)</th>
                        <th class="fw-semibold">ผู้จำหน่าย</th>
                        <th class="fw-semibold">ความคืบหน้า</th>
                        <th class="fw-semibold">หมายเหตุ</th>
                        <th class="fw-semibold text-start">วันที่ขอซื้อ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider->getModels() as $model): ?>
                    <tr class="">
                        <td class="fw-light">
                            <div class="btn-group">
                                <?= Html::a('<i class="bi bi-clock"></i> ' . $model->pr_number, ['/purchase/order/view', 'id' => $model->id], ['class' => 'btn btn-light']) ?>
                                <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                    <i class="bi bi-caret-down-fill"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์เอกสาร', ['/sm/order/document', 'id' => $model->id, 'title' => '<i class="fa-solid fa-print"></i> พิมพ์เอกสารประกอบการจัดซื้อ'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                    <li><?= Html::a('<i class="bi bi-bag-plus-fill me-1"></i> สร้างใบสั่งซื้อ', ['/purchase/po-order/create', 'id' => $model->id, 'title' => '<i class="fa-solid fa-print"></i> พิมพ์เอกสารประกอบการจัดซื้อ'], ['class' => 'dropdown-item open-modal-x', 'data' => ['size' => 'modal-md']]) ?>
                                    </li>
                                </ul>
                            </div>

                        </td>
                        <td class="fw-light"> 
                        <?= $model->getUserReq()['avatar'] ?>
                    
                    </td>
                        <td class="fw-light align-middle">
                            <?= Html::a($model->pq_number, ['/purchase/pq-order/view', 'id' => $model->id], ['class' => 'fw-bolder']) ?>
                        </td>
                        <td>-</td>
                        <td class="fw-light align-middle">
                            <?php
                            try {
                                $model->vendor->title;
                            } catch (\Throwable $th) {
                                // throw $th;
                            }
                            ?>

                        </td>

                        <td class="fw-light align-middle">
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar" role="progressbar" aria-label="Progress" style="width: 50%;"
                                    aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </td>
                        <td class="fw-light align-middle"><?php //  $model->data_json['comment'] ?></td>
                        <td class="fw-light align-middle">
                            <?= $model->viewCreatedAt() ?>
                            <div class="text-muted fs-13">4 วันที่แล้ว</div>
                        </td>

                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php Pjax::end() ?>