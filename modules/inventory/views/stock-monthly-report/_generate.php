<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\select2\Select2;

/** @var yii\web\View $this */
/** @var \app\modules\inventory\models\StockMonthlyReportSearch $searchModel */
/** @var array $monthOptions */
/** @var array $yearOptions */
/** @var array $warehouseOptions */
/** @var array $assetTypeOptions */

$defaultYear      = (int) ($searchModel->report_year ?: date('Y'));
$defaultMonth     = (int) ($searchModel->report_month ?: date('n'));
$defaultWarehouse = $searchModel->warehouse_id;
$defaultAssetType = $searchModel->category_id;
?>

<form method="post" action="<?= Url::to(['stock-monthly-report/generate']) ?>" id="form-generate-monthly">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

    <div class="row g-2">
        <div class="col-12 col-md-3">
            <label class="form-label">ปี (ค.ศ.)</label>
            <?= Select2::widget([
                'name' => 'report_year',
                'value' => $defaultYear,
                'data' => $yearOptions,
                'options' => ['placeholder' => 'ปี'],
            ]) ?>
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label">เดือน</label>
            <?= Select2::widget([
                'name' => 'report_month',
                'value' => $defaultMonth,
                'data' => $monthOptions,
                'options' => ['placeholder' => 'เดือน'],
            ]) ?>
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label">คลังหลัก</label>
            <?= Select2::widget([
                'name' => 'warehouse_id',
                'value' => $defaultWarehouse,
                'data' => $warehouseOptions,
                'options' => ['placeholder' => 'ทุกคลังหลัก (MAIN)'],
                'pluginOptions' => ['allowClear' => true],
            ]) ?>
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label">ประเภทวัสดุ</label>
            <?= Select2::widget([
                'name' => 'asset_type_id',
                'value' => $defaultAssetType,
                'data' => $assetTypeOptions,
                'options' => ['placeholder' => 'ทุกประเภทวัสดุ'],
                'pluginOptions' => ['allowClear' => true],
            ]) ?>
        </div>
    </div>

    <div class="alert alert-info mt-3 mb-3 small">
        <i class="fa-solid fa-circle-info"></i>
        <strong>วิธีคำนวณ (Auto-bootstrap):</strong><br>
        <strong>ยอดยกมา (opening)</strong> = <code>closing</code> ของเดือนก่อนในตาราง
        <code>stock_monthly_report</code> — ถ้าเดือนก่อนยังไม่มี (เริ่มใช้ระบบครั้งแรก)
        ระบบจะคำนวณจาก <code>stock_events</code> ที่ <code>movement_date &lt; วันที่ 1 ของเดือน</code> ให้อัตโนมัติ<br>
        <strong>รับเข้า / จ่ายออก</strong> = aggregate จาก <code>stock_events</code> ระหว่างเดือนนี้
        (วิธีเดียวกับ <a href="<?= Url::to(['/inventory/export-stock']) ?>" target="_blank"><code>/inventory/export-stock</code></a>) —
        จ่ายแยก <code>out_hosp_qty</code> (SUB = โรงพยาบาล) /
        <code>out_sub_qty</code> (BRANCH = รพ.สต.)<br>
        <strong>คงเหลือ (closing)</strong> = opening + in − total_out
        (chain ต่อเนื่องกับเดือนถัดไป)<br>
        รายการที่ "ไม่มี activity เดือนนี้ แต่มี closing > 0 จากเดือนก่อน" จะถูก carry over ให้อัตโนมัติ<br>
        <strong>หมายเหตุ:</strong> รายการที่ไม่ตรงกับ <code>stock_item.item_code</code>
        ของ V2 จะถูกข้าม (เพราะมี FK)
    </div>

    <div class="d-flex flex-wrap gap-2">
        <?= Html::submitButton('<i class="fa-solid fa-bolt"></i> สรุปข้อมูลเดือนนี้', [
            'class' => 'btn btn-primary',
            'data-confirm' => 'ระบบจะลบข้อมูลสรุปเดิมของเดือนนี้และคำนวณใหม่ ยืนยันหรือไม่?',
            'data-method' => 'post',
        ]) ?>

        <button type="button" class="btn btn-outline-danger" id="btn-delete-month">
            <i class="fa-solid fa-trash"></i> ลบข้อมูลเดือนนี้
        </button>
    </div>
</form>

<form method="post" action="<?= Url::to(['stock-monthly-report/delete-month']) ?>" id="form-delete-monthly" class="d-none">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
    <input type="hidden" name="report_year" value="<?= Html::encode($defaultYear) ?>">
    <input type="hidden" name="report_month" value="<?= Html::encode($defaultMonth) ?>">
    <input type="hidden" name="warehouse_id" value="<?= Html::encode($defaultWarehouse) ?>">
</form>

<?php
$js = <<<JS
$('#btn-delete-month').on('click', function () {
    var year  = $('#form-generate-monthly select[name="report_year"]').val();
    var month = $('#form-generate-monthly select[name="report_month"]').val();
    var wh    = $('#form-generate-monthly select[name="warehouse_id"]').val();
    if (!year || !month) {
        alert('กรุณาเลือกปีและเดือน');
        return;
    }
    if (!confirm('ยืนยันการลบข้อมูลสรุปของเดือนที่เลือก?')) return;
    var form = $('#form-delete-monthly');
    form.find('input[name="report_year"]').val(year);
    form.find('input[name="report_month"]').val(month);
    form.find('input[name="warehouse_id"]').val(wh || '');
    form.trigger('submit');
});
JS;
$this->registerJs($js);
?>
