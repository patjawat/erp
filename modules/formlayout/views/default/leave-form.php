<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>

<h2>แบบฟอร์มการลาพักผ่อน</h2>

<form method="POST" action="<?= Url::to(['site/leave-pdf']) ?>">
    <?= Html::csrfMetaTags() ?>

    <label>ชื่อ - นามสกุล:</label><br>
    <input type="text" name="fullname" required><br><br>

    <label>ตำแหน่ง:</label><br>
    <input type="text" name="position"><br><br>

    <label>วันที่เริ่มลา:</label><br>
    <input type="date" name="start_date" required><br><br>

    <label>วันที่สิ้นสุด:</label><br>
    <input type="date" name="end_date" required><br><br>

    <label>จำนวนวันลา:</label><br>
    <input type="number" name="leave_days" min="1" required><br><br>

    <label>เหตุผลในการลา:</label><br>
    <textarea name="reason" rows="3" required></textarea><br><br>

    <label>เซ็นชื่อ:</label><br>
    <canvas id="signature-pad" width="400" height="150" style="border:1px solid #ccc;"></canvas><br>
    <button type="button" onclick="clearCanvas()">ล้าง</button><br><br>

    <input type="hidden" name="signature" id="signature-input">
    <button type="button" onclick="submitForm()">ส่งคำร้อง</button>
</form>

<script>
function clearCanvas() {
    const canvas = document.getElementById("signature-pad");
    const ctx = canvas.getContext("2d");
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}

function submitForm() {
    const canvas = document.getElementById("signature-pad");
    const dataURL = canvas.toDataURL("image/png");
    document.getElementById("signature-input").value = dataURL;
    document.forms[0].submit();
}
</script>
