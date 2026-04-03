<?php
use yii\helpers\Html;
?>

<?php if($status == true): ?>
    <div class="row d-flex justify-content-center">
    <div class="col-6">

        <div class="alert alert-<?=$status == true ? 'success' : 'danger'?>" role="alert">
            <h4 class="alert-heading"><i class="bi bi-check-circle-fill"></i> แจ้งเตือน!</h4>
            <p>ดำเนินการเพิ่มข้อมูลจากไฟล์ CSV สำเร็จ</p>
            <?php echo Html::a('<i class="fa-regular fa-thumbs-up"></i> ดำเนินการสำเร็จ !',['/am/asset/'],['class' => $status == true ? 'btn btn-success' : 'btn btn-danger' ])?>
        </div>


    </div>
</div>
<?php endif;?>
<?php if($status == false): ?>
    <div class="row d-flex justify-content-center">
    <div class="col-6">

        <div class="alert alert-<?=$status == true ? 'success' : 'danger'?>" role="alert">
            <h4 class="alert-heading"><i class="bi bi-exclamation-circle-fill"></i> แจ้งเตือน!</h4>
            <p>พบข้อผิดพลาด กรุณาตรวจสอบข้อมูลดังกล่าว</p>
            <ul>
            <?php foreach($error as $x):?>
                <li><p><?= $x ?></p></li>
            <?php endforeach;?>
            </ul>
            <?php echo Html::a('<i class="fa-solid fa-rotate-left"></i> ย้อนกลับ !',['/am/asset/import-csv'],['class' => $status == true ? 'btn btn-success' : 'btn btn-danger' ])?>
        </div>
    </div>
</div>
<?php endif;?>
