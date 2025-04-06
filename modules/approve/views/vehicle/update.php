<?php echo $this->render('@app/modules/booking/views/vehicle/show',[
    'model' => $model->vehicle,'name' => 'vehicle',
    ])?>
<?php echo $this->render('@app/modules/approve/views/approve/level_approve',[
    'model' => $model->vehicle,'name' => 'vehicle',
    ])?>