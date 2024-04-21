<?php
use app\components\CategoriseHelper;

?>

<?php $list = CategoriseHelper::CategoryAndName($model->code,"ma")->all() ?>
<div class="d-flex justify-content-between">
    <h3><i class="fa-solid fa-brush"></i> การบำรุงรักษา</h3>
</div>
<div
    class="table-responsive"
>
    <table
        class="table table-primary"
    >
        <thead class="table-secondary">
            <tr>
                <th scope="col">วันที่</th>
                <th scope="col">ผู้ตรวจเช็คอุปกรณ์</th>
                <th scope="col">หัวหน้ารับรอง</th>
                <th scope="col">รายการที่ตรวจเช็ค</th>
                <th scope="col">ผลการตรวจ</th>
                <th scope="col">หมายเหตุ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($list as $item){ ?>
            <tr class="">
                <td scope="row"><?= $item->data_json["date"] ?></td>
                <td><?= $item->data_json["checker"] ?></td>
                <td><?= $item->data_json["endorsee"] ?></td>
                <td><?= $item->data_json["items"] ?></td>
                <td><?= $item->data_json["ma_status"] ?></td>
                <td><?= $item->data_json["comment"] ?></td>
            </tr>
           <?php } ?>
        </tbody>
    </table>
</div>
