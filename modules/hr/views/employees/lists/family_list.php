
<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
$title = '<i class="fa-solid fa-people-group"></i> ประวัติครอบครัว';
?>
<?php Pjax::begin(['id' => 'family']);?>

<div class="card border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h5 class="card-title"><?=$title;?></h5>
            <?=Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/hr/employee-detail/create', 'emp_id' => $model->id, 'name' => 'family', 'title' => $title], ['class' => 'btn btn-primary rounded-pill open-modal', 'data' => ['size' => 'modal-md']])?>
        </div>
        <div class="table-responsive">
            <table class="table table-striped" style="margin-bottom: 100px;">
                <thead>
                    <tr>
                    <th style="width: 32px;">#</th>
                        <th >ชื่อ-สกุล</th>
                        <th style="width:104px">ความสัมพันธ์</th>
                        <th class="align-middle">ที่อยู่</th>
                        <th class="align-middle">โทรศัพท์</th>
                        <th class="align-middle">สถานะ</th>
                        <th class="text-center" style="width: 100px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($model->familys as $key => $item): ?>
                    <tr class="">
                      <td><?=$key+1?></td>
                        <td>
                                    <?=$item->family_fullname;?>
                        </td>
                        <td>
                        <?=$item->data_json['family_relation'];?>
                        </td>
                        <td>
                        <?=$item->data_json['address'];?>
                        </td>
                        <td>
                        <?=$item->data_json['phone'];?>
                        </td>
                        <td>
                            <?=$item->status?>
                        </td>
                        <td class="text-center align-middle">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                <div class="dropdown-menu">
                                    <??>
                                    <?=Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>แก้ไข', ['/hr/employee-detail/update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-user-tag"></i> การศึกษา'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']])?>

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