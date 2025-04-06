<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\Vehicle $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Vehicles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>

<div class="mb-3 badge-soft-primary p-3 rounded">
    <label class="form-label fw-bold">เลขที่คำขอ: <?php echo $model->code?></label>
    <p><?php echo $model->userRequest()['fullname'];?>
        ขอใช้<?php echo $model->carType->title;?>ไป<?php echo $model->locationOrg?->title ?? '-'?> วันที่
        <?php echo $model->showDateRange()?></p>
       

</div>

<div class="booking-details">
    <label class="form-label">จัดสรรรถ</label>
    <table class="table table-bordered" id="details-table">
        <thead class="table-light">
            <tr>
                <th>วันที่</th>
                <th>รถ</th>
                <th>พนักงานขับรถ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($model->vehicleDetails ?? [] as $index => $detail): ?>
            <tr class="detail-row">
                <td>
                    <?=$detail->showDate()?>
                    <input type="hidden" name="vehicleDetails[<?= $index ?>][id]" value="<?= $detail->id ?>">
                </td>
                <td class="">
                            <div class="d-flex">
                                <?=Html::img($detail->car->ShowImg(),['class' => 'avatar rounded border-secondary'])?>
                                <div class="avatar-detail">
                                    <div class="d-flex flex-column">
                                        <p class="mb-0"><?=$detail->car->data_json['brand'];?></p>
                                        <p class="mb-0 fw-semibold text-primary"><?=$detail->license_plate?></p>
                                    </div>
                                </div>
                            </div>
                        </td>
                <td>
                    <?=$detail->driver->getAvatar(false,($detail->driver->phone))?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="form-group">
    <?php echo $model->data_json['remarks'] ?? '-'; ?>
</div>
