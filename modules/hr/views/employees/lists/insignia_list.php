
<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
$title = '<i class="fa-solid fa-chess"></i> เครื่องราชอิสริยาภรณ์';
?>
<?php Pjax::begin(['id' => 'award']);?>

<div class="card border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h5 class="card-title"><?=$title;?></h5>
            <?=Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/hr/employee-detail/create', 'emp_id' => $model->id, 'name' => 'insignia', 'title' => $title], ['class' => 'btn btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-md']])?>
        </div>
        <div class="table-responsive">
            <table class="table table-striped mt-3" style="margin-bottom: 100px;">
                <thead>
                    <tr>
                    <th style="width: 32px;">#</th>
                        <th style="width:120px">วันที่รับรางวัล</th>
                        <th>ชื่อรางวัล</th>
                        <th class="align-middle">หน่วยงานที่มอบรางวัล</th>
                        <th class="text-center" style="width: 100px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($model->insignias as $key => $item): ?>
                    <tr class="">
                      <td><?=$key+1?></td>
                        <td>
                                    <?=$item->date_start?>
                                
                        </td>
                        <td>
                            <?=$item->name?>
                       
                        </td>
                        <td>
                        <?=isset($item->data_json['company_name']) ? $item->data_json['company_name'] : '';?>
                        </td>
                        <td class="text-center align-middle">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false"><i
                                        class="bx bx-dots-vertical-rounded fw-bold"></i></button>
                                <div class="dropdown-menu" style="">
                                    <??>
                                    <?=Html::a('<i class="bx bx-edit-alt me-1"></i>แก้ไข', ['/hr/employee-detail/update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-user-tag"></i> การศึกษา'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']])?>

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