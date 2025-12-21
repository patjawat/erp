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
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </a>
                                <a href="<?= Url::to(['/am/asset-document/update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข']) ?>" class="btn btn-icon btn-ghost-secondary open-modal" data-size="modal-lg" title="ดูรายละเอียด">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"></path>
                                    </svg>
                                </a>

                                <a href="<?= Url::to(['/am/asset-document/delete', 'id' => $item->id]) ?>" class="btn btn-icon btn-ghost-secondary delete-item" title="ดูรายละเอียด">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M10 11v6"></path>
                                        <path d="M14 11v6"></path>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"></path>
                                        <path d="M3 6h18"></path>
                                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </a>
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