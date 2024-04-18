<?php
use app\components\AppHelper;
use kartik\detail\DetailView;
use yii\bootstrap5\Html;

?>
<div class="tab-content">

    <?=$this->render('emp-1',['model' => $model])?>

    <!-- Projects Tab -->
    <div class="tab-pane fade" id="emp-2">
        <div class="row">
            <div class="col-md-12 d-flex">
                <div class="card profile-box flex-fill">
                    <div class="card-body">
                        <h3 class="card-title">ข้อมูลการอบรมดูงาน
                            <?=Html::a('<i class="fas fa-pencil-alt"></i>',['/profile/form-train'],['class' => 'edit-icon open-modal']);?>
                        </h3>
                        <div class="table-responsive">
                            <table class="table table-nowrap">
                                <thead>
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>วันเดือนปี</th>
                                        <th>ความเคลื่อนไหว</th>
                                        <th>ตำแหน่ง/ส่วนราชการ</th>
                                        <th>ตำแหน่งเลขที่</th>
                                        <th>ประเภทกลุ่มงาน</th>
                                        <th>ระดับ</th>
                                        <th>เงินเดือน</th>
                                        <th>เอกสารอ้างอิง</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>01/01/2566</td>
                                        <td>-</td>
                                        <td>นักจัดการทั่วไป กลุ่มงานบริหารทัวไป</td>
                                        <td>66282</td>
                                        <td>วิชาการ</td>
                                        <td>ชำนาญการ</td>
                                        <td>55,540</td>
                                        <td>จ.เลย 1436/2566 ลว. 19 พ.ค.2566</td>
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
    </div>
    <div class="tab-pane fade" id="emp_3">
        <div class="row">
            <div class="col-md-6 d-flex">
                <div class="card profile-box flex-fill">
                    <div class="card-body">
                        <h3 class="card-title">ข้อมูลการอบรม/ดูงาน<a href="#" class="edit-icon" data-bs-toggle="modal"
                                data-bs-target="#family_info_modal"><i class="fas fa-pencil-alt"></i></a></h3>
                        <div class="table-responsive">
                            <table class="table table-nowrap">
                                <thead>
                                    <tr>
                                        <th>ตั้งแต่วันที่</th>
                                        <th>ถึงวันที่</th>
                                        <th>หลักสูตร</th>
                                        <th>สถาบัน</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>01/01/2566</td>
                                        <td>ผบก.รุ่น32</td>
                                        <td>วิทยาลัยพยาบาลบรมราชชรนนี</td>
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
            <div class="col-md-6 d-flex">
                <div class="card profile-box flex-fill">
                    <div class="card-body">
                        <h3 class="card-title">ใบประกอบวิชาชีพ<a href="#" class="edit-icon" data-bs-toggle="modal"
                                data-bs-target="#family_info_modal"><i class="fas fa-pencil-alt"></i></a></h3>
                        <div class="table-responsive">
                            <table class="table table-nowrap">
                                <thead>
                                    <tr>
                                        <th>ใบประกอบวิชาชีพ</th>
                                        <th>เลขที่</th>
                                        <th>สถาบันที่ออก</th>
                                        <th>วันที่ออก</th>
                                        <th>วันหมดอายุ</th>
                                        <th>การต่ออายุ</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>ว.14125</td>
                                        <td>125</td>
                                        <td>สภาเจได</td>
                                        <td>10/11/2566</td>
                                        <td>10/11/2568</td>
                                        <td>1</td>

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
                        <h3 class="card-title">ข้อมูลประวัติเครื่องราชอิสริยาภรณ์<a href="#" class="edit-icon"
                                data-bs-toggle="modal" data-bs-target="#family_info_modal"><i
                                    class="fas fa-pencil-alt"></i></a></h3>
                        <div class="table-responsive">
                            <table class="table table-nowrap">
                                <thead>
                                    <tr>
                                        <th>ปี</th>
                                        <th>เครื่องราชฯ</th>
                                        <th>หน่วยงาน</th>
                                        <th>ชื่อ-สกุล ปีที่ขอ</th>
                                        <th>ตำแหน่ง</th>
                                        <th>ระดับ</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>2558</td>
                                        <td>เหรียญจักรพรรดิ์มาลา</td>
                                        <td>รพร.ด่าน สสจ.เลย <br>สำนักงานปลัดกระทรวงสาธารสุข<br>กระทรวงสาธารณสุข</td>
                                        <td>ว่าที่ ร.ต.จิตสง่า เพชรสุวรรณ</td>
                                        <td>นักจัดการทั่วไป</td>
                                        <td>ชำนาญการ</td>
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
            <div class="col-md-6 d-flex">
                <div class="card profile-box flex-fill">
                    <div class="card-body">
                        <h3 class="card-title">ข้อมูลลงโทษทางวินัย<a href="#" class="edit-icon" data-bs-toggle="modal"
                                data-bs-target="#family_info_modal"><i class="fas fa-pencil-alt"></i></a></h3>
                        <div class="table-responsive">
                            <table class="table table-nowrap">
                                <thead>
                                    <tr>
                                        <th>วันที่</th>
                                        <th>กรณี</th>
                                        <th>โทษที่ได้รับ</th>
                                        <th>เลขที่คำสั่ง</th>
                                        <th>หมายเหตุ</th>
                                        <th></th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>01/10/2566</td>
                                        <td>เมาสุราขับรถ</td>
                                        <td>ภาคทัณฑ์</td>
                                        <td>1251/66</td>
                                        <td></td>
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
    </div>

    <div class="tab-pane fade" id="emp_4">
        <div class="card profile-box flex-fill">
            <div class="card-body">

                <div class="row">
                    <div class="col-md-4">

                        <div class="row">
                            <div class="col-md-6">
                                <h5>วันบรรจุเข้ารับราชการ</h5>
                            </div>
                            <div class="col-md-6">03/12/2533</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h5>วันเข้าสู่หน่วยงานปัจจุบัน</h5>
                            </div>
                            <div class="col-md-6">29/11/2543</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h5>วันเข้าสู่ตำแหน่งสายงานปัจจุบัน</h5>
                            </div>
                            <div class="col-md-6">07/12/2563</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h5>วันเข้าสู่ระดับปัจจุบัน</h5>
                            </div>
                            <div class="col-md-6">11/12/2551</div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>วันที่บรรจุกลับ</h5>
                            </div>
                            <div class="col-md-6">-</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>วันที่รับโอน</h5>
                            </div>
                            <div class="col-md-6">-</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>วันเกษีณอายุ</h5>
                            </div>
                            <div class="col-md-6">-</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>วันลาออก</h5>
                            </div>
                            <div class="col-md-6">-</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>วันเข้าสู่ระดับตาม พรบ.เดิม</h5>
                            </div>
                            <div class="col-md-6">-</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>วันให้โอน</h5>
                            </div>
                            <div class="col-md-6">-</div>
                        </div>



                    </div>

                    <div class="col-md-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>อายุราชการ</h5>
                            </div>
                            <div class="col-md-6">32 ปี 8 เดือน</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h5>ระยะเวลา</h5>
                            </div>
                            <div class="col-md-6">22 ปี 8 เดือน</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h5>ระยะเวลา</h5>
                            </div>
                            <div class="col-md-6">2 ปี 8 เดือน</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h5>ระยะเวลา</h5>
                            </div>
                            <div class="col-md-6">14 ปี 8 เดือน</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>ระยะเวลา</h5>
                            </div>
                            <div class="col-md-6">14 ปี 8 เดือน</div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>อายุราชการ</h5>
                            </div>
                            <div class="col-md-6">-</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>ระยะเวลา</h5>
                            </div>
                            <div class="col-md-6">-</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>ระยะเวลา</h5>
                            </div>
                            <div class="col-md-6">-</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>ระยะเวลา</h5>
                            </div>
                            <div class="col-md-6">-</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>ระยะเวลา</h5>
                            </div>
                            <div class="col-md-6">-</div>
                        </div>


                    </div>

                    <div class="col-md-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>วันสิ้นสุดสัญญาจ้าง</h5>
                            </div>
                            <div class="col-md-6">-</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>จำนวนครั้งที่จ้าง</h5>
                            </div>
                            <div class="col-md-6">-</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>ระดับตาม พรบ.เดิม</h5>
                            </div>
                            <div class="col-md-6">6</div>
                        </div>

                    </div>

                </div>

            </div>
        </div>

    </div>




    <div class="tab-pane fade" id="emp_5">
        <div class="card profile-box flex-fill">
            <div class="card-body">
                <h3 class="card-title">สถานะการดำรงตำแหน่ง<a href="#" class="edit-icon" data-bs-toggle="modal"
                        data-bs-target="#family_info_modal"><i class="fas fa-pencil-alt"></i></a></h3>
                <div class="table-responsive">
                    <table class="table table-nowrap">
                        <thead>
                            <tr>
                                <th>ลำดับ</th>
                                <th>ตั้งแต่</th>
                                <th>สถานนะ</th>
                                <th>ลำดับการเรียง</th>
                                <th>เลขที่คำขอ</th>
                                <th>แก้ไขโดย</th>
                                <th>วันที่</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>03/12/2533</td>
                                <td>ดำรงตำแหน่ง</td>
                                <td>1</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-end">
                                    <div class="dropdown dropdown-action">
                                        <a aria-expanded="false" data-bs-toggle="dropdown"
                                            class="action-icon dropdown-toggle" href="#"><i
                                                class="material-icons">more_vert</i></a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="#" class="dropdown-item"><i class="fa fa-pencil m-r-5"></i>
                                                Edit</a>
                                            <a href="#" class="dropdown-item"><i class="fa fa-trash-o m-r-5"></i>
                                                Delete</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td>2</td>
                                <td>01/01/2536</td>
                                <td>ลาศึกษา</td>
                                <td>2</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-end">
                                    <div class="dropdown dropdown-action">
                                        <a aria-expanded="false" data-bs-toggle="dropdown"
                                            class="action-icon dropdown-toggle" href="#"><i
                                                class="material-icons">more_vert</i></a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="#" class="dropdown-item"><i class="fa fa-pencil m-r-5"></i>
                                                Edit</a>
                                            <a href="#" class="dropdown-item"><i class="fa fa-trash-o m-r-5"></i>
                                                Delete</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td>3</td>
                                <td>01/06/2536</td>
                                <td>ลาศึกษา</td>
                                <td>3</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-end">
                                    <div class="dropdown dropdown-action">
                                        <a aria-expanded="false" data-bs-toggle="dropdown"
                                            class="action-icon dropdown-toggle" href="#"><i
                                                class="material-icons">more_vert</i></a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="#" class="dropdown-item"><i class="fa fa-pencil m-r-5"></i>
                                                Edit</a>
                                            <a href="#" class="dropdown-item"><i class="fa fa-trash-o m-r-5"></i>
                                                Delete</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td>4</td>
                                <td>01/01/2555</td>
                                <td>ดำรงตำแหน่ง</td>
                                <td>4</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="text-end">
                                    <div class="dropdown dropdown-action">
                                        <a aria-expanded="false" data-bs-toggle="dropdown"
                                            class="action-icon dropdown-toggle" href="#"><i
                                                class="material-icons">more_vert</i></a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="#" class="dropdown-item"><i class="fa fa-pencil m-r-5"></i>
                                                Edit</a>
                                            <a href="#" class="dropdown-item"><i class="fa fa-trash-o m-r-5"></i>
                                                Delete</a>
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


    <div class="tab-pane fade" id="emp_6">
        <div class="row staff-grid-row">
            <div class="col-md-4 col-sm-6 col-12 col-lg-4 col-xl-3">
                <div class="profile-widget">
                    <div class="profile-img">
                        <a href="client-profile.html" class="avatar"><img alt="" src="img/car_1.jpg"></a>
                    </div>
                    <div class="dropdown profile-action">
                        <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown"
                            aria-expanded="false"><i class="material-icons">more_vert</i></a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#edit_client"><i
                                    class="fa fa-pencil m-r-5"></i> Edit</a>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#delete_client"><i
                                    class="fa fa-trash-o m-r-5"></i> Delete</a>
                        </div>
                    </div>
                    <h4 class="user-name m-t-10 mb-0 text-ellipsis"><a href="client-profile.html">กธ5555</a></h4>
                    <h5 class="user-name m-t-10 mb-0 text-ellipsis"><a href="client-profile.html">TOYATA REVO
                            สีบรอนซ์เทา</a></h5>
                </div>
            </div>

            <div class="col-md-4 col-sm-6 col-12 col-lg-4 col-xl-3">
                <div class="profile-widget">
                    <div class="profile-img">
                        <a href="client-profile.html" class="avatar"><img alt="" src="img/car_2.jpg"></a>
                    </div>
                    <div class="dropdown profile-action">
                        <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown"
                            aria-expanded="false"><i class="material-icons">more_vert</i></a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#edit_client"><i
                                    class="fa fa-pencil m-r-5"></i> Edit</a>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#delete_client"><i
                                    class="fa fa-trash-o m-r-5"></i> Delete</a>
                        </div>
                    </div>
                    <h4 class="user-name m-t-10 mb-0 text-ellipsis"><a href="client-profile.html">ฏ344</a></h4>
                    <h5 class="user-name m-t-10 mb-0 text-ellipsis"><a href="client-profile.html">HONDA WAVE 110
                            สีน้ำเงิน</a></h5>
                </div>
            </div>
        </div>
    </div>


    <div class="tab-pane fade" id="emp_admin">
        <div class="row staff-grid-row">
            <div class="col-md-4 col-sm-6 col-12 col-lg-4 col-xl-3">
                <div class="profile-widget">
                    <div class="profile-img">
                        <a href="client-profile.html" class="avatar"><img alt="" src="img/car_1.jpg"></a>
                    </div>
                    <div class="dropdown profile-action">
                        <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown"
                            aria-expanded="false"><i class="material-icons">more_vert</i></a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#edit_client"><i
                                    class="fa fa-pencil m-r-5"></i> Edit</a>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#delete_client"><i
                                    class="fa fa-trash-o m-r-5"></i> Delete</a>
                        </div>
                    </div>
                    <h4 class="user-name m-t-10 mb-0 text-ellipsis"><a href="client-profile.html">กธ5555</a></h4>
                    <h5 class="user-name m-t-10 mb-0 text-ellipsis"><a href="client-profile.html">TOYATA REVO
                            สีบรอนซ์เทา</a></h5>
                </div>
            </div>

            <div class="col-md-4 col-sm-6 col-12 col-lg-4 col-xl-3">
                <div class="profile-widget">
                    <div class="profile-img">
                        <a href="client-profile.html" class="avatar"><img alt="" src="img/car_2.jpg"></a>
                    </div>
                    <div class="dropdown profile-action">
                        <a href="#" class="action-icon dropdown-toggle" data-bs-toggle="dropdown"
                            aria-expanded="false"><i class="material-icons">more_vert</i></a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#edit_client"><i
                                    class="fa fa-pencil m-r-5"></i> Edit</a>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#delete_client"><i
                                    class="fa fa-trash-o m-r-5"></i> Delete</a>
                        </div>
                    </div>
                    <h4 class="user-name m-t-10 mb-0 text-ellipsis"><a href="client-profile.html">ฏ344</a></h4>
                    <h5 class="user-name m-t-10 mb-0 text-ellipsis"><a href="client-profile.html">HONDA WAVE 110
                            สีน้ำเงิน</a></h5>
                </div>
            </div>
        </div>
    </div>


    <!-- /Bank Statutory Tab -->
</div>