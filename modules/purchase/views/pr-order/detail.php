<?php
use yii\helpers\Html;
use app\components\SiteHelper;
?>
<table class="table table-striped-columns">
    <tbody>
        <tr class="">
            <td class="text-end">วันที่ขอซื้อ</td>
            <td> <?php echo Yii::$app->thaiFormatter->asDateTime($model->created_at, 'medium') ?></td>
            <td class="text-end" style="width:150px;">เลขที่ขอซื้อ</td>
            <td class="fw-semibold"><?= $model->pr_number ?></td>
        </tr>
        <tr class="">
            <td class="text-end">เพื่อจัดซื้อ/ซ่อมแซม</td>
            <td>
                <?php
                            echo $model->productType->id;
                            try {
                                echo $model->data_json['type_name'];
                            } catch (\Throwable $th) {
                            }
                        ?></td>
            <td class="text-end">วันที่ต้องการ</td>
            <td> <?= isset($model->data_json['due_date']) ? Yii::$app->thaiFormatter->asDate($model->data_json['due_date'], 'medium') : '' ?>
            </td>
        </tr>
        <tr>
            <td class="text-end">ผู้ขอ</td>
            <td> <?= $model->getUserReq()['avatar'] ?></td>
            <td class="text-end">ผู้เห็นชอบ</td>
            <td colspan=""><?= $model->viewLeaderUser()['avatar'] ?></td>
        </tr>
        <tr>
            <td class="text-end">เหตุผล</td>
            <td><?= isset($model->data_json['comment']) ? $model->data_json['comment'] : '' ?>
            </td>
            <td class="text-end">ความเห็น</td>
            <td colspan="6">
                <?php if($model->pr_number != ''):?>
                <?php if($model->data_json['pr_leader_confirm'] == 'Y'):?>
                <?=Html::a('<i class="bi bi-check2-circle"></i> เห็นชอบ',['/purchase/pr-order/leader-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-success open-modal','data' => ['size' => 'modal-md']])?>
                <?php elseif($model->data_json['pr_leader_confirm'] == 'N'):?>
                <?=Html::a('<i class="fa-solid fa-user-slash"></i> ไม่เห็นชอบ',['/purchase/pr-order/leader-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-danger open-modal','data' => ['size' => 'modal-md']])?>
                <?php else:?>
                <?=Html::a('<i class="fa-regular fa-clock"></i> รอเห็นชอบ',['/purchase/pr-order/leader-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-warning open-modal','data' => ['size' => 'modal-md']])?>
                <?php endif?>
                <?php endif?>

            </td>
        </tr>
        <?php if($model->data_json['pr_leader_confirm'] == 'Y'):?>
        <tr>
            <td class="text-end">ผู้ตรวจสอบ</td>
            <td > <?= $model->getMe()['avatar'] ?></td>
            <td class="text-end">ผู้อำนวยการ</td>
            <td > <?= SiteHelper::viewDirector()['avatar'] ?></td>
        </tr>
        <tr>
            <td class="text-end">จนท.พัสดุตรวจสอบ</td>
            <td >
                <?php if($model->data_json['pr_officer_checker'] == 'Y'):?>
                <?=Html::a('<i class="bi bi-check2-circle"></i> ผ่าน',['/purchase/pr-order/checker-confirm','id' => $model->id],
                                ['class' => 'btn btn-success open-modal','data' => ['size' => 'modal-md']])?>
                <?php elseif($model->data_json['pr_officer_checker'] == 'N'):?>
                <?=Html::a('<i class="fa-solid fa-user-slash"></i> ไม่ผ่าน',['/purchase/pr-order/checker-confirm','id' => $model->id],
                                ['class' => 'btn btn-danger open-modal','data' => ['size' => 'modal-md']])?>
                <?php else:?>
                <?=Html::a('<i class="fa-regular fa-clock"></i> ตรวจสอบ',['/purchase/pr-order/checker-confirm','id' => $model->id],
                                ['class' => 'btn btn-warning open-modal','data' => ['size' => 'modal-md']])?>
                <?php endif?>

            </td>

            <td class="text-end">ผู้อำนวยการอนุมัติ</td>
            <td >
                <?php if($model->data_json['pr_director_confirm'] == 'Y'):?>
                <?=Html::a('<i class="bi bi-check2-circle"></i> อนุมัติ',['/purchase/pr-order/director-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-success open-modal','data' => ['size' => 'modal-md']])?>
                <?php elseif($model->data_json['pr_director_confirm'] == 'N'):?>
                <?=Html::a('<i class="fa-solid fa-user-slash"></i> ไม่อนุมัติ',['/purchase/pr-order/director-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-danger open-modal','data' => ['size' => 'modal-md']])?>
                <?php else:?>
                <?=Html::a('<i class="fa-regular fa-clock"></i> รออนุมัติ',['/purchase/pr-order/director-confirm','id' => $model->id,'title' => 'หัวหน้าลงความเห็นชอบ'],
                                ['class' => 'btn btn-warning open-modal','data' => ['size' => 'modal-md']])?>
                <?php endif?>

            </td>
        </tr>
        
        <?php endif;?>
    </tbody>
</table>