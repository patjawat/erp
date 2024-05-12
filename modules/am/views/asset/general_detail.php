<?php
use yii\helpers\Html;
use app\modules\helpdesk\models\Helpdesk;
use app\modules\am\models\AssetDetail;

$repair = Helpdesk::findOne(['code' => $model->code]);
$modelCar = AssetDetail::find()->where(['name' => "tax_car",'code'=>$model->code])->orderBy(['date_start' => SORT_DESC])->one();
?>

<table class="table table-striped-columns">
    <tbody>
        <tr>
            <td class="text-end"><span class="fw-semibold">ชื่อครุภัณฑ์ : </span></td>
            <td colspan="3"><?=(isset($model->data_json['asset_name']) ? $model->data_json['asset_name'] : '-')?></td>
            <td class="text-end"><span class="fw-semibold"> ประเภท : </span></td>
            <td colspan="3">
                <?=isset($model->data_json['asset_type_text']) ? $model->data_json['asset_type_text'] : '-'?></td>
        </tr>
        <tr>
            <td class="text-end"><span class="fw-semibold">เลขครุภัณฑ์ : </span></td>
            <td colspan="3"><code><?=$model->code?></code></td>
            <td class="text-end"><span class="fw-semibold"> มูลค่า : </span></td>
            <td colspan="3"><span
                    class="text-white bg-primary badge rounded-pill fs-6"><?=number_format($model->price, 2)?></span>
                บาท</td>
        </tr>
        <tr>
            <td class="text-end"><span class="fw-semibold">วันเดือนปีทีซื้อ : </span></td>
            <td colspan="3"> <?=Yii::$app->thaiFormatter->asDate($model->receive_date, 'medium')?>
            </td>
            <td class="text-end"><span class="fw-semibold">การจัดซื้อ : </span></td>
            <td colspan="3"><?=$model->purchase_text?></td>
        </tr>
        <tr>
            <td class="text-end"><span class="fw-semibold">ประจำหน่วยงาน : </span></td>
            <td colspan="3">
                <?=isset($model->data_json['department_name']) ? $model->data_json['department_name'] : ''?>
            </td>
            <td class="text-end"><span class="fw-semibold">วิธีได้มา : </span></td>
            <td colspan="3"><?=$model->method_get?></td>
        </tr>
        <tr>
            <td class="text-end"><span class="fw-semibold">ประเภทเงิน : </span></td>
            <td colspan="3"><?=$model->budget_type?></td>
            <td class="text-end"><span class="fw-semibold">ผู้ขาย/ผู้จำหน่าย/ผู้บริจาค : </span></td>
            <td colspan="3"><?=$model->vendor_name?></td>
        </tr>
        <?=$model->isComputer() ? $this->render('./is_computer/spec', ['model' => $model]) : ''?>
        <tr class="align-middle">
            <td class="text-end"><span class="fw-semibold">สถานะ : </span></td>
            <td colspan="5">
                <?php if($model->asset_status == 1):?>
                <label class="badge rounded-pill text-primary-emphasis bg-success-subtle py-2 fs-6 align-middle">
                    <i class="bi bi-check2-circle fs-5"></i> <?=$model->statusName()?> </label>
                <?php endif;?>

                <?php if($model->asset_status == 5):?>
                <label class="badge rounded-pill text-danger-emphasis bg-danger-subtle py-2 fs-6 align-middle">
                <i class="fa-solid fa-triangle-exclamation fs-5"></i> <?=$model->statusName()?> </label>
                <?php endif;?>

            </td>
        </tr>
        <!-- ถ้ามีการส่งซ่อม -->
        <?php if($model->asset_status == 5):?>
        <tr>
            <td colspan="6" class="text-center bg-warning-subtle">
            <span class="fw-semibold"> <i class="fa-solid fa-file-pen"></i> บันทึกการแจ้งซ่อม : </span>
                <?=Yii::$app->thaiFormatter->asDateTime($model->created_at,'medium')?></td>
        </tr>
        <tr>
            <td class="text-end"><span class="fw-semibold">อาการแจ้งซ่อม : </span></td>
            <td colspan="3">
                <span class="text-danger"><?=$repair->data_json['title']?></span>
            </td>
            <td class="text-end"><span class="fw-semibold">สภานะงานซ่อม : </span></td>
            <td colspan="3">
                <?php if($repair->data_json['repair_status'] == 'ร้องขอ'):?>
                <label class="badge rounded-pill text-danger-emphasis bg-danger-subtle py-2 fs-6 align-middle">
                    <i class="fa-regular fa-hourglass-half"></i> ร้องขอ</label>
                <?php else:?>
                <?=$repair->data_json['repair_status']?>
                <?php endif;?>
            </td>
        </tr>
        <tr>
            <td class="text-end"><span class="fw-semibold">ข้อมูลเพิ่มเติม : </span></td>
            <td colspan="5">
                <span class="text-danger">
                    <?=$repair->data_json['note']?>
                </span>
            </td>
        </tr>
        <?php if(isset($repair->data_json['technician_name'])):?>
        <tr>
            <td class="text-end"><span class="fw-semibold">ช่างผู้รับเรื่อง : </span></td>
            <td colspan="5"><?=$repair->data_json['technician_name'];?></td>
        </tr>
        <?php endif;?>
        <?php  endif;?>
        <!-- จบแสดงสถนะสงซ่อม -->
    </tbody>
</table>
<div class="row">
<div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">
                            <!-- ถ้าเป็นรถ -->
                            <div class="alert alert-primary bprder-0 d-flex justify-content-between p-4" role="alert">
                                <span><i class="fa-solid fa-hourglass-end"></i> อัตราค่าเสื่อม
                                    <?=isset($model->data_json['depreciation']) ? $model->data_json['depreciation'] : ''?>
                                    ต่อปี</span>
                                <span><i class="fa-regular fa-clock"></i> อายุการใช้งาน
                                    <?=isset($model->data_json['service_life']) ? $model->data_json['service_life'] : ''?></span>
                            </div>
                            <?php if (isset($model->Retire()['progress'])): ?>
                            <div class="progress progress-sm mt-3 w-100">
                                <div class="progress-bar" role="progressbar"
                                    <?="style='width:" . $model->Retire()['progress'] . "%; background-color:" . $model->Retire()['color'] . ";  '"?>
                                    aria-valuenow="65" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-2" style="width:100%;">
                                <div>
                                    <i class="fa-regular fa-clock"></i> <span class="fw-semibold">เหลือเวลา</span> :
                                    <?=AppHelper::CountDown($model->Retire()['date'])[0] != '-' ? AppHelper::CountDown($model->Retire()['date']) : "หมดอายุการใช้งาน"?>
                                </div>|<div>
                                    <i class="fa-solid fa-calendar-xmark"></i> <span class="fw-semibold">หมดอายุ</span>
                                    <span class="text-danger"><?=$model->Retire()['date'];?></span>
                                </div>
                            </div>
                            <?php endif;?>
                        </div>
                        <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">
                            <div class="d-flex justify-content-between total font-weight-bold bg-secondary-subtle rounded p-2">
                                <?=$model->getOwner()?>
                            </div>
                        </div>
</div>