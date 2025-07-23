<?php
use yii\helpers\Html;
use yii\web\View;
?>
    <table
        class="table">
        <thead>
            <tr>
                <th scope="col">ลำดับ</th>
                <th scope="col">ชื่อ</th>
                <th scope="col">หน่วยงาน</th>
                <th scope="col">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody>
            
            <?php foreach($listTeam as $key => $item):?>
            <tr class="">
                <td scope="row"><?=$key+1?></td>
                <td><?=$item->emp->getAvatar(false)?></td>
                <td><?=$item->emp->departmentName()?></td>
               <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                    จัดการ
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1" style="">
                                    <li><?=Html::a('<i class="bi bi-eye me-2"></i>ลบ',['/helpdesk2/team/delete','id' => $item->id],['class' => 'dropdown-item delete-team'])?></li>

                                </ul>
                            </div>
                        </td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>


<?php

$js = <<< JS
$("body").on("click", ".delete-team", function (e) {
    e.preventDefault();
    var url = $(this).attr('href');

    Swal.fire({
        title: 'ยืนยันการลบ?',
        text: "คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.value == true) {
            console.log('configm');
            
            $.ajax({
                type: "post",
                url: url,
                dataType: "json",
                success: function (response) {
                    if(response.status === 'success') {
                        loadFormTeam();
                        loadListTeam();
                        Swal.fire({
                            icon: 'success',
                            title: 'ลบสำเร็จ!',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else {
                        Swal.fire('เกิดข้อผิดพลาด', response.message || 'ไม่สามารถลบได้', 'error');
                    }
                },
                error: function() {
                    Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                }
            });
        }
    });
});
JS;
$this->registerJS($js, View::POS_END)
?>
