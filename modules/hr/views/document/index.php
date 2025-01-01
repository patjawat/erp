<?php
use yii\helpers\Url;
use yii\helpers\FileHelper;
        $directoryPath = Yii::getAlias('@webroot/msword/leave');

        try {
            $files = FileHelper::findFiles($directoryPath, [
                'only' => ['*.docx'], // ค้นหาเฉพาะไฟล์ที่ลงท้ายด้วย .docx
                'recursive' => false, // ค้นหาเฉพาะใน directory ปัจจุบัน
            ]);
        
        } catch (\Throwable $th) {
            //throw $th;
        }
?>
<?php if (empty($files)):?>
    ไม่พบไฟล์ใน directory

    <?php else:?>
<?php foreach($files as $index => $file):?>

    <?php

$fileName = basename($file); // ดึงชื่อไฟล์

?>
<iframe src="https://docs.google.com/gview?url=<?= Url::to(Yii::getAlias('@web') . '/msword/results/' . $fileName, true); ?>&embedded=true" width='100%' height='1000px frameborder="0"></iframe>
<p><?php echo $fileName?></p>
    <?php endforeach;?>
    <?php endif;?>