<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

$title = '<i class="fa-solid fa-chess"></i> เครื่องราชอิสริยาภรณ์';
?>
<?php Pjax::begin(['id' => 'insignia']); ?>


<div class="card border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-3">
            <h5 class="card-title"><?= $title; ?></h5>
            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/hr/employee-detail/create', 'emp_id' => $model->id, 'name' => 'insignia', 'title' => $title], ['class' => 'btn btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-lg']]) ?>
        </div>

        <div class="table-responsive" style="min-height: 300px;">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>ชั้นตราเครื่องราชอิสริยาภรณ์</th>
                        <th class="text-center">ปี พ.ศ.</th>
                        <th>อ้างอิงราชกิจจาฯ</th>
                        <th class="text-center">วันที่ประกาศ</th>
                        <th class="text-center">สถานะการส่งคืน</th>
                        <th class="text-center" style="width: 150px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($model->insignias)): ?>
                        <?php foreach ($model->insignias as $key => $item):
                            // ตรวจสอบว่าข้อมูลเป็น Array หรือต้อง json_decode ก่อน
                            $data = is_array($item->data_json) ? $item->data_json : json_decode($item->data_json, true);
                        ?>
                            <tr>
                                <td class="text-center"><?= $key + 1 ?></td>
                                <td>
                                    <strong><?= $data['name'] ?? '-' ?></strong>
                                </td>
                                <td class="text-center"><?= $data['thai_year'] ?? '-' ?></td>
                                <td>
                                    <small>
                                        เล่ม <?= $data['gazette_book'] ?? '-' ?>
                                        ตอน <?= $data['gazette_section'] ?? '-' ?>
                                        หน้า <?= $data['gazette_page'] ?? '-' ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <?= !empty($data['gazette_date']) ? date('d/m/Y', strtotime($data['gazette_date'])) : '-' ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $status = $data['return_status'] ?? '';
                                    $badgeClass = 'badge-secondary'; // default
                                    if ($status == 'คืนแล้ว') $badgeClass = 'bg-success text-white';
                                    if ($status == 'ยังไม่คืน') $badgeClass = 'bg-warning text-dark';
                                    if ($status == 'ชดใช้เงินแทน') $badgeClass = 'bg-info text-white';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>" style="padding: 5px 10px; border-radius: 4px;">
                                        <?= $status ?>
                                    </span>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                        <div class="dropdown-menu">
                                            <? ?>
                                            <?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>แก้ไข', ['/hr/employee-detail/update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-user-tag"></i> การศึกษา'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']]) ?>

                                            <?= Html::a('<i class="fa-solid fa-trash me-1"></i>ลบ', ['/hr/employee-detail/delete', 'id' => $item->id], [
                                                'class' => 'dropdown-item delete-item',
                                            ]) ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">ไม่พบข้อมูลเครื่องราชอิสริยาภรณ์</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>


    </div>
</div>

<?php Pjax::end(); ?>