<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\inventory\models\StockEvent;
?>
<table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th style="width:210px">รหัส</th>
                            <th scope="col">ผู้เบิก</th>
                            <th >ผู้ตรวจสอบ</th>
                            <th >มูลค่า</th>
                            <th class="text-center" style="width:300px">สถานะ</th>
                            <th style="width:100px">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle table-group-divider">
                        <?php foreach ($dataProvider->getModels() as $item): ?>
                        <tr>
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
                            <td><?=number_format($item->getTotalOrderPrice(),2)?></td>
                            <td class="text-center"><?=$item->viewstatus()?></td>
                            <td>
                                <div class="btn-group">
                                    <?=Html::a('<i class="fa-regular fa-pen-to-square text-primary"></i>',['/inventory/stock-order/view','id' => $item->id],['class'=> 'btn btn-light'])?>

                                    <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                        <i class="bi bi-caret-down-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu">

                                        <li><?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์ใบเบิก', ['/inventory/document/stock-order','id' => $item->id], ['class' => 'dropdown-item open-modal','data-pjax' => '0','data' => ['size' => 'modal-lg']]) ?>
                                        </li>


                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>