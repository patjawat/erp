<?php

use app\components\AppHelper;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\bootstrap5\LinkPager;
use app\modules\hr\models\EmployeeDetail;

$items = [
    ['name' => 'position_name', 'title' => 'กำหนดตำแหน่ง'],
    ['name' => 'position_type', 'title' => 'ตั้งค่าตำแหน่ง'],
    ['name' => 'position_level', 'title' => 'ระดับตำแหน่ง'],
    ['name' => 'position_manage', 'title' => 'ตำแหน่งบริหาร'],
    ['name' => 'position_group', 'title' => 'ประเภท/กลุ่มงาน'],
    ['name' => 'expertise', 'title' => 'ความเชี่ยวชาญ'],
];
?>

<style>
.dropdown-toggle::after {
    display: none;
}

.dropdown-item {
    background-color: #ffff;
}
</style>

<?php  Pjax::begin(['id' => 'position']);?>

<div class="card border-0">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h5 class="card-title"><i class="fa-solid fa-people-group"></i> ตำแหน่ง</h5>
            <div class="btn-group dropup">
                <?=Html::a('<i class="fa-solid fa-circle-plus"></i> สร้างใหม่', ['/hr/employee-detail/create', 'emp_id' => $model->id, 'name' => 'position', 'title' => '<i class="fa-solid fa-user-tag"></i> ตำแหน่ง'], ['class' => 'btn btn-primary rounded-start-pill shadow open-modal', 'data' => ['size' => 'modal-lg']])?>
                <button type="button" class="btn btn-secondary dropdown-toggle dropdown-toggle-split"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="visually-hidden"><i class="fa-solid fa-people-group"></i>Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu" style="">
                    <?php foreach ($items as $item): ?>
                    <li><?=Html::a('<i class="fa-regular fa-circle-check me-1"></i>' . $item['title'], ['/hr/categorise', 'name' => $item['name'], 'title' => $item['title']], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']])?>
                    </li>
                    <?php endforeach;?>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                </ul>
            </div>
        </div>
        <div class="table-responsive" style="min-height:468px">
            <table class="table table-striped mt-4">
                <thead>
                    <tr>
                        <th scope="col">ตั้งแต่วันที่</th>
                        <th scope="col">รายการเปลี่ยนแปลง</th>
                        <!-- <th scope="col" class="align-middle">เงินเดือน</th>
                        <th scope="col" class="align-middle">เอกสารอ้างอิง</th> -->
                        <th class="text-center" style="width: 100px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dataProvider->getModels() as $item): ?>
                    <tr class="">
                        <td class="align-middle">
                            <div class="d-flex flex-column">
                                <div>

                                    <i class="bi bi-calendar-event"></i>
                                    <?=isset($item->data_json['date_start']) ? AppHelper::DateFormDb($item->data_json['date_start']) : ''?>
                                </div>
                                <div>
                                    <?php if($item->status == 2):?>
                                    <label
                                        class="badge rounded-pill text-primary-emphasis bg-danger-subtle p-2 fs-6 text-truncate float-start">
                                        <i class="bi bi-clipboard-check"></i> <?php echo $item->GetStatusName()?>

                                    </label>
                                    <?php else:?>
                                    <label
                                        class="badge rounded-pill text-primary-emphasis bg-success-subtle p-2 fs-6 text-truncate float-start">
                                        <i class="bi bi-clipboard-check"></i> <?php echo $item->GetStatusName()?>

                                    </label>
                                    <?php endif;?>
                                </div>

                            </div>
                        </td>
                        <td class="align-middle">
                            <div class="d-flex flex-column">
                                <div class="fw-normal">
                                    <i class="bi bi-check2-circle text-primary"></i>
                                    <span
                                        class="text-danger"><?=isset($item->data_json['statuslist']) ? $item->data_json['statuslist'] : null?></span>
                                </div>
                                <div>
                                    <i class="bi bi-check2-circle text-primary"></i> <span
                                        class="fw-semibold">เอกสารอ้างอิง</span> : <span
                                        class="text-primary"><?=isset($item->data_json['doc_ref']) ?  $item->data_json['doc_ref'] : null?></span>
                                </div>
                                <div>

                                    <div class="d-flex flex-row gap-3">

                                        <div>
                                            <i class="bi bi-check2-circle text-primary"></i>

                                            <span class="fw-semibold"><?=$item->positionName()?>
                                                |<?=$item->positionGroupName()?> | <?=$item->positionTypeName()?> |
                                                ตำแหน่งเลขที่</span> : <span
                                                class="badge rounded-pill text-primary-emphasis bg-warning"><?=isset($item->data_json['position_number']) ? $item->data_json['position_number'] : null?></span>
                                        </div>

                                        <div>
                                            <i class="bi bi-wallet2"></i> เงินเดือน :
                                            <code class="">
                                                <?php echo $item->salary?>
                                                <?php // number_format($item->salary);?> </code>
                                            บาท
                                        </div>

                                    </div>
                                </div>


                            </div>
                        </td>


                        <td class="align-middle text-center ">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false"><i
                                        class="bx bx-dots-vertical-rounded fw-bold"></i></button>
                                <div class="dropdown-menu" style="">
                                    <?=Html::a('<i class="bx bx-edit-alt me-1"></i>แก้ไข', ['/hr/employee-detail/update', 'id' => $item->id, 'title' => '<i class="fa-solid fa-user-tag"></i> ตำแหน่ง'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-lg']])?>
                                    <?=Html::a('<i class="bi bi-trash"></i>ลบ', ['/hr/employee-detail/delete', 'id' => $item->id], ['class' => 'dropdown-item delete-item'])?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center">

            <div class="text-muted">
                <?= LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'firstPageLabel' => 'หน้าแรก',
                    'lastPageLabel' => 'หน้าสุดท้าย',
                    'options' => [
                        'listOptions' => 'pagination pagination-sm',
                        'class' => 'pagination-sm',
                    ],
                ]); ?>
            </div>
        </div>


    </div>
</div>

<?php  Pjax::end();?>