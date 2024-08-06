<?php
use yii\helpers\Html;
?>
<?php  yii\widgets\Pjax::begin(['id' => $container,'enablePushState' => false,'timeout' => 88888 ]); ?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><?=$title?>
            <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span>
            รายการ</h6>
        </div>

        <table class="table table-primary">
            <thead>
                <tr>
                    <th class="fw-semibold" style="width:280px">ผู้ขอซื้อ</th>
                    <th class="fw-semibold">ประเภท/มูลค่า</th>
                    <th class="fw-semibold" style="width: 200px;">สถานะ</th>
                    <th class="fw-semibold" style="width: 100px;">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataProvider->getModels() as $model): ?>
                <tr class="">
                    <td class="fw-light"> <?= $model->getUserReq()['avatar'] ?></td>
                    <td class="fw-light align-middle">
                        <div class="d-felx flex-column">
                            <div class="text-primary mb-0 fs-15">
                                <?=isset($model->data_json['order_type_name']) ? $model->data_json['order_type_name'] : ''?>
                            </div>
                            <div class="fw-semibold ">
                                <i class="fa-solid fa-tag"></i>
                                <?= number_format($model->calculateVAT()['priceAfterVAT'],2)?>
                            </div>
                        </div>
                    </td>
                    <td class="fw-light align-middle"><?=$model->showChecker()['leader']?></td>
                    <td class="fw-light">
                        <div class="btn-group">
                            <?= Html::a('<i class="bi bi-clock"></i>', ['/purchase/order/view', 'id' => $model->id], ['class' => 'btn btn-light w-100']) ?>
                            <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                <i class="bi bi-caret-down-fill"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <?php if ($model->status == 3): ?>
                                <li><?= Html::a('<i class="bi bi-bag-plus-fill me-1"></i> ลงทะเบีนยคุม', ['/purchase/po-order/create', 'id' => $model->id, 'title' => '<i class="fa-solid fa-print"></i> ลงทะเบีนยคุม'], ['class' => 'dropdown-item open-modal-x', 'data' => ['size' => 'modal-md']]) ?>
                                    <?php endif;?>
                                <li><?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์เอกสาร', ['/sm/order/document', 'id' => $model->id, 'title' => '<i class="fa-solid fa-print"></i> พิมพ์เอกสารประกอบการจัดซื้อ'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]) ?>
                                <li><?= Html::a('<i class="bi bi-bag-plus-fill me-1"></i> สร้างใบสั่งซื้อ', ['/purchase/po-order/create', 'id' => $model->id, 'title' => '<i class="fa-solid fa-print"></i> พิมพ์เอกสารประกอบการจัดซื้อ'], ['class' => 'dropdown-item open-modal-x', 'data' => ['size' => 'modal-md']]) ?>
                                <li> <?=Html::a('<i class="fa-solid fa-circle-plus text-white"></i> ตรวจรับ',['/purchase/gr-order/update','id' => $model->id,'title' => '<i class="fa-solid fa-circle-plus text-primary"></i> สร้างใบตรวจรับ'],['class' => 'open-modal dropdown-item','data' => ['size' => 'modal-xl']])?>
                                </li>
                                </li>
                            </ul>
                        </div>

                    </td>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="iq-card-footer text-muted d-flex justify-content-between mt-4">
            <?= yii\bootstrap5\LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'firstPageLabel' => 'หน้าแรก',
                    'lastPageLabel' => 'หน้าสุดท้าย',
                    'options' => [
                        'class' => 'pagination pagination-sm',
                    ],
                ]); ?>
                <div>
                <?=Html::a('แสดงทั้งหมด',['/purchase/'.($container == 'pr-accept-order' ? 'pr-order' : $container )],['data' => ['pjax' => 0]])?>
            </div>
        </div>
    </div>
</div>
<?php  yii\widgets\Pjax::end()?>