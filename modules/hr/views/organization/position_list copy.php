<?php
use yii\helpers\Html;
use app\modules\hr\models\Employees;
?>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <!-- <h5 class="card-title">บุคลากร <?php // count($model->EmpOnWorkGroup($model->code))?> คน</h5> -->
        <h5 class="card-title">ตำแหน่งในสายงาน</h5>
        <div>

            <?=app\components\AppHelper::Btn([
                'title' => 'เพิ่ม',
                'url' => ['create'],
                'modal' => true,
                'size' => 'lg',
                ])?>
</div>
    </div>
    <div class="card-body p-0">

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ชื่อ-นามสกุล</th>
                <th>อายุงาน</th>
                </tr>
                
            </thead>
            <tbody>
                <?php foreach($model->EmpOnWorkGroup($model->code) as  $key => $avatar):?>
                    <tr>
                        <td>
                            <?= Employees::findOne(['id' => $avatar['id']])->getAvatar();?>
                            
                        </td>
                        <td>1 ปี</td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
        </table>
        <div class="text-center">
            <a href="javascript:void(0);" class="btn btn-sm btn-light">
                <i class="bx bx-loader me-1 align-middle bx-spin"></i>
                <span class="align-middle">Load more</span>
            </a>
        </div>
    </div>
</div>