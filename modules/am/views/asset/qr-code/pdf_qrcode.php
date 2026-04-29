<?php

use yii\helpers\Html;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

$code = $model->code;
$result = Builder::create()
        ->writer(new PngWriter())
        ->data($code)
        ->size(300)
        ->build();
$base64 = base64_encode($result->getString());

?>

<table style="width: 100%; border: 1px solid #ccc; padding: 10px; border-radius: 8px;">
    <tr>
        <td style="width: 150px; vertical-align: top;">
            <img src="data:image/png;base64,<?= $base64 ?>" width="200">
        </td>
        <td style="vertical-align: top;">
            <table style="width: 100%; border-collapse: collapse; line-height: 1.5;">
                <tr>
                    <td style="text-align: right; width: 30%; padding-right: 8px; vertical-align: top;">หมายเลข :</td>
                    <td style="text-align: left; vertical-align: top;"><strong><?= $model->code ?></strong></td>
                </tr>
                <tr>
                    <td style="text-align: right; padding-right: 8px; vertical-align: top;">รายการ :</td>
                    <td style="text-align: left; vertical-align: top;"><strong><?= $model->asset_name ?></strong></td>
                </tr>
                <tr>
                    <td style="text-align: right; padding-right: 8px; vertical-align: top;">ประเภทเงิน :</td>
                    <td style="text-align: left; vertical-align: top;"><strong><?= $model->budgetTypeName() ?></strong></td>
                </tr>
                <tr>
                    <td style="text-align: right; padding-right: 8px; vertical-align: top;">วิธีการได้มา :</td>
                    <td style="text-align: left; vertical-align: top;"><strong><?= $model->methodGetName() ?></strong></td>
                </tr>
                <tr>
                    <td style="text-align: right; padding-right: 8px; vertical-align: top;">หน่วยงาน :</td>
                    <td style="text-align: left; vertical-align: top;"><strong><?= $model->departmentName() ?></strong></td>
                </tr>
                <tr>
                    <td style="text-align: right; padding-right: 8px; vertical-align: top;">สถานที่ :</td>
                    <td style="text-align: left; vertical-align: top;"><strong><?= $model->data_json['location'] ?? '-' ?></strong></td>
                </tr>
                <tr>
                    <td style="text-align: right; padding-right: 8px; vertical-align: top;">สถานะ :</td>
                    <td style="text-align: left; vertical-align: top;"><strong><?= $model->statusName() ?></strong></td>
                </tr>
                <tr>
                    <td style="text-align: right; padding-right: 8px; vertical-align: top;">ราคา :</td>
                    <td style="text-align: left; vertical-align: top;"><strong><?= number_format($model->price, 2) ?> บาท</strong></td>
                </tr>
                <tr>
                    <td style="text-align: right; padding-right: 8px; vertical-align: top;">วันที่ได้มา :</td>
                    <td style="text-align: left; vertical-align: top;"><strong><?= $model->viewReceiveDate()?></strong></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
