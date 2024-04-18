<?php
use app\components\AppHelper;
use yii\bootstrap5\Html;

?>
<!-- Profile Info Tab -->
<div id="emp-1" class="pro-overview tab-pane fade show active">

<div class="row">
    <div class="col-md-6 d-flex">
        <div class="card profile-box flex-fill">
            <div class="card-body">
                <h3 class="card-title">ข้อมูลส่วนตัว
                    <?=Html::a('<i class="fas fa-pencil-alt"></i>',['update','id' => $model->id,'form' => 'general'],['class' => 'edit-icon open-modal','data'=> ['size' => 'modal-lg']]);?>
                </h3>
                <div class="row">
                    <div class="col-6">
                        <table class="table table-borderless">

                            <tr class="">
                                <td>ชื่อ-สกุล</td>
                                <td><?=$model->fullname?></td>
                            </tr>
                            <tr class="">
                                <td>ชื่อ-สกุล อังกฤษ</td>
                                <td><?=$model->fullname_en?></td>
                            </tr>
                            <tr class="">
                                <td>เพศ</td>
                                <td><?=$model->gender?></td>
                            </tr>
                            <tr class="">
                                <td>วันเดือนปีเกิด</td>
                                <td><?=$model->fullname_en?></td>
                            </tr>
                            <tr class="">
                                <td>อายุปัจจุบัน</td>
                                <td><?=AppHelper::Age($model->birthday)?></td>
                            </tr>
                            <tr class="">
                                <td>หมู่เลือด</td>
                                <td><?=$model->blood_group?></td>
                            </tr>
                            <tr class="">
                                <td>ภูมิลำเนาเดิม</td>
                                <td><?=$model->hometown?></td>
                            </tr>
                            <tr class="">
                                <td>เชื้อชาติ</td>
                                <td><?=$model->ethnicity?></td>
                            </tr>
                            <tr class="">
                                <td>สัญชาติ</td>
                                <td><?=$model->religion?></td>
                            </tr>
                            <tr class="">
                                <td>ศาสนา</td>
                                <td><?=$model->fullname_en?></td>
                            </tr>
                            <tr class="">
                                <td>สถานภาพ</td>
                                <td><?=$model->marital_status?></td>
                            </tr>



                        </table>

                    </div>
                    <div class="col-6">
                        <div class="table-responsive">
                            <table class="table ">

                                <tr class="">
                                    <td scope="row">ที่อยู่ตามทะเบียนบ้าน</td>
                                    <td></td>
                                </tr>
                                <tr class="">
                                    <td colspan="2"><?=$model->address?> <?=$model->fulladdress?></td>
                                </tr>
                                <tr class="">
                                    <td>หมายเลขโทรศัพท์</td>
                                    <td><?=$model->phone?></td>
                                </tr>
                                <tr class="">
                                    <td>อีเมล</td>
                                    <td><?=$model->email?></td>
                                </tr>
                            </table>
                        </div>

                    </div>
                </div>

                <!-- End Row -->
            </div>
        </div>
    </div>
    <div class="col-md-6 d-flex">
        <div class="card profile-box flex-fill">
            <div class="card-body">
                <h3 class="card-title">การศึกษา
                    <?php // Html::a('<i class="fas fa-pencil-alt"></i>',['/profile/form-education'],['class' => 'edit-icon open-modal']);?>
                    <?=Html::a('<i class="fas fa-pencil-alt"></i>',['/employees/categorise/create','emp_id' =>$model->id,'title' => 'การศึกษา', 'name' => 'emp-education'],['class' => 'edit-icon open-modal','data'=> ['size' => 'modal-lg']]);?>
                    <!-- <a href="#" class="edit-icon"
                        data-bs-toggle="modal" data-bs-target="#education_info"><i
                            class="fas fa-pencil-alt"></i></a> -->
                </h3>
                <div class="experience-box">
                    <ul class="experience-list">
                        <li>
                            <div class="experience-user">
                                <div class="before-circle"></div>
                            </div>
                            <div class="experience-content">
                                <div class="timeline-content">
                                    <a href="#/" class="name">โรงเรียนชัยภูมภักดีชุมพล</a>
                                    <div>วิทย์-คณิต</div>
                                    <span class="time">2550 - 2553</span>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="experience-user">
                                <div class="before-circle"></div>
                            </div>
                            <div class="experience-content">
                                <div class="timeline-content">
                                    <a href="#/" class="name">มหาวิทยาลัยบูรพา</a>
                                    <div>สาขา เทคโนโลยีสารสนเทศ</div>
                                    <span class="time">2554 - 2558</span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">



    <div class="col-md-6 d-flex">
        <div class="card profile-box flex-fill">
            <div class="card-body">
                <h3 class="card-title"> ประวัติการเปลี่ยนชื่อ
                    <?=Html::a('<i class="fas fa-pencil-alt"></i>',['/profile/form-changname'],['class' => 'edit-icon open-modal']);?>
                </h3>
                <div class="table-responsive">
                    <table class="table table-nowrap">
                        <thead>
                            <tr>
                                <th>วันที่เปลี่ยน</th>
                                <th>คำนำหน้า</th>
                                <th>ชื่อ</th>
                                <th>ชื่อสกุล</th>
                                <th>สถานภาพ</th>
                                <th>สถานะการเปลี่ยน</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>30/01/58</td>
                                <td>ว่าที่ ร.ต.</td>
                                <td>จิตสง่า</td>
                                <td>เพชรสุวรรณ</td>
                                <td>โสด</td>
                                <td>ชื่อ-สกุลเดิม</td>

                                <td class="text-end">
                                    <div class="dropdown dropdown-action">
                                        <a aria-expanded="false" data-bs-toggle="dropdown"
                                            class="action-icon dropdown-toggle" href="#"><i
                                                class="material-icons">more_vert</i></a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="#" class="dropdown-item"><i class="fa fa-pencil m-r-5"></i>
                                                Edit</a>
                                            <a href="#" class="dropdown-item"><i
                                                    class="fa fa-trash-o m-r-5"></i> Delete</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>12/08/56</td>
                                <td>สิบเอก</td>
                                <td>จิตสง่า</td>
                                <td>เพชรสุวรรณ</td>
                                <td>โสด</td>
                                <td>ชื่อ-สกุลเดิม</td>

                                <td class="text-end">
                                    <div class="dropdown dropdown-action">
                                        <a aria-expanded="false" data-bs-toggle="dropdown"
                                            class="action-icon dropdown-toggle" href="#"><i
                                                class="material-icons">more_vert</i></a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="#" class="dropdown-item"><i class="fa fa-pencil m-r-5"></i>
                                                Edit</a>
                                            <a href="#" class="dropdown-item"><i
                                                    class="fa fa-trash-o m-r-5"></i> Delete</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>


                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>



