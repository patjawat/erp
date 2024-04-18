
<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use app\components\AppHelper;
$title = '<i class="fa-regular fa-id-badge"></i> ใบอนุญาต';
?>
<?php Pjax::begin(['id' => 'license_name']);?>

<div class="card border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h5 class="card-title"><?=$title?></h5>
            <?=Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/hr/employee-detail/create', 'emp_id' => $model->id, 'name' => 'license_name', 'title' => $title], ['class' => 'btn btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-lg']])?>
        </div>
        <div class="table-responsive"  style="min-height:468px">
            <table class="table mt-4">
                <thead>
                    <tr>
                    <th scope="col">ตั้งแต่วันที่</th>
                        <th scope="col">ถึงวันที่</th>
                        <th scope="col">ใบอนุญาต</th>
                        <th scope="col" class="align-middle">เอกสารอ้างอิง</th>
                        <th scope="col" class="align-middle">หน่วยงานผู้ออก</th>
                        <th class="text-center" style="width: 100px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($model->licenses as $item): ?>
                    <tr class="">
                        <td scope="row">
                            <?=isset($item->data_json['date_start']) ? AppHelper::DateFormDb($item->data_json['date_start']) : ''?>
                        </td>
                        <td scope="row">
                            <?=isset($item->data_json['date_end']) ? AppHelper::DateFormDb($item->data_json['date_end']) : ''?>
                        </td>
                        <td><?=isset($item->data_json['license_name']) ?  $item->data_json['license_name'] : null?></td>
                        <td><?=isset($item->data_json['doc_ref']) ?  $item->data_json['doc_ref'] : null?></td>
                        <td><?=isset($item->data_json['license_company']) ?  $item->data_json['license_company'] : null?></td>
                        <td class="text-center align-middle">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false"><i
                                        class="bx bx-dots-vertical-rounded fw-bold"></i></button>
                                <div class="dropdown-menu" style="">
                                    <??>
                                    <?=Html::a('<i class="bx bx-edit-alt me-1"></i>แก้ไข', ['/hr/employee-detail/update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-user-tag"></i> ตำแหน่ง'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']])?>

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