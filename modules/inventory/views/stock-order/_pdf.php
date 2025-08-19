<?php

use yii\helpers\Html;
?>
<h1 class="text-center">ใบเบิกวัสดุ</h1>
<div class="form-info">
    <table>
        <tr>
            <td style="width: 15%;"><strong>ชื่อหน่วยงาน</strong></td>
            <td style="width: 50%;">งานเวชระเบียนและข้อมูลทางการแพทย์</td>
        </tr>
    </table>
    <p>
        <strong>เลขที่ </strong>
        <?= $model->code ?>
    </p>
    <p class="mb-0 mt-0">
        <strong>เรียน</strong> ผู้อำนวยการโรงพยาบาลสมเด็จพระยุพราชด่านซ้าย
    </p>

    <p style="margin: 10px 0;">
        ด้วย งานเวชระเบียนและข้อมูลทางการแพทย์ มีความประสงค์ขอเบิกวัสดุ ใช้ในหน่วยงาน
    </p>

    <p><strong>มีรายการดังต่อไปนี้</strong></p>
</div>

<table class="" style="width:100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="width: 5%; text-align: center; border-bottom: 1px solid #000;">ที่</th>
            <th style="width: 45%; border-bottom: 1px solid #000;">รายการ</th>
            <th style="width: 8%; border-bottom: 1px solid #000;">หน่วย</th>
            <th style="width: 15%; text-align: center; border-bottom: 1px solid #000;">จำนวนเบิก</th>
            <th style="width: 8%; border-bottom: 1px solid #000;">อนุมัติ</th>
            <th style="width: 15%; text-align: right; border-bottom: 1px solid #000;">ราคา/หน่วย</th>
            <th style="width: 10%; text-align: right; border-bottom: 1px solid #000;">ราคารวม</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($model->getItems() as $key =>  $item): ?>
            <tr>
                <td style="text-align:center;"><?= $key + 1 ?></td>
                <td class="text-left"><?= $item->product?->title ?? '-' ?></td>
                <td><?=$item->product->data_json['unit']?></td>
                <td style="text-align:center;"><?=$item->data_json['req_qty']?></td>
                <td><?=$item->qty?></td>
             <td style="text-align: right; font-weight: bold;"><?=isset($item->unit_price) && is_numeric($item->unit_price) ? number_format($item->unit_price, 2) : '0.00'?></td>
<td style="text-align: right; font-weight: bold;"><?=$model->order_status == 'success' && isset($item->qty, $item->unit_price) && is_numeric($item->qty) && is_numeric($item->unit_price) ? number_format(($item->qty * $item->unit_price), 2) : '0.00'?></td>
            </tr>
        <?php endforeach; ?>
        <tr class="total-row">
            <td colspan="6" style="text-align: right; font-weight: bold;">ราคารวม</td>
            <td style="font-weight: bold;">0.00</td>
        </tr>
    </tbody>
</table>
<table class="table" style="width:100%; border-collapse: collapse;">
    <tr>
        <td style="text-align: center; vertical-align: middle; height: 150px;">
            <div>ลงชื่อ.................................................ผู้เบิก</div>
            <div>(น.ส.เมวิกา กาญจนะโกมล)</div>
            <div>ตำแหน่ง พนักงานบัตรรายงานโรค</div>
            <div>19 สิงหาคม 2568 13:25 น.</div>
        </td>
        <td style="text-align: center; vertical-align: middle; height: 150px;">
            <div>ลงชื่อ.................................................ผู้จ่ายพัสดุ</div>
            <div>(ไม่ระบุผู้จ่าย)</div>
            <div>ตำแหน่ง</div>
            <div>-</div>
        </td>
    </tr>

    <tr>
        <td style="text-align: center; vertical-align: middle; height: 150px;">
            <div>ลงชื่อ.................................................ผู้เห็นชอบ</div>
            <div>(น.ส.เมวิกา กาญจนะโกมล)</div>
            <div>ตำแหน่ง พนักงานบัตรรายงานโรค</div>
            <div>19 สิงหาคม 2568 13:25 น.</div>
        </td>
        <td style="text-align: center; vertical-align: middle; height: 150px;">
            <div>ลงชื่อ.................................................ผู้รับวัสดุ</div>
            <div>(ไม่ระบุผู้จ่าย)</div>
            <div>ตำแหน่ง</div>
            <div>-</div>
        </td>
    </tr>


        <tr>
        <td style="text-align: center; vertical-align: middle; height: 150px;">
            <div>ลงชื่อ.................................................ผู้สั่งจ่าย</div>
            <div>(น.ส.เมวิกา กาญจนะโกมล)</div>
            <div>ตำแหน่ง พนักงานบัตรรายงานโรค</div>
            <div>19 สิงหาคม 2568 13:25 น.</div>
        </td>
        <td style="text-align: center; vertical-align: middle; height: 150px;">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </td>
    </tr>

</table>
