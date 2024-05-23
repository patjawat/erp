<?php
use app\components\AppHelper;
use app\modules\am\models\AssetDetail;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\hr\models\Employees;
use yii\helpers\Html;

$repair = Helpdesk::findOne(['code' => $model->code]);
$modelCar = AssetDetail::find()->where(['name' => 'tax_car', 'code' => $model->code])->orderBy(['date_start' => SORT_DESC])->one();
?>

<table class="table table-striped-columns">
    <tbody>
        <tr>
            <td class="text-end"><span class="fw-semibold">ชื่อครุภัณฑ์ : </span></td>
            <td colspan="3"><?= (isset($model->data_json['asset_name']) ? $model->data_json['asset_name'] : '-') ?></td>
            <td class="text-end"><span class="fw-semibold"> ประเภท : </span></td>
            <td colspan="3">
                <?= isset($model->data_json['asset_type_text']) ? $model->data_json['asset_type_text'] : '-' ?></td>
        </tr>
       
        <tr>
            <td class="text-end"><span class="fw-semibold">เลขครุภัณฑ์ : </span></td>
            <td colspan="3"><code><?= $model->code ?></code></td>
            <td class="text-end"><span class="fw-semibold"> มูลค่า : </span></td>
            <td colspan="3"><span
                    class="text-white bg-primary badge rounded-pill fs-6"><?= number_format($model->price, 2) ?></span>
                บาท</td>
        </tr>
        <tr>
            <td class="text-end"><span class="fw-semibold">อายุการใช้งาน : </span></td>
            <td colspan="3"><?= isset($model->data_json['service_life']) ? $model->data_json['service_life'] : '' ?> ปี</td>
            <td class="text-end"><span class="fw-semibold"> อัตราค่าเสื่อม : </span></td>
            <td colspan="3">
            <?= isset($model->data_json['depreciation']) ? $model->data_json['depreciation'] : '' ?>  % ต่อปี
        </td>
        </tr>
        <tr>
            <td class="text-end"><span class="fw-semibold">วันเดือนปีทีซื้อ : </span></td>
            <td colspan="3"> <?= Yii::$app->thaiFormatter->asDate($model->receive_date, 'medium') ?>
            </td>
            <td class="text-end"><span class="fw-semibold">การจัดซื้อ : </span></td>
            <td colspan="3"><?= $model->purchase_text ?></td>
        </tr>
        <tr>
            <td class="text-end"><span class="fw-semibold">ประจำหน่วยงาน : </span></td>
            <td colspan="3">
                <?= isset($model->data_json['department_name']) ? $model->data_json['department_name'] : '' ?>
            </td>
            <td class="text-end"><span class="fw-semibold">วิธีได้มา : </span></td>
            <td colspan="3"><?= $model->method_get ?></td>
        </tr>
        <tr>
            <td class="text-end"><span class="fw-semibold">ประเภทเงิน : </span></td>
            <td colspan="3"><?= $model->budget_type ?></td>
            <td class="text-end"><span class="fw-semibold">ผู้ขาย/ผู้จำหน่าย/ผู้บริจาค : </span></td>
            <td colspan="3"><?= $model->vendor_name ?></td>
        </tr>
        <?= $model->isComputer() ? $this->render('./is_computer/spec', ['model' => $model]) : '' ?>

        <tr class="align-middle">
            <td class="text-end"><span class="fw-semibold">สถานะ : </span></td>
            <td colspan="3">
                <?= $model->viewStatus() ?>
            </td>
            <td class="text-end"><span class="fw-semibold">ผู้รับผิดชอบ : </span></td>
            <td colspan="3">
            <?= $model->getOwner() ?>
            </td>
        </tr>
        <!-- ถ้ามีการส่งซ่อม -->
        <?php if ($model->asset_status == 5 && isset($repair)): ?>
       <?= $this->render('@app/modules/helpdesk/views/repair/repair_detail', ['repair' => $repair]) ?>
        <?php endif; ?>
        <!-- จบแสดงสถนะสงซ่อม -->
    </tbody>
</table>
<div class="row">
    <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">
       

    </div>

</div>