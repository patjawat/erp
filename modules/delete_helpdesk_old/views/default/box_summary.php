<?php
use yii\helpers\Html;
use yii\web\View;
?>
<div class="row">
            <div class="col-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <?=Html::a('<span class="text-muted text-uppercase fs-6">รับเรื่อง</span>',['/helpdesk/repair','repair_group' => $group,'status' => 2],['class' => 'box-summary'])?>
                                <h6 class="mb-0 mt-1" id="status2">0</h6>
                            </div>
                            <div class="text-center" style="position: relative;">
                                <div>
                                    <div class="bg-warning-subtle rounded p-3">
                                        <i class="fa-solid fa-user-check text-warning fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                        <?=Html::a('<span class="text-muted text-uppercase fs-6">ดำเนินการ</span>',['/helpdesk/repair','repair_group' => $group,'status' => 3],['class' => 'box-summary'])?>
                                <h6 class="mb-0 mt-1" id="status3">0</h6>
                            </div>
                            <div class="text-center" style="position: relative;">
                                <div>
                                    <div class="bg-primary-subtle rounded p-3">
                                        <i class="fa-solid fa-person-digging text-primary fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                        <?=Html::a('<span class="text-muted text-uppercase fs-6">ยกเลิก</span>',['/helpdesk/repair','repair_group' => $group,'status' => 5],['class' => 'box-summary'])?>
                                <h6 class="mb-0 mt-1" id="status5">0</h6>
                            </div>
                            <div class="text-center" style="position: relative;">
                                <div>
                                    <div class="bg-danger-subtle rounded p-3">
                                    <i class="fa-solid fa-circle-minus text-danger fs-4"></i>
                                      
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <?=Html::a('<span class="text-muted text-uppercase fs-6">เสร็จสิ้น</span>',['/helpdesk/repair','repair_group' => $group,'status' => 4],['class' => 'box-summary'])?>
                                <h6 class="mb-0 mt-1" id="status4">0</h6>
                            </div>
                            <div class="text-center" style="position: relative;">
                                <div>
                                    <div class="bg-success-subtle rounded p-3">
                                        <i class="fa-regular fa-circle-check text-success fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


<?php
// $urlAccept = Url::to(['/helpdesk/repair/list-accept','repair_group' => 1]);
// $urlSummary = Url::to(['/helpdesk/general/summary']);
$js = <<< JS
$('.box-summary').click(async function (e) { 
    e.preventDefault();
    await $.ajax({
        type: "get",
        url: $(this).attr('href'),
        dataType: "json",
    //     beforeSend: function() {
    //         $('#viewJob').html('<h6 class="text-center">กำลังโหลด...</h6>');
    // },
        success: function (res) {
            console.log(res);
            $('#viewJob').html(res.content);
        }
    });
    
});



JS;
$this->registerJS($js,View::POS_READY);
?>

