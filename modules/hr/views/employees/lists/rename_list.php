
<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
$title = '<i class="fa-solid fa-people-group"></i> ประวัติการเปลี่ยนชื่อ';
?>
<?php Pjax::begin(['id' => 'rename']);?>

<div class="card border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h5 class="card-title"><?=$title?></h5>
            <?=Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/hr/employee-detail/create', 'emp_id' => $model->id, 'name' => 'rename', 'title' => $title], ['class' => 'btn btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-lg']])?>
        </div>
        <div class="table-responsive"  style="min-height:468px">
            <table class="table mt-4">
                <thead>
                    <tr>
                        <th scope="col">ตั้งแต่วันที่</th>
                        <th scope="col">รายการเดิม</th>
                        <th scope="col">สิ่งที่เปลี่ยน</th>
                        <th scope="col" class="text-center align-middle">รายการปัจจุบัน</th>
                        <th scope="col" class="text-center align-middle">เอกสารอ้างอิง</th>
                        <th scope="col" class="text-center align-middle">หมายเหตุ</th>
                        <th class="text-center" style="width: 100px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($model->hisRename as $item): ?>
                    <tr class="">
                        <td scope="row">
                            <?=isset($item->data_json['date_start']) ? AppHelper::DateFormDb($item->data_json['date_start']) : ''?>
                        </td>

                        <td class="text-start align-middle"><?=$item->old_fullname?></td>
                        <td class="text-start align-middle"><?=$item->new_fullname?></td>
                        <td class="text-center align-middle"><?=isset($item->data_json['rename_type']) ?  $item->data_json['rename_type'] : null?></td>
                        <td class="text-center align-middle"><?=isset($item->data_json['doc_ref']) ?  $item->data_json['doc_ref'] : null?></td>
                        <td class="text-center align-middle"><?=isset($item->data_json['comment']) ?  $item->data_json['comment'] : null?></td>
                        <td class="align-middle text-center ">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false"><i
                                        class="bx bx-dots-vertical-rounded fw-bold"></i></button>
                                <div class="dropdown-menu" style="">
                                    <?=Html::a('<i class="bx bx-edit-alt me-1"></i>แก้ไข', ['/hr/employee-detail/update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-user-tag"></i> ตำแหน่ง'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']])?>

                                    <?=Html::a('<i class="bi bi-trash"></i>ลบ', ['/hr/employee-detail/delete', 'id' => $item->id], [
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