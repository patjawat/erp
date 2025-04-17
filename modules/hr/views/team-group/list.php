<?php
use yii\helpers\Html;
?>
<div class="table-responsive">
    <table class="table table-primary mb-5">
        <thead>
            <tr>
                <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                <th class=" fw-semibold" scope="col" style="width:80px">พ.ศ.</th>
                <th class=" fw-semibold" scope="col">รายการ</th>
                <th class=" fw-semibold" scope="col">กรรมการ</th>
                <th class=" fw-semibold" scope="col" style="width:130px">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody>
        <tbody class="align-middle table-group-divider">
            <?php foreach($dataProvider->getModels() as $key => $item):?>
            <tr>
                <td class="text-center fw-semibold"><?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
                <td>
                    <?= $item->teamGroupDetail()?->thai_year?>
                </td>
                <td><?=$item->title;?></td>
                <td>
                    <?= $item->teamGroupDetail()?->stackComittee()?>
            </td>
                <td>
                    <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                        <?=Html::a('<i class="fa-solid fa-eye"></i>',['view','id' => $item->id],['class' => 'btn btn-light'])?>
                        <?=Html::a('<i class="fa-solid fa-pen-to-square"></i>',['update','id' => $item->id,'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไข'],['class' => 'btn btn-light open-modal','data' => ['size' => 'modal-md']])?>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-sort-down"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><?=Html::a('<i class="fa-solid fa-trash-can me-1"></i> ลบข้อมูล',['delete','id' => $item->id],['class' => 'dropdown-item delete-item'])?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>
</div>