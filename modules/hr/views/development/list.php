<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>

    <table class="table table-primary">
        <thead>
            <tr>
                <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                <th class="text-center fw-semibold" style="width:30px">ปีงบประมาณ</th>

                <th class="fw-semibold" scope="col">เรื่อง/วัน/สถานที่</th>
                <th class="fw-semibold" scope="col">คณะเดินทาง</th>
                <th class="fw-semibold" scope="col">สถานะ</th>
                <th class="fw-semibold text-center">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dataProvider->getModels() as $key => $item): ?>
            <tr>
                <td class="text-center fw-semibold">
                    <?php echo (($dataProvider->pagination->offset + 1) + $key) ?>
                </td>
                <td><?= $item->thai_year; ?></td>
                <td>
                    <div>
                        <p class="fw-semibold mb-0"><?= $item->topic ?></p>
                        สถานที่ <?= $item->data_json['location'] ?? '-' ?> <?= $item->showDateRange() ?>
                    </div>
                </td>

                <td> <?= $item->StackMember() ?></td>
                <td> <?php //  $item->getStatus() ?></td>
                <td style="width:120px">
                    <div class="btn-group">
                        <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-light w-100 open-modal', 'data' => ['size' => 'modal-xl']]) ?>
                        <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                            data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                            <i class="bi bi-caret-down-fill"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><?= Html::a('<i class="fa-solid fa-eye me-1"></i> แสดงรายละเอียด', ['view', 'id' => $item->id], ['class' => 'dropdown-item']) ?></li>
                            <li><?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์ใบขอไปราชการ', ['view', 'id' => $item->id], ['class' => 'dropdown-item']) ?></li>
                            <li><?= Html::a('<i class="fa-solid fa-triangle-exclamation me-1"></i> แจ้งยกเลิก', ['view', 'id' => $item->id], ['class' => 'dropdown-item']) ?></li>
                            <li><?= Html::a('<i class="fa-solid fa-user-check me-1"></i> ตอบรับเป็นวิทยากร', ['view', 'id' => $item->id], ['class' => 'dropdown-item']) ?></li>
                            <li><?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์ใบขออนุญาต', ['view', 'id' => $item->id], ['class' => 'dropdown-item']) ?></li>
                            <li><?= Html::a('<i class="fa-solid fa-print me-1"></i> พิมพ์ใบตอบรับเป็นวิทยากร', ['view', 'id' => $item->id], ['class' => 'dropdown-item']) ?></li>
                            </ui>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
