<?php
use yii\helpers\Html;
?>
<div
    class="table-responsive"
>
    <table
        class="table table-primary"
    >
        <thead>
            <tr>
                <th class="fw-semibold" scope="col">ลำดับ</th>
                <th class="fw-semibold" scope="col">ชื่อ-นามสกุล</th>
                <th class="fw-semibold text-center" scope="col">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($model->committee as  $key => $item):?>
            <tr class="">
                <td scope="row"><?=$key+1?></td>
                <td><?=$item->employee->getAvatar(false,$item->data_json['committee_name'])?></td>
                <td class="text-center">
                            <div class="btn-group">
                                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/dms/committee/update','id' => $item->id], ['class' => 'btn btn-light w-100 open-modal','data' => ['size' => 'modal-md']]) ?>
                                <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                                    data-bs-toggle="dropdown" aria-expanded="false" data-bs-reference="parent">
                                    <i class="bi bi-caret-down-fill"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li> <?=Html::a('<i class="fa-solid fa-trash-can text-danger me-1"></i> ลบ',['/dms/committee/delete','id' => $item->id],['class' => 'o dropdown-item delete-item','data' => ['size' => 'modal-xl']])?></li>
                                    </li>
                                </ul>
                            </div>

                        </td>
            </tr>
          <?php endforeach?>
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-center">
    <?=Html::a('<i class="fa-solid fa-circle-plus"></i> เพิ่มคณะกรรมการ',['/dms/committee/create','id' => $model->id, 'name' => 'committee', 'title' => '<i class="fa-regular fa-pen-to-square"></i> กรรมการ'],['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-md']])?>
</div>
