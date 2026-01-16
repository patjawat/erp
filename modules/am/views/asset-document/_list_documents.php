<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\am\models\AssetDetail;
$listAssetDocument = AssetDetail::find()->where(['asset_id' => $model->id,'name' => 'asset_document'])->all();
?>
<div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ชื่อเอกสาร</th>
                                <th>ประเภท</th>
                                <th>วันที่อัปโหลด</th>
                                <th>ผู้อัปโหลด</th>
                                <th class="no-print" style="width:80px;">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($listAssetDocument as $item):?>
                            <tr>
                                <td><?= $item->data_json['title'] ?? '-' ?></td>
                                <td><?= $item->data_json['asset_document_type'] ?? '-' ?></td>
                                <td><?=Yii::$app->thaiDate->toThaiDate($item->created_at, true, false);?></td>
                                <td><?=$item->createdBy?->employee?->fullname ?? '-'?></td>

                                <td class="text-center py-2">
                            <div class="d-flex justify-content-center">
                                <a href="<?= Url::to(['/am/asset-document/view', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary open-modal" data-size="modal-lg" title="ดูรายละเอียด">
                                    <i class="fa-regular fa-eye"></i></a>
                                <a href="<?= Url::to(['/am/asset-document/update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข']) ?>" class="btn btn-icon btn-ghost-secondary open-modal" data-size="modal-lg" title="ดูรายละเอียด">
                                 <i class="fa-regular fa-pen-to-square"></i></a>

                                <a href="<?= Url::to(['/am/asset-document/delete', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary delete-item" title="ดูรายละเอียด">
                                   <i class="fa-regular fa-trash-can"></i></a>
                            </div>
                        </td>
                                <!-- <td class="no-print">
                                     <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/am/asset-document/update','id' => $item->id], ['class' => 'btn btn-sm btn-outline-warning open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                    <button class="btn btn-sm btn-outline-success"><i class="bi bi-download"></i></button>
                                </td> -->
                            </tr>
                            <?php endforeach;?>
                        </tbody>
                    </table>
                </div>