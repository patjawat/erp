<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\TeamGroupDetail $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Team Group Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-people-fill fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>

<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('@app/modules/hr/views/employees/menu') ?>
<?php $this->endBlock(); ?>


<div class="card">

    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6>คณะกรรมการ<?=$model->teamGroup->title;?> ปี <?=$model->thai_year?></h6>
            <?=Html::a('เพิ่มรายการ', ['create-committee','name' => 'committee','category_id' => $model->id,'document_id' => $model->document_id,'thai_year' => $model->thai_year],['class' => 'btn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-md']])?>
        </div>
    
<div
    class="table-responsive"
>
    <table
        class="table table-primary mb-5"
    >
        <thead>
            <tr>
            <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                <th class=" fw-semibold" scope="col">รายกชื่อ-นามสกุลาร</th>
                <th class=" fw-semibold" scope="col">ตำแหน่ง</th>
                <th class=" fw-semibold" scope="col" style="width:130px">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($model->listCommittee() as $key => $item):?>
            <tr class="">
                <td scope="row"><?=$key+1?></td>
                <td><?=$item->employee->getAvatar(false);?></td>
                <td><?=$item->data_json['committee_name'] ?? '-';?></td>
                <td>
                    <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                        <?=Html::a('<i class="fa-solid fa-pen-to-square"></i>',['update-committee','id' => $item->id,'title' => '<i class="fa-solid fa-pen-to-square"></i> แก้ไข'],['class' => 'btn btn-light open-modal','data' => ['size' => 'modal-md']])?>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fa-solid fa-sort-down"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><?=Html::a('<i class="fa-solid fa-trash-can me-1"></i> ลบข้อมูล',['delete-committee','id' => $item->id],['class' => 'dropdown-item delete-item'])?>
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
</div>


</div>


</div>
<?php
$js = <<<JS

JS;
$this->registerJs($js);
?>