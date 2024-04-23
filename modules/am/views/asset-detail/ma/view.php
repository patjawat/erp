<?php 

use yii\helpers\Html;
?>
<div class="d-flex justify-content-between">
        <!-- <h5 class="alert-heading"><i class="fa-solid fa-tag"></i> พ.ร.บ./ต่อภาษี</h5> -->
        <span class="fw-semibold">
        <i class="bi bi-list-check text-primary fs-5"></i> ข้อมูลการบำรุงรักษา
        </span>
        <span>
                        <i class="bi bi-calendar-check text-primary fs-5"></i><span class="fw-semibold"> วันที่ </span><?= Yii::$app->formatter->asDate($model->date_start, 'long') ?><br>

                </span>
    </div>
    <hr>
<div class="mt-1">
      <i class="bi bi-check2-circle text-primary fs-5"></i><span class="fw-semibold"> รหัส </span><?= $model->code ?>

</div>
<div class="mt-1">
<i class="bi bi-person-lines-fill text-primary fs-5"></i><span class="fw-semibold"> ผู้ตรวจเช็คอุปกรณ์ </span><?= $model->data_json["checker"] ?>
</div>
<div class="mt-1">

<div class="d-flex justify-content-between">
        <!-- <h5 class="alert-heading"><i class="fa-solid fa-tag"></i> พ.ร.บ./ต่อภาษี</h5> -->
        <span class="fw-semibold">
        <i class="bi bi-person-video2 text-primary fs-5"></i><span class="fw-semibold"> หัวหน้ารับรอง </span><?= $model->data_json["endorsee"] ?>
        </span>
        <span>
                        <i class="bi bi-clipboard-check text-primary fs-5"></i><span class="fw-semibold"> ผลการตรวจ </span><?= $model->data_json["status"] ?>

                </span>
    </div>
<i class="text-primary fs-5">*</i><span class="fw-semibold"> หมายเหตุ </span><?= $model->data_json["description"] ?>




<div
    class="table-responsive mt-3"
>
    <table
        class="table table-primary"
    >
        <thead class="table-secondary">
            <tr>
                <th scope="col">รายการที่ตรวจเช็ค</th>
                <th scope="col">สถานะ</th>
                <th scope="col">หมายเหตุ</th>
            </tr>
        </thead>
        <tbody>
                <?php foreach($model->data_json["items"] as $x){ ?>
                    <tr class="">
                        <td><?= $x["item"] ?></td>
                        <td><?= $x["ma_status"] ?></td>
                        <td><?= $x["comment"] ?></td>
                    </tr>
                <?php } ?>
        </tbody>
    </table>
</div>
<hr>
<div class="d-flex gap-2  justify-content-center">
                                                                                         <?php #   ['/am/asset-detail/update','name'=>'ma', "title"=>"<i class="fa-regular fa-pen-to-square"></i> แก้ไข","id"=>$model->id] ?>
    <?=Html::a('<i class="bx bx-edit-alt me-1"></i>แก้ไข', ['/am/asset-detail/update','name'=>'ma', "title"=>'<i class="fa-regular fa-pen-to-square"></i> แก้ไข',"id"=>$model->id], ['class' => 'btn btn-warning  open-modal', 'data' => ['size' => 'modal-lg']])?>
    <?=Html::a('<i class="bx bx-trash me-1"></i>ลบ', ['/am/asset-detail/delete', 'id' => $model->id], [
    'class' => 'btn btn-danger  delete-item',
])?>
</div>