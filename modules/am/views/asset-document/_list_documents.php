<?php

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
                                <th class="no-print" style="width:130px;">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($listAssetDocument as $item):?>
                            <tr>
                                <td><?= $item->data_json['title'] ?? '-' ?></td>
                                <td><span class="badge bg-secondary"><?= $item->data_json['asset_document_type'] ?? '-' ?></span></td>
                                <td><?=$item->created_at?></td>
                                <td><?=$item->created_by?></td>
                                <td class="no-print">
                                     <?= Html::a('<i class="fa-regular fa-eye"></i>', ['/am/asset-document/view','id' => $item->id], ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                     <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/am/asset-document/update','id' => $item->id], ['class' => 'btn btn-sm btn-outline-warning open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                    <button class="btn btn-sm btn-outline-success"><i class="bi bi-download"></i></button>
                                </td>
                            </tr>
                            <?php endforeach;?>
                        </tbody>
                    </table>
                </div>