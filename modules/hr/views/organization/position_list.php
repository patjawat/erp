<?php
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\modules\hr\models\Employees;
use app\models\Categorise;
$items = Categorise::find()->where(['name' => 'position_name','category_id' => $model->code])->all() ;
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-middle p-3">
        <h6 class="card-title">ตำแหน่งในสายงาน <?=count($items)?> รายการ</h6>
        <div>

            <?=app\components\AppHelper::Btn([
                'title' => '<i class="fa-solid fa-circle-plus"></i> เพิ่มตำแหน่ง',
                'url' => ['create','name' => 'position_name','code' => $model->code],
                'modal' => true,
                'size' => 'md',
                ])?>
        </div>
    </div>
    <div class="card-body p-0">

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ชื่อสายงาน</th>
                    <th>ชื่อในตำแหนงสายงาน</th>
                    <th class="text-center">ดำเนินการ</th>
                </tr>

            </thead>
            <tbody>
                <?php foreach($items as $item):?>
                <tr>
                    <td>
                    <i class="bi bi-check2-circle text-primary"></i> <?=$item->title;?>
                    </td>
                    <td> <?=isset($item->data_json['sub_title'])? $item->data_json['sub_title'] : '-'?></td>
                    <td>

                    <div class="dropdown text-center">
                            <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-ellipsis-vertical"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" style="">
                            <?=app\components\AppHelper::Btn([
                                'title' => '<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไข',
                                'url' => ['update','id' => $item->id],
                                'modal' => true,
                                'size' => 'md',
                                'class' => 'dropdown-item open-modal'
                                ])?>
                               
                               <?=Html::a('<i class="bx bx-trash me-1"></i>ลบ', ['/hr/organization/delete', 'id' => $item->id], [
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