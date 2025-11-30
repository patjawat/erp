<div class="d-flex align-items-center gap-2 mb-4">
    <div class="p-2 bg-primary bg-opacity-10 rounded-circle text-primary"><svg
            xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="lucide lucide-file-text" aria-hidden="true">
            <path
                d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z">
            </path>
            <path d="M14 2v5a1 1 0 0 0 1 1h5"></path>
            <path d="M10 9H8"></path>
            <path d="M16 13H8"></path>
            <path d="M16 17H8"></path>
        </svg></div>
    <h6 class="fw-bold mb-0 text-dark">ประวัติการลา</h6>
</div>
<table class="table table-striped mt-3">
    <thead>
        <tr class="table-secondary">
            <th scope="col">ผู้ขออนุมัติการลา</th>
            <th scope="col">ประเภทการลา</th>
            <th scope="col">เหตุผล</th>
            <th class="text-center" scope="col">เป็นเวลา/วัน</th>
            <th scope="col">วันที่</th>
            <th scope="col">ปีงบประมาณ</th>

        </tr>
    </thead>
    <tbody class="align-middle table-group-divider">
        <?php foreach ($model->listHistory() as $item): ?>
            <tr class="">
                <td class="text-truncate" style="max-width: 230px;">
                    <?= $item->getAvatar(false)['avatar'] ?>
                </td>
                <td class="text-start"><?php echo $item->leaveType->title ?></td>
                <td class="text-start"><?php echo $item->data_json['reason'] ?></td>
                <td class="text-center"><?php echo $item->total_days ?></td>
                <td><?= $item->showLeaveDate() ?></td>
                <td class="text-center"><?php echo $item->thai_year ?></td>

            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
$js = <<< JS
    var offcanvasElement = document.getElementById('offcanvasExample');
    var offcanvas = new bootstrap.Offcanvas(offcanvasElement, {
    backdrop: 'static'
    });

JS;
$this->registerJs($js);
?>