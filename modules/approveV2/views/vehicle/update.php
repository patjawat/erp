

<div class="mb-3 badge-soft-primary p-3 rounded">
    <label class="form-label fw-bold">เลขที่คำขอ: <?php echo $model->vehicle->code?></label>
    <p><?php echo $model->vehicle->userRequest()['fullname'];?>
        ขอใช้<?php echo $model->vehicle->carType->title;?>ไป<?php echo $model->vehicle->locationOrg?->title ?? '-'?> วันที่
        <?php echo $model->vehicle->showDateRange()?></p>
       

</div>
<?php echo $this->render('@app/modules/booking/views/vehicle/view',['model' => $model->vehicle])?>
<?php echo $this->render('@app/modules/approve/views/approve/level_approve',['model' => $model->vehicle,'name' => 'vehicle',])?>