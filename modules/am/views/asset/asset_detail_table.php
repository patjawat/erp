<?php
use yii\helpers\Html;
?>



<table class="table table-striped-columns">
    <tbody>
        <tr class="">
            <td class="text-end"><span class="fw-semibold">ชื่อครุภัณฑ์ : </span></td>
            <td colspan="3">
                <?=(isset($model->data_json['asset_name']) ? $model->data_json['asset_name'] : '-')?>
            </td>
            <td class="text-end"><span class="fw-semibold"> ประเภท : </span></td>
            <td colspan="3">
                <?=isset($model->data_json['asset_type_text']) ? $model->data_json['asset_type_text'] : '-'?>
            </td>
        </tr>
        <tr>
            <td class="text-end"><span class="fw-semibold">เลขครุภัณฑ์ : </span></td>
            <td colspan="3"><code><?=$model->code?></code></td>
            <td class="text-end"><span class="fw-semibold"> มูลค่า : </span></td>
            <td colspan="3">
                <span class="text-white bg-primary badge rounded-pill fs-6"><?=number_format($model->price, 2)?></span>
                บาท
            </td>
        </tr>
        <tr class="">
            <td class="text-end"><span class="fw-semibold">วันเดือนปีทีซื้อ : </span></td>
            <td colspan="3"> <?=Yii::$app->thaiFormatter->asDate($model->receive_date, 'medium')?>
            </td>
            <td class="text-end"><span class="fw-semibold">การจัดซื้อ : </span></td>
            <td colspan="3"><?=$model->purchase_text?></td>
        </tr>
        <tr class="">
            <td class="text-end"><span class="fw-semibold">ประจำหน่วยงาน : </span></td>
            <td colspan="3">
                <?=isset($model->data_json['department_name']) ? $model->data_json['department_name'] : ''?>
            </td>
            <td class="text-end"><span class="fw-semibold">วิธีได้มา : </span></td>
            <td colspan="3"><?=$model->method_get?></td>
        </tr>
        <tr class="">
            <td class="text-end"><span class="fw-semibold">ประเภทเงิน : </span></td>
            <td colspan="3">
                <?=$model->budget_type?>
            </td>
            <td class="text-end"><span class="fw-semibold">ผู้ขาย/ผู้จำหน่าย/ผู้บริจาค : </span></td>
            <td colspan="3"><?=$model->vendor_name?></td>
        </tr>
        <tr class="">
            <td class="text-end"><span class="fw-semibold">สถานะ : </span></td>
            <td colspan="3">
                <?=$model->statusName()?>
            </td>
            <td class="text-end"><span class="fw-semibold">ผู้รับผิดชอบ : </span></td>
            <td colspan="3"><?=$model->vendor_name?></td>
        </tr>
        </tr>
        <tr class="">
            <td class="text-end"><span class="fw-semibold">อาการแจ้งซ่อม : </span></td>
            <td colspan="3">
                <span class="text-danger"><?=$repair->data_json['title']?></span>
            </td>
            <td class="text-end"><span class="fw-semibold">สภานะงานซ่อม : </span></td>
            <td colspan="3">
                <?php if($repair->data_json['repair_status'] == 'ร้องขอ'):?>
                    <label class="badge rounded-pill text-danger-emphasis bg-danger-subtle p-2 text-truncate">
                                <i class="fa-regular fa-hourglass-half"></i> ร้องขอ</label>
                                <?php else:?>
                                    <?=$repair->data_json['repair_status']?>
                    <?php endif;?>
            </td>
        </tr>
        <tr class="">
            <td class="text-end"><span class="fw-semibold">ข้อมูลเพิ่มเติม : </span></td>
            <td colspan="5">
                <span class="text-danger">
                    <?=$repair->data_json['note']?>
                </span>
            </td>
        </tr>
    </tbody>
</table>
