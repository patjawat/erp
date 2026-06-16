<?php

use app\components\SiteHelper;
use app\components\ThaiDateHelper;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use yii\helpers\Html;
$site = SiteHelper::getInfo();

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
                    <td style="text-align: left; vertical-align: top; font-size: 35px;"><strong><?= $model->code ?></strong></td>
                </tr>
                <tr>
                    <td style="text-align: left; vertical-align: top;font-size: 20px;"><strong><?= $model->asset_name ?></strong></td>
                </tr>
                <tr>
                    <td style="text-align: left; vertical-align: top;font-size: 20px;"><strong><?= $model->departmentName() ?></strong></td>
                </tr>
                <tr>
                    <td style="text-align: left; vertical-align: top;font-size: 20px;"><strong><?= number_format($model->price, 2) ?> :: <?= ThaiDateHelper::formatThaiDate($model->receive_date,'numeric')?></strong></td>
                </tr>
                <tr>
                    <td style="text-align: left; vertical-align: top;font-size: 20px;"><strong><?= $site['company_name']?></strong></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
