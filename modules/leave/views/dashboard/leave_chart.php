<?php
$arrLeave = ['ลาทั้งหมด','ลากกิจ','ลาพักผ่อน','ลาป่วย'];
?>
<div class="row">
    <div class="col-5">

        <div class="row">
            <?php foreach($arrLeave as $key => $title):?>
            <div class="col-xl-6">
                <?=$this->render('leave_summary',['title' => $title])?>
                </div>
                <?php endforeach;?>
        </div>



    </div>

</div>
<?php
$js = <<< JS


JS;
$this->registerJS($js);
?>