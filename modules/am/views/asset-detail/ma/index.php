<?php

use app\modules\am\models\AssetDetail;
use yii\helpers\Html;

?>
<?php $list = AssetDetail::find()->where(['code'=>$code])->orderBy(['date_start' => SORT_DESC])->all() ?>
<div class="d-flex justify-content-between">
    <h3><i class="fa-solid fa-brush"></i> การบำรุงรักษา</h3>
</div>
<div
    class="table-responsive"
>
    <table
        class="table table-primary"
    >
        <thead class="table-secondary">
            <tr>
                <th scope="col">วันที่</th>
                <th scope="col">ผู้ตรวจเช็คอุปกรณ์</th>
                <th scope="col">หัวหน้ารับรอง</th>
                <th scope="col">ผลการตรวจ</th>
                <th scope="col">หมายเหตุ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($list as $item){ ?>
                    <tr class="">
                        <td scope="row">
                            <?=Html::a('<i class="bi bi-calendar-event-fill text-primary fs-5" style="margin-right:5px;"></i>'.Yii::$app->formatter->asDate($item->date_start, 'long'),['/am/asset-detail/view-history-ma','name'=>'ma', "title"=>"ประวัติการบำรุงรักษา","id"=>$item->id],['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-lg']])?>
                        </td>
                        <td><?= $item->data_json["checker"] ?></td>
                        <td><?= $item->data_json["endorsee"] ?></td>
                        <td><?= $item->data_json["status"] ?></td>
                        <td><?= $item->data_json["description"] ?></td>
                    </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
