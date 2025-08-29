<?php

use app\modules\plan\models\PlanOrder;

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
            <td width="30%" rowspan="2" colspan="4" class="fw-semibold text-center align-middle">รายการ</td>
            <td width="10%" rowspan="2" class="fw-semibold text-center text-center align-middle">แผนปี 2569</td>
            <td colspan="3" class="fw-semibold text-center bg-primary text-dark bg-opacity-25">ไตรมาส 1</td>
            <td colspan="3" class="fw-semibold  text-center bg-secondary text-dark bg-opacity-25">ไตรมาส 2</td>
            <td colspan="3" class="fw-semibold  text-center bg-success text-dark bg-opacity-25">ไตรมาส 3</td>
            <td colspan="3" class="fw-semibold  text-center bg-danger text-dark bg-opacity-25">ไตรมาส 4</td>
        </tr>
        <tr>
            <td width="5%" class="fw-semibold text-center bg-primary text-dark bg-opacity-25">ต.ค. 68</td>
            <td width="5%" class="fw-semibold text-center bg-primary text-dark bg-opacity-25">พ.ย. 68</td>
            <td width="5%" class="fw-semibold text-center bg-primary text-dark bg-opacity-25">ธ.ค. 68</td>
            <td width="5%" class="fw-semibold text-center bg-secondary text-dark bg-opacity-25">ม.ค. 69</td>
            <td width="5%" class="fw-semibold text-center bg-secondary text-dark bg-opacity-25">ก.พ. 69</td>
            <td width="5%" class="fw-semibold text-center bg-secondary text-dark bg-opacity-25">มี.ค. 69</td>
            <td width="5%" class="fw-semibold text-center bg-success text-dark bg-opacity-25">เม.ย. 69</td>
            <td width="5%" class="fw-semibold text-center bg-success text-dark bg-opacity-25">พ.ค. 69</td>
            <td width="5%" class="fw-semibold text-center bg-success text-dark bg-opacity-25">มิ.ย. 69</td>
            <td width="5%" class="fw-semibold text-center bg-danger text-dark bg-opacity-25">ก.ค. 69</td>
            <td width="5%" class="fw-semibold text-center bg-danger text-dark bg-opacity-25">ส.ค. 69</td>
            <td width="5%" class="fw-semibold text-center bg-danger text-dark bg-opacity-25">ก.ย. 69</td>
        </tr>
    </thead>
    <tbody class="align-middle table-group-divider">
        <tr>
            <td colspan="4" class="fw-semibold"><i class="fa-solid fa-caret-right"></i> รายจ่าย</td>
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
            <td width="10px"></td>
            <td colspan="16" class="bg-warning fw-semibold text-dark bg-opacity-25"><i class="fa-solid fa-chevron-right me-1"></i> รายจ่ายบุคลากร</td>
        </tr>
        <?php foreach (PlanOrder::listOverviewSummary('2569', 'PE') as $item): ?>
            <tr class="yellow">
                <td width="10px"></td>
                <td width="10px"></td>
                <td colspan="2"><?= $item['title'] ?></td>
                <td class="text-end"><?= $item['total'] ?></td>
                <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25"><?= number_format($item['m9'], 2) ?></td>
                <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25"><?= number_format($item['m10'], 2) ?></td>
                <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25"><?= number_format($item['m11'], 2) ?></td>
                <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25"><?= number_format($item['m12'], 2) ?></td>
                <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25"><?= number_format($item['m1'], 2) ?></td>
                <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25"><?= number_format($item['m2'], 2) ?></td>
                <td class="text-end fw-semibold bg-success text-dark bg-opacity-25"><?= number_format($item['m3'], 2) ?></td>
                <td class="text-end fw-semibold bg-success text-dark bg-opacity-25"><?= number_format($item['m4'], 2) ?></td>
                <td class="text-end fw-semibold bg-success text-dark bg-opacity-25"><?= number_format($item['m5'], 2) ?></td>
                <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25"><?= number_format($item['m6'], 2) ?></td>
                <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25"><?= number_format($item['m7'], 2) ?></td>
                <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25"><?= number_format($item['m8'], 2) ?></td>

            </tr>
        <?php endforeach ?>

        <tr>
            <td width="10px"></td>
            <td colspan="16" class="bg-warning fw-semibold text-dark bg-opacity-25"><i class="fa-solid fa-chevron-right me-1"></i>รายจ่ายจากการดำเนินงาน</td>

        </tr>
        <?php foreach (PlanOrder::listOverviewSummary('2569', 'OE') as $item): ?>
            <tr class="yellow">
                <td width="10px"></td>
                <td width="10px"></td>
                <td colspan="2"><?= $item['title'] ?></td>
                <td class="text-end fw-semibold"><?= number_format($item['total'], 2) ?></td>
                <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25"><?= number_format($item['m9'], 2) ?></td>
                <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25"><?= number_format($item['m10'], 2) ?></td>
                <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25"><?= number_format($item['m11'], 2) ?></td>
                <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25"><?= number_format($item['m12'], 2) ?></td>
                <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25"><?= number_format($item['m1'], 2) ?></td>
                <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25"><?= number_format($item['m2'], 2) ?></td>
                <td class="text-end bg-success text-dark bg-opacity-25"><?= number_format($item['m3'], 2) ?></td>
                <td class="text-end bg-success text-dark bg-opacity-25"><?= number_format($item['m4'], 2) ?></td>
                <td class="text-end bg-success text-dark bg-opacity-25"><?= number_format($item['m5'], 2) ?></td>
                <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25"><?= number_format($item['m6'], 2) ?></td>
                <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25"><?= number_format($item['m7'], 2) ?></td>
                <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25"><?= number_format($item['m8'], 2) ?></td>

            </tr>
        <?php endforeach ?>

        <tr>
            <td width="10px"></td>
            <td colspan="16" class="bg-warning fw-semibold text-dark bg-opacity-25"><i class="fa-solid fa-chevron-right me-1"></i>รายจ่ายลงทุน</td>
        </tr>
        <?php foreach (PlanOrder::listOverviewSummary('2569', 'CE') as $item): ?>
            <tr class="yellow">
                <td width="10px"></td>
                <td width="10px"></td>
                <td colspan="2"><?= $item['title'] ?></td>
                <td class="text-end"><?= number_format($item['total'], 2) ?></td>
                <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25"><?= number_format($item['m9'], 2) ?></td>
                <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25"><?= number_format($item['m10'], 2) ?></td>
                <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25"><?= number_format($item['m11'], 2) ?></td>
                <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25"><?= number_format($item['m12'], 2) ?></td>
                <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25"><?= number_format($item['m1'], 2) ?></td>
                <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25"><?= number_format($item['m2'], 2) ?></td>
                <td class="text-end bg-success text-dark bg-opacity-25"><?= number_format($item['m3'], 2) ?></td>
                <td class="text-end bg-success text-dark bg-opacity-25"><?= number_format($item['m4'], 2) ?></td>
                <td class="text-end bg-success text-dark bg-opacity-25"><?= number_format($item['m5'], 2) ?></td>
                <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25"><?= number_format($item['m6'], 2) ?></td>
                <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25"><?= number_format($item['m7'], 2) ?></td>
                <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25"><?= number_format($item['m8'], 2) ?></td>

            </tr>
        <?php endforeach ?>
        <tr>
            <td width="10px"></td>
            <td colspan="16" class="bg-warning fw-semibold text-dark bg-opacity-25"><i class="fa-solid fa-chevron-right me-1"></i>รายจ่ายอื่น</td>
        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายจ่ายสนับสนุน รพ.สต. รพช. รพท. รพศ. สสอ. สสจ.</td>
            <td class="text-end">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายจ่ายอื่นๆ</td>
            <td class="text-end">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
        </tr>
        <tr>
            <td width="10px"></td>
            <td colspan="16" class="bg-warning fw-semibold text-dark bg-opacity-25"><i class="fa-solid fa-chevron-right me-1"></i>รายรับจากการดำเนินงาน</td>
        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลสำหรับโครงการสุขภาพถ้วนหน้า UC</td>
            <td class="text-end">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลสำหรับโครงการสุขภาพถ้วนหน้า UC งบลงทุน</td>
            <td class="text-end">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับจากระบบปฏิบัติการฉุกเฉิน (EMS)</td>
            <td class="text-end">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลเบิกจ่ายตรงกรมบัญชีกลาง</td>
            <td class="text-end">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลผู้ป่วยเบิกต้นสังกัด</td>
            <td class="text-end">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลเบิกจาก อปท.</td>
            <td class="text-end">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลจากกองทุนประกันสังคม</td>
            <td class="text-end">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลแรงงานต่างด้าว</td>
            <td class="text-end">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลและการบริการอื่น</td>
            <td class="text-end">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr>
            <td width="10px"></td>
            <td colspan="16" class="bg-warning fw-semibold text-dark bg-opacity-25"><i class="fa-solid fa-chevron-right me-1"></i>รายรับอื่น</td>
        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับเงินช่วยเหลือ</td>
            <td class="text-end">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับเงินอุดหนุน</td>
            <td class="text-end">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับจากการบริจาค</td>
            <td class="text-end">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับดอกเบี้ยเงินฝากธนาคาร</td>
            <td class="text-end">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับอื่น</td>
            <td class="text-end">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับไม่ทราบแหล่งที่มา</td>
            <td class="text-end">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr>
            <td></td>
            <td colspan="16" class="bg-warning fw-semibold text-dark bg-opacity-25"><i class="fa-solid fa-chevron-right me-1"></i>งบกลาง (ไม่เกินร้อยละ 2-3.5 ของประมาณการรายจ่าย)</td>
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
            <td class="text-end">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end fw-semibold bg-danger text-dark bg-opacity-25">0.00</td>
        </tr>
    </tbody>
</table>