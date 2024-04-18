
<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
$items = [
    ['name' => 'education','title' => 'ระดับการศึกษา'],
];
?>
<style>

.dropdown-item {
    background-color: #ffff;
}
</style>
<?php Pjax::begin(['id' => 'education']);?>

<div class="card border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between">

            <h5 class="card-title"><i class="fa-solid fa-user-tag"></i> การศึกษา</h5>
            <div class="btn-group dropup">
                <?= Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/hr/employee-detail/create', 'emp_id' => $model->id, 'name' => 'education', 'title' => '<i class="fa-solid fa-user-tag"></i> การศึกษา'], ['class' => 'btn btn-primary rounded-start-pill shadow open-modal', 'data' => ['size' => 'modal-lg']])?>
                <button type="button" class="btn btn-secondary dropdown-toggle dropdown-toggle-split"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="visually-hidden"><i class="fa-solid fa-people-group"></i>Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu" style="">
                    <?php foreach($items  as $item):?>
                    <li><?=Html::a('<i class="fa-regular fa-pen-to-square text-primary me-1"></i>'.$item['title'],['/hr/categorise','name' =>  $item['name'],'title' => $item['title']],['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-lg']])?>
                    </li>
                    <?php endforeach;?>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                </ul>
            </div>

            <?php // Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/hr/employee-detail/create', 'emp_id' => $model->id, 'name' => 'education', 'title' => '<i class="fa-solid fa-user-tag"></i> การศึกษา'], ['class' => 'btn btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-lg']])?>
        </div>
        <div class="table-responsive">
            <table class="table table-striped" style="margin-bottom: 100px;">
                <thead>
                    <tr>
                        <th scope="col">ระดับการศึกษา</th>
                        <th scope="col">สำเร็จจากสถาบัน</th>
                        <th class="text-center" style="width: 100px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($model->educations as $item): ?>
                    <tr class="">
                        <td scope="row">
                            <div class=" d-flex flex-column">
                                <span>
                                    <?=isset($item->data_json['education_name']) ? $item->data_json['education_name'] : '-';?>
                                </span>
                                <span>
                                    <?= $item->major;?>
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span>
                                    <?=$item->institute;?>
                                </span>
                                <span>
                                    <?=$item->rangYearText()?>
                                </span>
                        </td>
                        <td class="text-center align-middle">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false"><i
                                        class="bx bx-dots-vertical-rounded fw-bold"></i></button>
                                <div class="dropdown-menu" style="">
                                    <??>
                                    <?=Html::a('<i class="bx bx-edit-alt me-1"></i>แก้ไข', ['/hr/employee-detail/update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-user-tag"></i> การศึกษา'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']])?>

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