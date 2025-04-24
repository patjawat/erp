
<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
$title = '<i class="fa-solid fa-person-walking-luggage"></i> ทะเบียนอบรม/ประชุม/ดูงาน';
?>
<?php Pjax::begin(['id' => 'scholarships']);?>

<div class="card border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h5 class="card-title"><?=$title;?></h5>
            <?php // Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/hr/employee-detail/create', 'emp_id' => $model->id, 'name' => 'develop', 'title' => $title], ['class' => 'btn btn-primary rounded-pill open-modal shadow', 'data' => ['size' => 'modal-lg']])?>
        </div>
       

        <div class="table-responsive pb-5">
            <table class="table table-primary">
                <thead>
                    <tr>
                        <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                        <th class="text-center fw-semibold" style="width:30px">ปีงบประมาณ</th>

                        <th scope="col">ประเภท/เรื่อง</th>
                        <th scope="col">วัน/สถานที่</th>
                        <th scope="col">คณะเดินทาง</th>
                        <!-- <th class="fw-semibold text-center">ดำเนินการ</th> -->
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($model->developmentMenber as $key => $item): ?>
                    <tr>
                        <td class="text-center fw-semibold">
                            <?php echo $key+1 ?>
                        </td>
                        <td><?=$item->development->thai_year;?></td>
                        <td>
                            <div>
                                <p class="text-muted mb-0 fs-13"><?=$item->development->data_json['development_type_name'] ?? '-'?>
                                </p>
                                <p class="fw-semibold mb-0"><?=$item->development->topic?></p>
                            </div>

                        </td>
                        <td>
                            <div>
                                <p class="text-muted mb-0 fs-13"><?=$item->development->showDateRange()?></p>
                                <p class="fw-semibold mb-0"><?=$item->development->data_json['location'] ?? '-'?></p>
                            </div>
                        </td>
                        <td>  <?=$item->development->StackMember()?></td>
                        <!-- <td style="width:120px">
                            <div class="btn-group">
                                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['update','id' => $item->development->id,'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไข'], ['class' => 'btn btn-light w-100 open-modal','data' => ['size' => 'modal-xl']]) ?>
                                <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                    <i class="bi bi-caret-down-fill"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><?= Html::a('<i class="fa-solid fa-eye me-1"></i> แสดงรายละเอียด', ['view','id' => $item->development->id], ['class' => 'dropdown-item']) ?></li>
                                    </ui>
                            </div>
                        </td> -->
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php Pjax::end();?>