<?php echo $this->render('@app/modules/hr/views/leave/view_detail',['model' => $model->leave])?>
<?php echo $this->render('@app/modules/approve/views/approve/level_approve',[
    'model' => $model->leave,'name' => 'leave',
    ])?>