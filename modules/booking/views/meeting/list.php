<?php
use yii\helpers\Html;
?>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                <th class="fw-semibold">หัวข้อการประชุม</th>
                <th class="fw-semibold">ผู้ขอ</th>
                <th class="fw-semibold">ห้องประชุม</th>
                <th class="fw-semibold">สถานะ</th>
                <th class="fw-semibold text-center">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody class="table-group-divider">
            <!-- Row 1 -->
            <?php foreach ($dataProvider->getModels() as $key => $item): ?>
            <tr>
                <td class="text-center fw-semibold">
                    <?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                <td>
                    <div class="avatar-detail">
                        <h6 class="mb-0 fs-13"><?= $item->title ?></h6>
                        <p class="text-muted mb-0 fs-13">
                            <?= $item->viewMeetingDate() ?> เวลา <?= $item->viewMeetingTime() ?>
                        </p>
                    </div>
                </td>
                <td><?= $item->getUserReq()['avatar'] ?></td>
                <td><?= $item->room->title ?></td>
                <td><?= $item->viewStatus()['view'] ?></td>
                <td class="text-center">
                    <?= Html::a('<i class="fa-solid fa-eye fa-2x"></i>', [$url . 'view', 'id' => $item->id], ['class' => 'open-modal', 'data' => ['size' => 'modal-md']]) ?>
                    <?php if ($item->status == 'Pending'): ?>
                    <div class="action-icon approve d-inline-flex confirm-meeting" data-id="<?= $item->id ?>"
                        data-status="Pass" data-text="อนุมัติการจอง">
                        <i class="fa-solid fa-circle-check fa-2x"></i>
                    </div>
                    <?php endif; ?>
                    <?php if ($item->status == 'Pending'): ?>
                    <div class="action-icon reject d-inline-flex confirm-meeting" data-id="<?= $item->id ?>"
                        data-status="Cancel" data-text="ยกเลิกการจอง">
                        <i class="fa-solid fa-circle-xmark fa-2x"></i>
                    </div>
                    <?php endif; ?>
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
</div>