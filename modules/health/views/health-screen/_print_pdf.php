<?php

use app\components\SiteHelper;
use yii\helpers\Html;
$info = SiteHelper::getInfo();
?>

<div style="text-align: center; font-weight: bold; font-size: 20pt; margin-bottom: 20px;">
    หนังสือรับรองการตรวจสุขภาพประจำปี
</div>
<div style="text-align: right;">เขียนที่ <?= $info['company_name'] ?? 'โรงพยาบาลสมเด็จพระยุพราชด่านซ้าย' ?></div>
<div style="text-align: center;">วันที่ <?= Yii::$app->formatter->asDate(date('Y-m-d'), 'long') ?></div>

<div style="margin-top: 30px; line-height: 1.8;">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ข้าพเจ้า <b><?= $model->employee->prefix . $model->employee->fullname ?></b> 
    อายุ <b><?= $model->employee->age ?></b> ปี 
    หน่วยงานต้นสังกัด <b><?= $model->employee->departmentName() ?></b><br>
    เลขบัตร <b><?= $model->employee->cid ?></b> 
    ที่อยู่ <b><?= $model->employee->address ?></b><br>
    ได้เข้ารับการตรวจสุขภาพประจำปี ที่ <b><?= $model->id ?></b> เมื่อวันที่ <?= Yii::$app->formatter->asDate($model->date_checkup, 'long') ?>
</div>

<div style="margin-top: 15px;">เป็นเงิน <b><?php echo   number_format($model->labTotalPrice(), 2) ?></b> บาท ตามรายการดังนี้</div>

<table class="content-table" style="width:100%">
    <thead>
        <tr>
            <th>รหัสรายการ</th>
            <th>รายการ</th>
            <th class="text-center">จำนวน</th>
            <th class="text-right">ราคาต่อหน่วย</th>
            <th class="text-right">ราคารวม</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        // สมมติว่ามี relation 'details' เก็บรายการตรวจ
        foreach ($model->labs as $item): ?>
        <tr>
            <td class="text-center"><?= $item->lab_code ?></td>
            <td><?= $item->lab->lab_name ?></td>
            <td class="text-center"><?= $item->qty ?></td>
            <td class="text-right"><?= number_format($item->lab_price, 2) ?></td>
            <td class="text-right"><?= number_format(($item->lab_price*$item->qty) ?? 0, 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr style="background-color: #fafafa; font-weight: bold;">
            <td colspan="4" class="text-center">รวมเป็นเงิน</td>
            <td class="text-right"><?php echo number_format($model->labTotalPrice(), 2) ?></td>
        </tr>
    </tfoot>
</table>

<div style="margin-top: 50px; text-align: center; float: right; width: 40%;">
    ลงชื่อ...............................................เจ้าหน้าที่ รพ.<br>
    ( <?= $model->employee->prefix . $model->employee->fullname ?> )<br>
    ตำแหน่ง <?= $model->employee->positionName() ?>
</div>