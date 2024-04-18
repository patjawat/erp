<?php
use app\components\UserHelper;
use yii\bootstrap5\Html;
use yii\widgets\Pjax;

$this->title = 'ข้อมูลพื้นฐาน';

$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/employees']];
$this->params['breadcrumbs'][] = $this->title;



?>
<style>
.avatar-emp img {
    width: 120px;
}
</style>

<div class="row">
    <div class="col-lg-3 col-md-3 col-sm-12">
        <div class="card rounded-4 border-0 p-4">
            <div class="d-flex flex-column align-items-center justify-content-center">

                <?=Html::img('@web/img/demo/patjwat.jpg',['class' => 'rounded-circle border border-primary border-4','style' => 'width:150px'])?>
                <h5 class="mt-3">ปัจวัฒน์ ศรีบุญเรือง</h5>
                <p class="">Programmer</p>
                <span class="badge text-bg-primary rounded-pill p-2">ปฎิบัติงาน</span>
            </div>



            <div class="progress mt-4" role="progressbar" aria-label="Example with label" aria-valuenow="25"
                aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar" style="width: 25%">25%</div>
            </div>

            <div class="d-flex justify-content-between mt-3">

                <button type="button" class="btn btn-outline-primary">Primary</button>
                <button type="button" class="btn btn-outline-secondary">Secondary</button>
                <button type="button" class="btn btn-outline-success">Success</button>
            </div>



        </div>
    </div>

    <div class="col-lg-6 col-md-6 col-sm-12">

	<div class="d-flex flex-column gap-3">

		<?=$this->render('./view-emp/general',['model' => $model])?>
		

<br>
<br>
<br>
<br>

<?php // Html::img('@web/img/demo/profile1.png',['style' => 'width:600px'])?>
<div class="card mb-0">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="profile-view">
                    <div class="profile-img-wrap">

                        <?=$model->getAvatar()?>


                    </div>
                    <?php Pjax::begin(['id' => 'general-container']) ?>
                    <div class="staff-msg">
                        <div class="profile-basic">
                            <div class="row">
                                <!-- <div class="col-md-4">
													<div class="profile-info-left">
														<h3 class="user-name m-t-0 mb-0">จิตสง่า เพชรสุวรรณ</h3>
														<div class="row">
															<div class="col-md-4">
																<div class="nomal doj ">ตำแหน่งเลขที่ </div>
																<div class="nomal doj ">ตำแหน่งประสานงาน </div> (ทีมประสาน) [ ทีมงาน ]
																<div class="nomal doj ">ตำแหน่งบริหาร </div> ??
																<div class="nomal doj ">ประเภท </div> ข้าราชการ พลเรีทอน พนักงานราชการ พนักงานกระทรรวงสาธารณะสุข ลูกจ้างชั่วคราวรายเดือน ลูกจ้างชั่วราวรายวัน 
																<div class="nomal doj ">ระดับ </div>  ปฏิบัติการ ชำนาญการ ชำนาญการพิเศษ (ตรี)
                                                                ปวส ปฏิบัติงาน ชำนาญงาน

																<div class="nomal doj ">ความเชี่ยวชาญ </div> (ความสามารถพิเศษ)  text
																<div class="nomal doj ">สถานะปฏิบัติงาน </div> 
															</div>
															<div class="col-md-8">
																<div class="nomal doj ">66282</div>
																<div class="nomal doj ">นักจัดการทั่วไป</div>
																<div class="nomal doj ">-</div>
																<div class="nomal doj ">วิชาการ</div>
																<div class="nomal doj ">ชำนาญการ</div>
																<div class="nomal doj ">-</div>
																<div class="nomal doj ">ตรง จ. </div>
															</div>
		
														</div>
													</div>
												</div> -->

                                <div class="col-md-5">
                                    <div class="profile-info-left">
                                        <h3 class="user-name m-t-0 mb-0"><?=$model->fullname?></h3>
                                        <div class="staff-id"><?=$model->fullname_en?></div>
                                        <div class="staff-id">รหัสประจำตัว ID : FT-0001</div>
                                        <div class="staff-id">เลขประจำตัวประชาชน : <?=$model->cid?></div>
                                        <h6 class="staff-id">ชื่อตำแหน่ง : นักวิชาการคอมพิวเตอร์</h6>
                                        <h6 class="staff-id">ระดับตำแหน่ง : ชำนาญการพิเศษ</h6>
                                        <small class="staff-id">สังกัด : สำนักงานอธิการบดี</small><br>

                                        <!-- <div class="small doj text-muted">วันเดือนปีบรรจุ : 8 เมษายน พ.ศ.2565</div> -->
                                        <div class="staff-msg"><a class="btn btn-custom" href="chat.html"><i
                                                    class="fa-solid fa-print"></i> พิมพ์ [PDF]</a></div>
                                    </div>
                                </div>

                                <div class="col-md-7">

                                    <ul class="personal-info">


                                        <li>
                                            <div class="title">สถานะปฏิบัติงาน:</div>
                                            <div class="text"><a href="">ปฏิบัติราชการ</a></div>
                                        </li>
                                        <li>
                                            <div class="title">ประเภทบุคลากร:</div>
                                            <div class="text"><a href="">ข้าราชการ (66282)</a></div>
                                        </li>
                                        <li>
                                            <div class="title">ประเภท:</div>
                                            <div class="text"><a href="">วิชาการ</a></div>
                                        </li>


                                        <li>
                                            <div class="title">วันเดือนปีบรรจุ:</div>
                                            <div class="text"><a href="">8 เมษายน พ.ศ.2565</a></div>
                                        </li>
                                        <!-- <li>
															<div class="title">วันครบกำหนดสัญญาจ้าง :</div>
															<div class="text"><a href="">8 เมษายน พ.ศ.2565</a></div>
														</li> -->
                                        <li>
                                            <div class="title">วันผ่านการทดลองงาน:</div>
                                            <div class="text">8 เมษายน พ.ศ.2565</div>
                                        </li>

                                        <li>
                                            <div class="title">เกษียณอายุราชการเมื่อ:</div>
                                            <div class="text">8 เมษายน พ.ศ.2565</div>
                                        </li>


                                        <li>
                                            <div class="title">อายุราชการ:</div>
                                            <div class="text">1 ปี 5 เดือน 3 วัน</div>
                                        </li>

                                        <li>
                                            <div class="title">วันออกจากราชการ:</div>
                                            <div class="text">1 ปี 5 เดือน 3 วัน</div>
                                        </li>


                                    </ul>






                                </div>

                            </div>
                        </div>
                    </div>

                    <?php Pjax::end() ?>
                    <div class="pro-edit">

                        <!-- <a data-bs-target="#profile_info" data-bs-toggle="modal"  class="edit-icon open-modal" href="#"><i class="fas fa-pencil-alt"></i></a> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?=$this->render('emp_tabs/index',['model' => $model])?>