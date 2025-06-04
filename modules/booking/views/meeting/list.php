<?php
use yii\helpers\Html;
?>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
            <th class="fw-semibold">ห้องประชุม</th>
            <th class="fw-semibold">หัวข้อการประชุม</th>
            <th class="fw-semibold">ผู้ขอ</th>
            <th class="fw-semibold">สถานะ</th>
            <th class="fw-semibold text-center">ดำเนินการ</th>
        </tr>
    </thead>
    <tbody class="table-group-divider align-middle">
        <!-- Row 1 -->
        <?php foreach ($dataProvider->getModels() as $key => $item): ?>
        <tr>
            <td class="text-center fw-semibold">
                <?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
            <td>
                <div class="avatar-detail">
                    <h6 class="mb-0 fs-13"><?= $item->room?->title ?? '-' ?></h6>
                    <p class="text-muted mb-0 fs-13">
                        <?= $item->viewMeetingDate() ?>
                    </p>
                </div>
            </td>
            <td>
                <div class="avatar-detail">
                    <h6 class="mb-0 fs-13"><?= $item->title ?></h6>
                    <p class="text-muted mb-0 fs-13">
                        เริ่มเวลา <?= $item->viewMeetingTime() ?>
                    </p>
                </div>
            </td>
            <td><?= $item->getUserReq()['avatar'] ?></td>
            <td><?= $item->viewStatus()['view'] ?>
            </td>

            <td class="fw-light text-center">
                <div class="btn-group">
                    <?= Html::a('<i class="fa-solid fa-pen-to-square"></i>', [$url . 'view', 'id' => $item->id], ['class' => 'btn btn-light w-100 open-modal', 'data' => ['size' => 'modal-md']]) ?>
                    <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                        data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                        <i class="bi bi-caret-down-fill"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><?php echo Html::a('<i class="fa-regular fa-circle-xmark me-1"></i> ยกเลิก', ['/booking/vehicle/cancel', 'id' => $item->id], ['class' => 'dropdown-item cancel-order','data' => ['size' => 'modal-lg']])?>
                        </li>
                    </ul>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
    <?= yii\bootstrap5\LinkPager::widget([
                        'pagination' => $dataProvider->pagination,
                        'firstPageLabel' => 'หน้าแรก',
                        'lastPageLabel' => 'หน้าสุดท้าย',
                        'options' => [
                            'listOptions' => 'pagination pagination-sm',
                            'class' => 'pagination-sm',
                        ],
                    ]); ?>
</div>