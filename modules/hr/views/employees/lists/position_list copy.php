<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;


$items = [
    ['name' => 'position_name','title' => 'ตั้งค่าชื่อตำแหน่ง'],
    ['name' => 'position_type','title' => 'ตั้งค่าตำแหน่ง'],
    ['name' => 'position_level','title' => 'ระดับตำแหน่ง'],
    ['name' => 'position_manage','title' => 'ตำแหน่งบริหาร'],
    ['name' => 'position_group','title' => 'ประเภท/กลุ่มงาน'],
    ['name' => 'expertise','title' => 'ความเชี่ยวชาญ'],
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
            <h5 class="card-title"><i class="fa-solid fa-people-group"></i> ตำแหน่ง</h5>


            <div class="btn-group dropup">
                <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/hr/employee-detail/create', 'emp_id' => $model->id, 'name' => 'position', 'title' => '<i class="fa-solid fa-user-tag"></i> ตำแหน่ง'], ['class' => 'btn btn-primary rounded-start-pill shadow open-modal', 'data' => ['size' => 'modal-lg']])?>
                <button type="button" class="btn btn-secondary dropdown-toggle dropdown-toggle-split"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="visually-hidden"><i class="fa-solid fa-people-group"></i>Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu" style="">
                    <?php foreach($items  as $item):?>
                    <li><?=Html::a('<i class="fa-regular fa-circle-check me-1"></i>'.$item['title'],['/hr/categorise','name' =>  $item['name'],'title' => $item['title']],['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-lg']])?>
                    </li>
                    <?php endforeach;?>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                </ul>
            </div>

            <?php // Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/hr/employee-detail/create', 'emp_id' => $model->id, 'name' => 'position', 'title' => '<i class="fa-solid fa-user-tag"></i> ตำแหน่ง'], ['class' => 'btn btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-lg']])?>
        </div>
        <div class="table-responsive" style="min-height:468px">
            <table class="table mt-4">
                <thead>
                    <tr>
                        <th scope="col">ตั้งแต่วันที่</th>
                        <th scope="col">รายการเปลี่ยนแปลง</th>
                        <th scope="col" class="align-middle">เลขที่ตำแหน่ง</th>
                        <th scope="col" class="align-middle">เงินเดือน</th>
                        <th scope="col" class="align-middle">เอกสารอ้างอิง</th>
                        <th class="text-center" style="width: 100px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($model->positions as $item): ?>
                    <tr class="">
                        <td scope="row">
                            <?=isset($item->data_json['date_start']) ? AppHelper::DateFormDb($item->data_json['date_start']) : ''?>
                        </td>


                        <td class="align-middle">
                            <?=isset($item->data_json['statuslist']) ?  $item->data_json['statuslist'] : null?></td>
                        <td class="align-middle">
                            <?=isset($item->data_json['position_number']) ?  $item->data_json['position_number'] : null?>
                        </td>
                        <td class="align-middle">
                            <?=isset($item->data_json['salary']) ?  $item->data_json['salary'] : null?></td>
                        <td class="align-middle">
                            <?=isset($item->data_json['doc_ref']) ?  $item->data_json['doc_ref'] : null?></td>
                        <td class="align-middle text-center ">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                <div class="dropdown-menu" style="">
                                    <??>
                                    <?=Html::a('<i class="fa-solid fa-copy me-1"></i> ทำสำเนา', ['/hr/employee-detail/create','id' => $item->id, 'emp_id' => $model->id, 'name' => 'position', 'title' => '<i class="fa-solid fa-user-tag"></i> ตำแหน่ง'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']])?>
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