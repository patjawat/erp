<?php

$this->title = 'ติดตามแผนรายจ่าย';
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-chart-simple"></i> </i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('@app/modules/plan/menu', ['active' => 'overview']) ?>
<?php $this->endBlock(); ?>


<table class="table table-bordered table-hover table-overview">
    <thead>
        <tr>
            <td width="30%" rowspan="2" colspan="4" class="fw-semibold">รายการ</td>
            <td width="10%" rowspan="2" class="fw-semibold text-center">แผนปี 2569</td>
            <td colspan="3" class="fw-semibold text-center">ไตรมาส 1</td>
            <td colspan="3" class="fw-semibold  text-center">ไตรมาส 2</td>
            <td colspan="3" class="fw-semibold  text-center">ไตรมาส 3</td>
            <td colspan="3" class="fw-semibold  text-center">ไตรมาส 4</td>
        </tr>
        <tr>
            <td width="5%" class="text-center">ต.ค. 68</td>
            <td width="5%" class="text-center">พ.ย. 68</td>
            <td width="5%" class="text-center">ธ.ค. 68</td>
            <td width="5%" class="text-center">ม.ค. 69</td>
            <td width="5%" class="text-center">ก.พ. 69</td>
            <td width="5%" class="text-center">มี.ค. 69</td>
            <td width="5%" class="text-center">เม.ย. 69</td>
            <td width="5%" class="text-center">พ.ค. 69</td>
            <td width="5%" class="text-center">มิ.ย. 69</td>
            <td width="5%" class="text-center">ก.ค. 69</td>
            <td width="5%" class="text-center">ส.ค. 69</td>
            <td width="5%" class="text-center">ก.ย. 69</td>
        </tr>
    </thead>
    <tbody class="align-middle table-group-divider">
        <tr class="grey">
            <td colspan="4">รายจ่าย</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="grey">
            <td width="10px"></td>
            <td colspan="3">รายจ่ายบุคลากร</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าจ้างลูกจ้างชั่วคราว / พนักงานกระทรวง</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าล่วงเวลางานบริการ / งานสนับสนุน</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าตอบแทนการปฏิบัติงานเวรผลัดบ่ายหรือผลัดดึกของเจ้าหน้าที่</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าตอบแทนเงินเพิ่มพิเศษไม่ทำเวชปฏิบัติส่วนตัว หรือปฏิบัติงาน รพ.เอกชน</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าตอบแทนเบี้ยเลี้ยงเหมาจ่าย (ฉ.11)</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าตอบแทนตามผลการปฏิบัติงาน (ฉ.12)</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">เงินเพิ่ม (พ.ต.ส)</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าตอบแทนเจ้าหน้าที่ปฏิบัติงานของเจ้าหน้าที่ (นอกเวลา) ฉ5</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าตอบแทนเจ้าหน้าที่ปฏิบัติงานในคลินิกพิเศษเฉพาะทางนอกเวลาราชการ (SMC)</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าตอบแทนอื่น</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">เงินค่าใช้จ่ายบุคลากรอื่น</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าตอบแทนเบี้ยเลี้ยงเหมาจ่าย (ฉ.10)</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="grey">
            <td width="10px"></td>
            <td colspan="3">รายจ่ายจากการดำเนินงาน</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่ายา</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าเวชภัณฑ์มิใช่ยา</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าวัสดุ</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าสาธารณูปโภค</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าใช้สอย</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าใช้จ่ายดำเนินงานอื่น</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr>
        <tr class="grey">
            <td width="10px"></td>
            <td colspan="3">รายจ่ายลงทุน</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าครุภัณฑ์</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าที่ดินและสิ่งก่อสร้าง</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าครุภัณฑ์ต่ำกว่าเกณฑ์</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="grey">
            <td width="10px"></td>
            <td colspan="3">รายจ่ายอื่น</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายจ่ายสนับสนุน รพ.สต. รพช. รพท. รพศ. สสอ. สสจ.</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายจ่ายอื่นๆ</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr>
        <tr class="grey">
            <td width="10px"></td>
            <td colspan="3">รายรับจากการดำเนินงาน</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลสำหรับโครงการสุขภาพถ้วนหน้า UC</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลสำหรับโครงการสุขภาพถ้วนหน้า UC งบลงทุน</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับจากระบบปฏิบัติการฉุกเฉิน (EMS)</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลเบิกจ่ายตรงกรมบัญชีกลาง</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลผู้ป่วยเบิกต้นสังกัด</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลเบิกจาก อปท.</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลจากกองทุนประกันสังคม</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลแรงงานต่างด้าว</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลและการบริการอื่น</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="grey">
            <td width="10px"></td>
            <td colspan="3">รายรับอื่น</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับเงินช่วยเหลือ</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับเงินอุดหนุน</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับจากการบริจาค</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับดอกเบี้ยเงินฝากธนาคาร</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับอื่น</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับไม่ทราบแหล่งที่มา</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr> <!---->
        <tr class="grey">
            <td></td>
            <td colspan="3">งบกลาง (ไม่เกินร้อยละ 2-3.5 ของประมาณการรายจ่าย)</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="14"></td>
        </tr>
        <tr class="td-sum">
            <td width="10px"></td>
            <td width="10px"></td>
            <td width="10px"></td>
            <td class="text-right">รวมรายจ่าย</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
            <td class="text-end fw-semibold">0.00</td>
        </tr>
    </tbody>
</table>