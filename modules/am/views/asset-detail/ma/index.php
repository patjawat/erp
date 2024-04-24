<?php

use app\modules\am\models\AssetDetail;
use yii\helpers\Html;
use yii\widgets\Pjax;

?>
<?php Pjax::begin(['id' => 'am-container', 'enablePushState' => true, 'timeout' => 5000]);?>
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
                <?php $date = Yii::$app->formatter->asDate($item->date_start, 'long') ?>
                    <tr class="">
                        <td scope="row">
                            <?=Html::a('<i class="bi bi-calendar-event-fill text-primary fs-5" style="margin-right:5px;"></i>'.Yii::$app->formatter->asDate($item->date_start, 'long'),['/am/asset-detail/view-history-ma','name'=>'ma', "title"=>' <div style="display:flex; margin:0;padding:0; flex-direction: column; align-items: flex-end;">
    <span class="fw-semibold">
        <i class="bi bi-list-check text-primary fs-5"></i> ข้อมูลการบำรุงรักษา
        </span >
        <div style="margin:0;padding:0; font-size: 15px;">
            <i class="bi bi-calendar-check text-primary"></i><span class="fw-semibold"> วันที่ </span>'.$date. '<br>
        </div>
    </div>'
    ,"id"=>$item->id],['class' => 'dropdown-item open-modal','data' => ['size' => 'modal-lg']])?>
                        </td>
                        <td><?= isset($item->data_json["checker_name"]) ? $item->data_json["checker_name"] : '' ?></td>
                        <td><?= isset($item->data_json["endorsee_name"]) ? $item->data_json["endorsee_name"]  : '' ?></td>
                        <td><?= isset($item->data_json["status"]) ? $item->data_json["status"] : '' ?></td>
                        <td><?= $item->data_json["description"] ?></td>
                    </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php Pjax::end();?>