<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;


$this->title = "การรับโทษทางวินัย";
?>
<?php  Pjax::begin(['id' => 'blame']);?>

<div class="card border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h5 class="card-title"><i class="fa-solid fa-fire"></i> ข้อมูลการรับโทษทางวินัย</h5>
            <?=Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/hr/employee-detail/create', 'emp_id' => $model->id, 'name' => 'blame', 'title' => '<i class="fa-solid fa-fire"></i> '.$this->title], ['class' => 'btn btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-lg']])?>
        </div>
        <div class="table-responsive">
            <table class="table table-striped mt-3" style="margin-bottom: 100px;">
                <thead>
                    <tr>
                        <th style="width: 32px;">#</th>
                        <th style="width:120px">วันที่ได้รับโทษ</th>
                        <th>วันที่สิ้นสุดโทษ</th>
                        <th class="align-middle">ประเภทความผิด</th>
                        <th class="align-middle">การลงโทษ</th>
                        <th class="text-center" style="width: 100px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($model->blames as $key => $item): ?>
                    <tr class="">
                        <td><?=$key+1?></td>
                        <td scope="row"><?=$item->date_start?></td>
                        <td scope="row"><?=$item->date_end?></td>
                        <td><?=$item->data_json['blame_type']?></td>
                        <td><?=$item->data_json['blame']?></td>
                        <td class="text-center align-middle">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false"><i class="bx bx-dots-vertical-rounded fw-bold"></i></button>
                                <div class="dropdown-menu">
                                    <?=Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>แก้ไข', ['/hr/employee-detail/update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-fire"></i> '.$this->title], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']])?>
                                    <?=Html::a('<i class="fa-solid fa-trash me-1"></i>ลบ', ['/hr/employee-detail/delete', 'id' => $item->id], ['class' => 'dropdown-item delete-item'])?>
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