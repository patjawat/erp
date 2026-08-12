<?php
use yii\helpers\Html;
?>
<?php  yii\widgets\Pjax::begin(['id' => $container,'enablePushState' => false,'timeout' => 88888 ]); ?>

<div class="card" <?=($container == 'pq-order' ? 'style="height: 1018px;"' : null)?>>
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> <?=$title?>
            <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span>
            รายการ</h6>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th style="width:280px">ผู้ขอซื้อ</th>
                    <th>ประเภท/มูลค่า</th>
                    <th style="width: 200px;">สถานะ</th>
                    <th style="width: 100px;">ดำเนินการ</th>
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
                                <i class="bi bi-tag"></i>
                                <?= number_format($model->calculateVAT()['priceAfterVAT'],2)?>
                            </div>
                        </div>
                    </td>
                    <td class="fw-light align-middle"><?=$model->showChecker()['leader']?></td>
                    <td class="fw-light">
                        <div class="btn-group">
                            <?= Html::a('<i class="bi bi-pencil-square"></i>', ['/purchase/order/view', 'id' => $model->id], [
                                'class' => 'btn btn-outline-primary w-100',
                                'title' => 'ดำเนินการ ' . ($model->pr_number ?: ''),
                                'aria-label' => 'ดำเนินการ ' . ($model->pr_number ?: ''),
                            ]) ?>
                            <button type="button" class="btn btn-outline-primary dropdown-toggle dropdown-toggle-split"
                                data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent"
                                aria-label="เมนูเพิ่มเติม">
                                <i class="bi bi-caret-down-fill"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <?php if ($model->status == 3): ?>
                                <li><?= Html::a('<i class="bi bi-bag-plus-fill me-1"></i> ลงทะเบียนคุม', ['/purchase/po-order/create', 'id' => $model->id, 'title' => '<i class="bi bi-printer"></i> ลงทะเบียนคุม'], ['class' => 'dropdown-item open-modal-x', 'data' => ['size' => 'modal-md']]) ?>
                                    <?php endif;?>
                                    <li><?= Html::a('<i class="bi bi-printer me-1"></i> พิมพ์เอกสาร', ['/purchase/order/document','id' => $model->id,'title' => '<i class="bi bi-printer-fill"></i> พิมพ์เอกสาร'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?></li>
                                </li>
                                </li>
                            </ul>
                        </div>

                    </td>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="d-flex justify-content-between align-items-center gap-2 mt-3">
            <div class="flex-grow-1">
                <?= app\components\widgets\DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?>
            </div>
                <div>
                <?=$dataProvider->getTotalCount() <= 0 ? null :  Html::a('<i class="bi bi-chevron-double-right"></i> แสดงทั้งหมด',['/purchase/'.($container == 'pr-accept-order' ? 'pr-order' : $container )],['class' => 'btn btn-outline-secondary','data' => ['pjax' => 0]])?>
            </div>
        </div>
    </div>
</div>
<?php  yii\widgets\Pjax::end()?>