<div class="row">
    <div class="col-md-6 d-flex">
        <div class="card profile-box flex-fill">
            <div class="card-body">
                <h3 class="card-title">ประสบการณ์
                    <?=Html::a('<i class="fas fa-pencil-alt"></i>',['/profile/form-experience'],['class' => 'edit-icon open-modal']);?>

                </h3>
                <div class="experience-box">
                    <ul class="experience-list">
                        <li>
                            <div class="experience-user">
                                <div class="before-circle"></div>
                            </div>
                            <div class="experience-content">
                                <div class="timeline-content">
                                    <a href="#/" class="name">Testter ธนาคาร CIMB THAI</a>
                                    <span class="time">Jan 2013 - Present (5 years 2 months)</span>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="experience-user">
                                <div class="before-circle"></div>
                            </div>
                            <div class="experience-content">
                                <div class="timeline-content">
                                    <a href="#/" class="name">โปรแกรมเมอร์ 3BB</a>
                                    <span class="time">Jan 2013 - Present (5 years 2 months)</span>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="experience-user">
                                <div class="before-circle"></div>
                            </div>
                            <div class="experience-content">
                                <div class="timeline-content">
                                    <a href="#/" class="name">นวก.คอมโรงพยาบาลอุบลรัตน์</a>
                                    <span class="time">Jan 2013 - Present (5 years 2 months)</span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    
</div>



</div>
<!-- /Profile Info Tab -->