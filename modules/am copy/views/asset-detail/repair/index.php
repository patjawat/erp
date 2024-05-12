<?php
use yii\helpers\Html;
?>
<div class="d-flex justify-content-between">
    <h3><i class="fa-solid fa-screwdriver-wrench"></i> ประวัติการซ่อม</h3>
    <?=Html::a('ข้อมูล',['/'])?>
</div>
<div
    class="table-responsive"
>
    <table
        class="table table-primary"
    >
        <thead class="table-secondary">
            <tr>
                <th scope="col">วันที่</th>
                <th scope="col">อาการ</th>
                <th scope="col">สถานะ</th>
                <th scope="col">แจ้งซ่อม</th>
                <th scope="col">ช่างซ่อม</th>
            </tr>
        </thead>
        <tbody>
            <tr class="">
                <td scope="row">1 ม.ค. 2566</td>
                <td>เปิดไม่ติด</td>
                <td>รอดำเนินการ</td>
                <td>มีเสียงดัง</td>
                <td>นายปัจวัฒน์ ศรีบุญเรือง</td>
               
            </tr>
           
        </tbody>
    </table>
</div>
