<!-- <div class="card mb-0">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="profile-view">
                    <div class="profile-img-wrap">


                    </div>
                    <div class="staff-msg">
                        <div class="profile-basic">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="profile-info-left">
                                        <h3 class="user-name m-t-0 mb-0">จิตสง่า เพชรสุวรรณ</h3>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="nomal doj ">ตำแหน่งเลขที่ </div>
                                                <div class="nomal doj ">ตำแหน่งประสานงาน </div>
                                                <div class="nomal doj ">ตำแหน่งบริหาร </div>
                                                <div class="nomal doj ">ประเภท </div>
                                                <div class="nomal doj ">ระดับ </div>
                                                <div class="nomal doj ">ความเชี่ยวชาญ </div>
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
                                </div>

                                <div class="col-md-5">
                                    <div class="profile-info-left">
                                        <h3 class="user-name m-t-0 mb-0">นายปัจวัฒน์ ศรีบุญเรือง</h3>
                                        <div class="staff-id">Mr.Patjawat Sriboonrouang</div>
                                        <div class="staff-id">รหัสประจำตัว ID : FT-0001</div>
                                        <div class="staff-id">เลขประจำตัวประชาชน : 1409900181748</div>
                                        <h6 class="staff-id">ชื่อตำแหน่ง : นักวิชาการคอมพิวเตอร์</h6>
                                        <h6 class="staff-id">ระดับตำแหน่ง : ชำนาญการพิเศษ</h6>
                                        <small class="staff-id">สังกัด : สำนักงานอธิการบดี</small><br>

                                        <div class="small doj text-muted">วันเดือนปีบรรจุ : 8 เมษายน พ.ศ.2565</div>
                                        <div class="staff-msg"><a class="btn btn-custom" href="chat.html"><i
                                                    class="fa-solid fa-print"></i> พิมพ์ [PDF]</a></div>
                                    </div>
                                </div>


                            </div>

                        </div>
                    </div>
                </div>

                <div class="pro-edit">

                </div>
            </div>
        </div>
    </div>
</div> -->



<div class="card border-0 rounded-3 mb-4">
    <div class="card-body">
        <h5 class="card-title"><i class="fa-solid fa-id-card-clip"></i> ข้อมูลการเข้ารับตำแหน่ง</h5>

        <table class="table border-0 table-striped-columns">
            <tbody>
                <tr>
                    <td scope="row">ตำแหน่ง</td>
                    <td><?=$model->positionName()?></td>
                    <td>ประเภท</td>
                    <td><?=$model->positionTypeName()?></td>
                   

                </tr>
                <tr>
               
                    <td scope="row">ตำแหน่งเลขที่</td>
                    <td><?=$model->position_number?></td>
                    <td>ระดับตำแหน่ง</td>
                    <td><?=$model->positionLevelName()?></td>
                </tr>
                <tr>
                    <td>ตำแหน่งบริหาร</td>
                    <td>???</td>
                    <td>ความเชี่ยวชาญ</td>
                    <td>ชำนาญการพิเศษ</td>
                </tr>
                <tr>
                  
                    <td>สถานะ</td>
                    <td>ปฏิบัติงาน</td>
                    <td>รหัสประจำตัว</td>
                    <td>FT-0001</td>
                </tr>
                <tr>
                    <td>อัตราเงินเดือน</td>
                    <td colspan="3">
                       <?=isset($model->salary) ? number_format($model->salary,2) : '-'?> บาท
                        </div>
                    </td>
                </tr>
            </tbody>
            <tfoot>

            </tfoot>
        </table>
    </div>
</div>