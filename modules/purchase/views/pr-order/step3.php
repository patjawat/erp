

<?php
use yii\helpers\Html;
?>
<div class="d-flex justify-content-between">
            <div>
                <h5><i class="fa-solid fa-circle-info text-primary"></i> ออกใบสั่งซื้อ</h5>
            </div>
           
        </div>
        <div class="card">
            <div class="card-body">
            <div class="dropdown float-end">
                    <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa-solid fa-ellipsis"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <?= Html::a('<i class="fa-regular fa-eye me-1 text-primary"></i> แสดง', ['update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                        <?= Html::a('<i class="fa-regular fa-file-word me-1"></i> พิมพ์', ['update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                        <?= Html::a('<i class="bx bx-trash me-1 text-danger"></i> ลบ', ['/sm/asset-type/delete', 'id' => $model->id], [
                            'class' => 'dropdown-item  delete-item',
                        ]) ?>
                    </div>
                </div>
                <table class="table table-striped-columns">
                    <tbody>
                        <tr class="">
                            <td class="text-end" style="width:150px;">เลขที่สั่งซื้อ</td>
                            <td class="fw-semibold">PO-670001</td>
                            <td class="text-end">ผู้ขอ</td>
                            <td> <?= $model->getUserReq()['avatar'] ?></td>
                        </tr>
                        <tr class="">
                            <td>เพื่อจัดซื้อ/ซ่อมแซม</td>
                            <td>
                                <?php
                                    try {
                                        echo $model->data_json['product_type_name'];
                                    } catch (\Throwable $th) {
                                    }
                                ?></td>
                            <td class="text-end">วันที่ขอซื้อ</td>
                            <td> <?php echo Yii::$app->thaiFormatter->asDateTime($model->created_at, 'medium') ?></td>

                        </tr>
                        <tr class="">
                            <td class="text-end">เหตุผล</td>
                            <td><?= isset($model->data_json['comment']) ? $model->data_json['comment'] : '' ?></td>
                            <td class="text-end">วันที่ต้องการ</td>
                            <td> <?= isset($model->data_json['due_date']) ? Yii::$app->thaiFormatter->asDate($model->data_json['due_date'], 'medium') : '' ?>
                            </td>
                        </tr>
                        <td class="text-end">ผู้เห็นชอบ</td>
                        <td colspan="5"><?= $model->viewLeaderUser()['avatar'] ?></td>
                        </tr>
                        <tr>
                            <td class="text-end">ความเห็น</td>
                            <td colspan="5"><?= isset($model->data_json['pr_confirm_2']) ? '<span class="badge rounded-pill bg-success-subtle"><i class="fa-regular fa-thumbs-up"></i> ' . $model->data_json['pr_confirm_2'] . '</span>' : '' ?></td>
                        </tr>
                    </tbody>
                </table>

            </div>
        </div>
