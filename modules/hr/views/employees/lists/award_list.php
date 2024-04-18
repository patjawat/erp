<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;
?>
<?php Pjax::begin(['id' => 'award']);?>

<div class="card border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h5 class="card-title"><i class="fa-solid fa-award"></i> รางวัลเชิดชูเกียรติ</h5>
            <?=Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/hr/employee-detail/create', 'emp_id' => $model->id, 'name' => 'award', 'title' => '<i class="fa-solid fa-award"></i> รางวัลเชิดชูเกียรติ'], ['class' => 'btn btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-md']])?>
        </div>
        <div class="table-responsive">
            <table class="table table-striped mt-3" style="margin-bottom: 100px;">
                <thead>
                    <tr>
                        <th style="width: 32px;">#</th>
                        <th style="width:120px">วันที่เสนอ</th>
                        <th>วันที่ได้รับ</th>
                        <th class="align-middle">รายการ</th>
                        <th class="align-middle">หน่วยงานที่มอบ</th>
                        <th class="text-center" style="width: 100px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($model->awards as $key => $item): ?>
                    <tr class="">
                        <td><?=$key+1?></td>
                        <td scope="row">
                            <?=isset($item->data_json['date_start']) ? AppHelper::DateFormDb($item->data_json['date_start']) : ''?>
                        </td>
                        <td scope="row">
                            <?=isset($item->data_json['date_end']) ? AppHelper::DateFormDb($item->data_json['date_end']) : ''?>
                        </td>
                        <td>
                            <?=$item->award_company_name?>
                        </td>
                        <td>
                            <?=$item->award_company_name?>
                        </td>
                        <td class="text-center align-middle">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false"><i
                                        class="bx bx-dots-vertical-rounded fw-bold"></i></button>
                                <div class="dropdown-menu" style="">
                                    <??>
                                    <?=Html::a('<i class="bx bx-edit-alt me-1"></i>แก้ไข', ['/hr/employee-detail/update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-user-tag"></i> การศึกษา'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']])?>

                                    <?=Html::a('<i class="bx bx-trash me-1"></i>ลบ', ['/hr/employee-detail/delete', 'id' => $item->id], [
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