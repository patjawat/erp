<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use yii\bootstrap5\LinkPager;
use app\modules\inventory\models\StockEvent;
// คำนวณค่าเริ่มต้นของลำดับที่


?>
<table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th class="fw-semibold text-center fw-semibold" style="width:30px">ลำดับ</th>
                            <th class="fw-semibold" style="width:210px">รหัส</th>
                            <th class="fw-semibold" scope="col">ผู้เบิก</th>
                            <th class="fw-semibold">หัวหน้าตรวจสอบ</th>
                            <th class="fw-semibold text-end">มูลค่า</th>
                            <th class="fw-semibold text-center" style="width:300px">สถานะ</th>
                            <th class="fw-semibold" style="width:100px">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle table-group-divider">
                        <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                        <tr>
                        <td class="text-center fw-semibold">
                                <?php 
                                if ($dataProvider->pagination !== false) {
                                    echo (($dataProvider->pagination->offset + 1) + $key);
                                } else {
                                    echo ($key + 1);
                                }
                                ?>
                            </td>
                            <td>
                              
                                <div>
                                    <p class="fw-semibold mb-0"><?=$item->code?></p>
                                    <p class="text-muted mb-0 fs-13"><?=$item->viewCreatedAt()?></p>
                                </div>
                            </td>
                            <td>
                                <?php
                                try {
                                   echo $item->CreateBy($item->fromWarehouse->warehouse_name.' | '.$item->viewCreated())['avatar'];
                                } catch (\Throwable $th) {
                                }
                                ?>
                            </td>
                            <td><?=$item->viewChecker()['avatar']?></td>
                            <td class="text-end">
                                <span class="fw-semibold">
                                    <?php echo $item->order_status == 'success' ? number_format($item->getTotalOrderPriceSuccess(),2) : number_format($item->getTotalOrderPrice(),2) ?>        
                                </span>
                            </td>
                            <td class="text-center"><?=$item->viewstatus()?></td>
                            <td>

                            <div class="btn-group">
                                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/inventory/stock-order/view','id' => $item->id], ['class' => 'btn btn-light w-100']) ?>
                                <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                    <i class="bi bi-caret-down-fill"></i>
                                </button>

                                <ul class="dropdown-menu">
                                <?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์เอกสาร', ['/inventory/document/stock-order','id' => $item->id], ['class' => 'dropdown-item open-modal','data-pjax' => '0','data' => ['size' => 'modal-xl']]) ?>
                                </ui>
                            </div>

                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="d-flex justify-content-center mt-5">
            <div class="text-muted">
                <?= ($dataProvider->pagination !== false) ?  LinkPager::widget([
                            'pagination' => $dataProvider->pagination,
                            'firstPageLabel' => 'หน้าแรก',
                            'lastPageLabel' => 'หน้าสุดท้าย',
                            'options' => [
                                'listOptions' => 'pagination pagination-sm',
                                'class' => 'pagination-sm',
                            ],
                        ]) : ''; ?>
            </div>
        </div>