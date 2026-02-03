<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;

$title = '<i data-lucide="heart-pulse"></i> ข้อมูลประวัติการตรวจสุขภาพ';
?>



<div class="card border-0" style="min-height:500px">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h5 class="card-title"><?= $title; ?></h5>
            <?= Html::a('<i class="fa-solid fa-circle-plus"></i> ทำแบบคัดกรองใหม่', ['/hr/health/create', 'emp_id' => $model->id, 'name' => 'health', 'title' => $title], ['class' => 'btn btn-primary rounded-pill open-modal-xx', 'data' => ['size' => 'modal-xl', 'pjax' => '0']]) ?>
        </div>
        <div class="table-responsive" style="min-height:500px">
            <table class="table table-striped">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr class="text-secondary text-uppercase">
                            <th>ปี</th>
                            <th>สถานะความเสี่ยง</th>
                            <th>BMI</th>
                            <th>ความดัน</th>
                            <th>วันที่</th>
                            <th class="text-center align-middle">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        <?php foreach ($model->health as $key => $item): ?>
                            <tr>
                                <td class="fw-bold text-dark"><?= $item->data_json['checkup_year'] ?? '-' ?></td>
                                <td class="px-4 py-4">
                                    <?php
                                    $color = $item->getBmiResult()['color'];
                                    ?>
                                    <span class="badge bg-<?= $color?> bg-opacity-10 text-<?= $color ?> border border-<?= $color ?>-subtle rounded-pill fw-medium px-2 py-1" style="font-size: 10px;"><?= $item->getBmiResult()['label']?></span>
                                </td>
                                <td class="text-secondary"><?= $item->bmi?></td>
                                <td class="text-secondary"><?= $item->bloodPressure?></td>
                                <td class="text-muted small">
                                    <?php
                                    echo isset($item->data_json['screening_date']) ? AppHelper::convertToThai($item->data_json['screening_date']) : '';
                                    ?>
                                    </td>
                                <td class="text-center align-middle">
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                        <div class="dropdown-menu">
                                            <? ?>
                                            <?= Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>แก้ไข', ['/hr/health/update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-user-tag"></i> การศึกษา'], ['class' => 'dropdown-item open-modal-xx', 'data' => ['size' => 'modal-md', 'pjax' => '0']]) ?>

                                            <?= Html::a('<i class="fa-solid fa-trash me-1"></i>ลบ', ['/hr/employee-detail/delete', 'id' => $item->id], [
                                                'class' => 'dropdown-item delete-item',
                                            ]) ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
        </div>


    </div>
</div>