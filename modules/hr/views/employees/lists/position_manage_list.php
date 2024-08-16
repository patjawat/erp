<?php

use app\components\AppHelper;
use yii\helpers\Html;
use yii\widgets\Pjax;

$items = [
    ['name' => 'position_name', 'title' => 'ตั้งค่าชื่อตำแหน่ง'],
    ['name' => 'position_type', 'title' => 'ตั้งค่าตำแหน่ง'],
    ['name' => 'position_level', 'title' => 'ระดับตำแหน่ง'],
    ['name' => 'position_manage', 'title' => 'ตำแหน่งบริหาร'],
    ['name' => 'expertise', 'title' => 'ความเชี่ยวชาญ'],
];
?>

<style>
.dropdown-toggle::after {
    display: none;
}

.dropdown-item {
    background-color: #ffff;
}
</style>

<?php Pjax::begin(['id' => 'position']);?>

<div class="card border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h5 class="card-title"><i class="fa-solid fa-people-group"></i> ปฏิบัติหน้าที่/ราชการตำแหน่งบริหาร</h5>

            <?php // Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/hr/employee-detail/create', 'emp_id' => $model->id, 'name' => 'position', 'title' => '<i class="fa-solid fa-user-tag"></i> ตำแหน่ง'], ['class' => 'btn btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-lg']])?>
        </div>
        <div class="table-responsive" style="min-height:468px">
            <table class="table table-striped mt-4">
                <thead>
                    <tr>
                        <th scope="col">ตั้งแต่วันที่</th>
                        <th scope="col">รายการ</th>
                        <!-- <th scope="col" class="align-middle">เงินเดือน</th>
                        <th scope="col" class="align-middle">เอกสารอ้างอิง</th> -->
                        <th class="text-center" style="width: 100px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($model->positionManage as $item): ?>
                    <tr class="">
                        <td class="align-middle">
                            <i class="bi bi-calendar-event"></i>
                            <?=isset($item->data_json['date_start']) ? AppHelper::DateFormDb($item->data_json['date_start']) : ''?>
                        </td>


                        <td class="align-middle">
                            <div class="d-flex flex-column">
                                <div class="fw-lighter">
                                    <i class="bi bi-check2-circle text-primary"></i>
                                    <?=isset($item->data_json['statuslist']) ? $item->data_json['statuslist'] : null?>
                                </div>
                                <div>
                                <i class="bi bi-check2-circle text-primary"></i> <span class="fw-semibold">เอกสารอ้างอิง</span> : <span class="text-primary"><?=isset($item->data_json['doc_ref']) ?  $item->data_json['doc_ref'] : null?></span>
                                        </div>
                                <div>

                                    <div class="d-flex flex-row gap-3">

                                        <div>

                                            <i class="bi bi-check2-circle text-primary"></i> <span class="fw-semibold">ตำแหน่งเลขที่</span> : <span
                                                class="badge rounded-pill text-primary-emphasis bg-warning"><?=isset($item->data_json['position_number']) ? $item->data_json['position_number'] : null?></span>
                                        </div>
                                        |
                                        <div>
                                            <i class="bi bi-wallet2"></i> เงินเดือน :
                                            <code class=""><?=isset($item->data_json['salary']) ?  number_format($item->data_json['salary']) : null?> </code>
                                            บาท
                                        </div>
                                       
                                    </div>
                                </div>
                                

                            </div>
                        </td>


                        <td class="align-middle text-center ">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                <div class="dropdown-menu">
                                    <??>
                                    <?=Html::a('<i class="fa-solid fa-copy me-1"></i> ทำสำเนา', ['/hr/employee-detail/create', 'id' => $item->id, 'emp_id' => $model->id, 'name' => 'position', 'title' => '<i class="fa-solid fa-user-tag"></i> ตำแหน่ง'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']])?>
                                    <?=Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>แก้ไข', ['/hr/employee-detail/update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-user-tag"></i> ตำแหน่ง'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']])?>

                                    <?=Html::a('<i class="fa-solid fa-trash me-1"></i>ลบ', ['/hr/employee-detail/delete', 'id' => $item->id], [
    'class' => 'dropdown-item delete-item',
])?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>


    </div>
</div>

<?php Pjax::end();?>