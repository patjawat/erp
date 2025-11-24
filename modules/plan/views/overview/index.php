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
            <td colspan="16" class="bg-warning text-dark bg-opacity-25"><i class="fa-solid fa-chevron-right me-1"></i> รายจ่ายบุคลากร</td>
        </tr>

         <?php
        $sql1 = "SELECT t.code,t.title,c.category_id,c.code,c.title,i.code,i.title,
                   COALESCE(SUM(o.order_price),0) AS total,
                    COALESCE(SUM(o.month_1),0) AS m1,
                    COALESCE(SUM(o.month_2),0) AS m2,
                    COALESCE(SUM(o.month_3),0) AS m3,
                    COALESCE(SUM(o.month_4),0) AS m4,
                    COALESCE(SUM(o.month_5),0) AS m5,
                    COALESCE(SUM(o.month_6),0) AS m6,
                    COALESCE(SUM(o.month_7),0) AS m7,
                    COALESCE(SUM(o.month_8),0) AS m8,
                    COALESCE(SUM(o.month_9),0) AS m9,
                    COALESCE(SUM(o.month_10),0) AS m10,
                    COALESCE(SUM(o.month_11),0) AS m11,
                    COALESCE(SUM(o.month_12),0) AS m12
                    FROM `plan_order` o
                    LEFT JOIN categorise i ON i.code = o.plan_item_id
                    LEFT JOIN categorise c ON c.code = i.category_id
                    LEFT JOIN categorise t ON t.code = c.category_id
                    WHERE i.name = 'plan_item'
                    AND c.name = 'plan_category'
                    AND t.name = 'plan_type'
                    AND c.category_id = 'PER'
                    AND c.code = 'PER_01'";

        $query1 = Yii::$app->db->createCommand($sql1)->queryOne();
        ?>

        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าจ้างลูกจ้างชั่วคราว / พนักงานกระทรวง</td>
            <td class="text-end"><?=$query1['total']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query1['m10']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query1['m11']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query1['m12']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query1['m1']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query1['m2']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query1['m3']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query1['m4']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query1['m5']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query1['m6']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query1['m7']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query1['m8']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query1['m9']?></td>
        </tr>

         <?php
        $sql2 = "SELECT t.code,t.title,c.category_id,c.code,c.title,i.code,i.title,
                   COALESCE(SUM(o.order_price),0) AS total,
                    COALESCE(SUM(o.month_1),0) AS m1,
                    COALESCE(SUM(o.month_2),0) AS m2,
                    COALESCE(SUM(o.month_3),0) AS m3,
                    COALESCE(SUM(o.month_4),0) AS m4,
                    COALESCE(SUM(o.month_5),0) AS m5,
                    COALESCE(SUM(o.month_6),0) AS m6,
                    COALESCE(SUM(o.month_7),0) AS m7,
                    COALESCE(SUM(o.month_8),0) AS m8,
                    COALESCE(SUM(o.month_9),0) AS m9,
                    COALESCE(SUM(o.month_10),0) AS m10,
                    COALESCE(SUM(o.month_11),0) AS m11,
                    COALESCE(SUM(o.month_12),0) AS m12
                    FROM `plan_order` o
                    LEFT JOIN categorise i ON i.code = o.plan_item_id
                    LEFT JOIN categorise c ON c.code = i.category_id
                    LEFT JOIN categorise t ON t.code = c.category_id
                    WHERE i.name = 'plan_item'
                    AND c.name = 'plan_category'
                    AND t.name = 'plan_type'
                    AND c.category_id = 'PER'
                    AND c.code = 'PER_02'";

        $query2 = Yii::$app->db->createCommand($sql2)->queryOne();
        ?>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าล่วงเวลางานบริการ / งานสนับสนุน</td>
            <td class="text-end"><?=$query2['total']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query2['m10']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query2['m11']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query2['m12']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query2['m1']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query2['m2']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query2['m3']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query2['m4']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query2['m5']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query2['m6']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query2['m7']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query2['m8']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query2['m9']?></td>

        </tr>
           <?php
        $sql3 = "SELECT t.code,t.title,c.category_id,c.code,c.title,i.code,i.title,
                    COALESCE(SUM(o.order_price),0) AS total,
                    COALESCE(SUM(o.month_1),0) AS m1,
                    COALESCE(SUM(o.month_2),0) AS m2,
                    COALESCE(SUM(o.month_3),0) AS m3,
                    COALESCE(SUM(o.month_4),0) AS m4,
                    COALESCE(SUM(o.month_5),0) AS m5,
                    COALESCE(SUM(o.month_6),0) AS m6,
                    COALESCE(SUM(o.month_7),0) AS m7,
                    COALESCE(SUM(o.month_8),0) AS m8,
                    COALESCE(SUM(o.month_9),0) AS m9,
                    COALESCE(SUM(o.month_10),0) AS m10,
                    COALESCE(SUM(o.month_11),0) AS m11,
                    COALESCE(SUM(o.month_12),0) AS m12
                    FROM `plan_order` o
                    LEFT JOIN categorise i ON i.code = o.plan_item_id
                    LEFT JOIN categorise c ON c.code = i.category_id
                    LEFT JOIN categorise t ON t.code = c.category_id
                    WHERE i.name = 'plan_item'
                    AND c.name = 'plan_category'
                    AND t.name = 'plan_type'
                    AND c.category_id = 'PER'
                    AND c.code = 'PER_03'";

        $query3 = Yii::$app->db->createCommand($sql3)->queryOne();
        ?>
        
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าตอบแทนการปฏิบัติงานเวรผลัดบ่ายหรือผลัดดึกของเจ้าหน้าที่</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query3['m10']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query3['m11']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query3['m12']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query3['m1']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query3['m2']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query3['m3']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query3['m4']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query3['m5']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query3['m6']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query3['m7']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query3['m8']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query3['m9']?></td>

        </tr>
          <?php
        $sql4 = "SELECT t.code,t.title,c.category_id,c.code,c.title,i.code,i.title,
                     COALESCE(SUM(o.order_price),0) AS total,
                    COALESCE(SUM(o.month_1),0) AS m1,
                    COALESCE(SUM(o.month_2),0) AS m2,
                    COALESCE(SUM(o.month_3),0) AS m3,
                    COALESCE(SUM(o.month_4),0) AS m4,
                    COALESCE(SUM(o.month_5),0) AS m5,
                    COALESCE(SUM(o.month_6),0) AS m6,
                    COALESCE(SUM(o.month_7),0) AS m7,
                    COALESCE(SUM(o.month_8),0) AS m8,
                    COALESCE(SUM(o.month_9),0) AS m9,
                    COALESCE(SUM(o.month_10),0) AS m10,
                    COALESCE(SUM(o.month_11),0) AS m11,
                    COALESCE(SUM(o.month_12),0) AS m12
                    FROM `plan_order` o
                    LEFT JOIN categorise i ON i.code = o.plan_item_id
                    LEFT JOIN categorise c ON c.code = i.category_id
                    LEFT JOIN categorise t ON t.code = c.category_id
                    WHERE i.name = 'plan_item'
                    AND c.name = 'plan_category'
                    AND t.name = 'plan_type'
                    AND c.category_id = 'PER'
                    AND c.code = 'PER_04'";

        $query4 = Yii::$app->db->createCommand($sql4)->queryOne();
        ?>
       
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าตอบแทนเงินเพิ่มพิเศษไม่ทำเวชปฏิบัติส่วนตัว หรือปฏิบัติงาน รพ.เอกชน</td>
            <td class="text-end"><?=$query3['total']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query3['m10']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query3['m11']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query3['m12']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query3['m1']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query3['m2']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query3['m3']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query3['m4']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query3['m5']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query3['m6']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query3['m7']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query3['m8']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query3['m9']?></td>

        </tr>
          <?php
        $sql5 = "SELECT t.code,t.title,c.category_id,c.code,c.title,i.code,i.title,
                   COALESCE(SUM(o.order_price),0) AS total,
                    COALESCE(SUM(o.month_1),0) AS m1,
                    COALESCE(SUM(o.month_2),0) AS m2,
                    COALESCE(SUM(o.month_3),0) AS m3,
                    COALESCE(SUM(o.month_4),0) AS m4,
                    COALESCE(SUM(o.month_5),0) AS m5,
                    COALESCE(SUM(o.month_6),0) AS m6,
                    COALESCE(SUM(o.month_7),0) AS m7,
                    COALESCE(SUM(o.month_8),0) AS m8,
                    COALESCE(SUM(o.month_9),0) AS m9,
                    COALESCE(SUM(o.month_10),0) AS m10,
                    COALESCE(SUM(o.month_11),0) AS m11,
                    COALESCE(SUM(o.month_12),0) AS m12
                    FROM `plan_order` o
                    LEFT JOIN categorise i ON i.code = o.plan_item_id
                    LEFT JOIN categorise c ON c.code = i.category_id
                    LEFT JOIN categorise t ON t.code = c.category_id
                    WHERE i.name = 'plan_item'
                    AND c.name = 'plan_category'
                    AND t.name = 'plan_type'
                    AND c.category_id = 'PER'
                    AND c.code = 'PER_05'";

        $query5 = Yii::$app->db->createCommand($sql5)->queryOne();
        ?>
       
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าตอบแทนเบี้ยเลี้ยงเหมาจ่าย (ฉ.11)</td>
            <td class="text-end"><?=$query5['total']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query5['m10']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query5['m11']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query5['m12']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query5['m1']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query5['m2']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query5['m3']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query5['m4']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query5['m5']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query5['m6']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query5['m7']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query5['m8']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query5['m9']?></td>

        </tr>
          <?php
        $sql6 = "SELECT t.code,t.title,c.category_id,c.code,c.title,i.code,i.title,
                   COALESCE(SUM(o.order_price),0) AS total,
                    COALESCE(SUM(o.month_1),0) AS m1,
                    COALESCE(SUM(o.month_2),0) AS m2,
                    COALESCE(SUM(o.month_3),0) AS m3,
                    COALESCE(SUM(o.month_4),0) AS m4,
                    COALESCE(SUM(o.month_5),0) AS m5,
                    COALESCE(SUM(o.month_6),0) AS m6,
                    COALESCE(SUM(o.month_7),0) AS m7,
                    COALESCE(SUM(o.month_8),0) AS m8,
                    COALESCE(SUM(o.month_9),0) AS m9,
                    COALESCE(SUM(o.month_10),0) AS m10,
                    COALESCE(SUM(o.month_11),0) AS m11,
                    COALESCE(SUM(o.month_12),0) AS m12
                    FROM `plan_order` o
                    LEFT JOIN categorise i ON i.code = o.plan_item_id
                    LEFT JOIN categorise c ON c.code = i.category_id
                    LEFT JOIN categorise t ON t.code = c.category_id
                    WHERE i.name = 'plan_item'
                    AND c.name = 'plan_category'
                    AND t.name = 'plan_type'
                    AND c.category_id = 'PER'
                    AND c.code = 'PER_06'";

        $query6 = Yii::$app->db->createCommand($sql6)->queryOne();
        ?>
         
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าตอบแทนตามผลการปฏิบัติงาน (ฉ.12)</td>
            <td class="text-end"><?=$query6['total']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query6['m10']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query6['m11']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query6['m12']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query6['m1']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query6['m2']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query6['m3']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query6['m4']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query6['m5']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query6['m6']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query6['m7']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query6['m8']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query6['m9']?></td>

        </tr>
         <?php
        $sql7 = "SELECT t.code,t.title,c.category_id,c.code,c.title,i.code,i.title,
                   COALESCE(SUM(o.order_price),0) AS total,
                    COALESCE(SUM(o.month_1),0) AS m1,
                    COALESCE(SUM(o.month_2),0) AS m2,
                    COALESCE(SUM(o.month_3),0) AS m3,
                    COALESCE(SUM(o.month_4),0) AS m4,
                    COALESCE(SUM(o.month_5),0) AS m5,
                    COALESCE(SUM(o.month_6),0) AS m6,
                    COALESCE(SUM(o.month_7),0) AS m7,
                    COALESCE(SUM(o.month_8),0) AS m8,
                    COALESCE(SUM(o.month_9),0) AS m9,
                    COALESCE(SUM(o.month_10),0) AS m10,
                    COALESCE(SUM(o.month_11),0) AS m11,
                    COALESCE(SUM(o.month_12),0) AS m12
                    FROM `plan_order` o
                    LEFT JOIN categorise i ON i.code = o.plan_item_id
                    LEFT JOIN categorise c ON c.code = i.category_id
                    LEFT JOIN categorise t ON t.code = c.category_id
                    WHERE i.name = 'plan_item'
                    AND c.name = 'plan_category'
                    AND t.name = 'plan_type'
                    AND c.category_id = 'PER'
                    AND c.code = 'PER_07'";

        $query7 = Yii::$app->db->createCommand($sql7)->queryOne();
        ?>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">เงินเพิ่ม (พ.ต.ส)</td>
            <td class="text-end"><?=$query7['total']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query7['m10']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query7['m11']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query7['m12']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query7['m1']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query7['m2']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query7['m3']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query7['m4']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query7['m5']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query7['m6']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query7['m7']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query7['m8']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query7['m9']?></td>

        </tr>

         <?php
        $sql8 = "SELECT t.code,t.title,c.category_id,c.code,c.title,i.code,i.title,
                   COALESCE(SUM(o.order_price),0) AS total,
                    COALESCE(SUM(o.month_1),0) AS m1,
                    COALESCE(SUM(o.month_2),0) AS m2,
                    COALESCE(SUM(o.month_3),0) AS m3,
                    COALESCE(SUM(o.month_4),0) AS m4,
                    COALESCE(SUM(o.month_5),0) AS m5,
                    COALESCE(SUM(o.month_6),0) AS m6,
                    COALESCE(SUM(o.month_7),0) AS m7,
                    COALESCE(SUM(o.month_8),0) AS m8,
                    COALESCE(SUM(o.month_9),0) AS m9,
                    COALESCE(SUM(o.month_10),0) AS m10,
                    COALESCE(SUM(o.month_11),0) AS m11,
                    COALESCE(SUM(o.month_12),0) AS m12
                    FROM `plan_order` o
                    LEFT JOIN categorise i ON i.code = o.plan_item_id
                    LEFT JOIN categorise c ON c.code = i.category_id
                    LEFT JOIN categorise t ON t.code = c.category_id
                    WHERE i.name = 'plan_item'
                    AND c.name = 'plan_category'
                    AND t.name = 'plan_type'
                    AND c.category_id = 'PER'
                    AND c.code = 'PER_08'";

        $query8 = Yii::$app->db->createCommand($sql8)->queryOne();
        ?>
      
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าตอบแทนเจ้าหน้าที่ปฏิบัติงานของเจ้าหน้าที่ (นอกเวลา) ฉ5</td>
            <td class="text-end"><?=$query8['total']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query8['m10']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query8['m11']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query8['m12']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query8['m1']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query8['m2']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query8['m3']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query8['m4']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query8['m5']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query8['m6']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query8['m7']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query8['m8']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query8['m9']?></td>

        </tr>

        <?php
        $sql9 = "SELECT t.code,t.title,c.category_id,c.code,c.title,i.code,i.title,
                   COALESCE(SUM(o.order_price),0) AS total,
                    COALESCE(SUM(o.month_1),0) AS m1,
                    COALESCE(SUM(o.month_2),0) AS m2,
                    COALESCE(SUM(o.month_3),0) AS m3,
                    COALESCE(SUM(o.month_4),0) AS m4,
                    COALESCE(SUM(o.month_5),0) AS m5,
                    COALESCE(SUM(o.month_6),0) AS m6,
                    COALESCE(SUM(o.month_7),0) AS m7,
                    COALESCE(SUM(o.month_8),0) AS m8,
                    COALESCE(SUM(o.month_9),0) AS m9,
                    COALESCE(SUM(o.month_10),0) AS m10,
                    COALESCE(SUM(o.month_11),0) AS m11,
                    COALESCE(SUM(o.month_12),0) AS m12
                    FROM `plan_order` o
                    LEFT JOIN categorise i ON i.code = o.plan_item_id
                    LEFT JOIN categorise c ON c.code = i.category_id
                    LEFT JOIN categorise t ON t.code = c.category_id
                    WHERE i.name = 'plan_item'
                    AND c.name = 'plan_category'
                    AND t.name = 'plan_type'
                    AND c.category_id = 'PER'
                    AND c.code = 'PER_09'";

        $query9 = Yii::$app->db->createCommand($sql9)->queryOne();
        ?>
         
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าตอบแทนเจ้าหน้าที่ปฏิบัติงานในคลินิกพิเศษเฉพาะทางนอกเวลาราชการ (SMC)</td>
            <td class="text-end"><?=$query9['total']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query9['m10']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query9['m11']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query9['m12']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query9['m1']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query9['m2']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query9['m3']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query9['m4']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query9['m5']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query9['m6']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query9['m7']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query9['m8']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query9['m9']?></td>

        </tr>

          
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าตอบแทนอื่น</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>

        </tr>
         <?php
        $sql10 = "SELECT t.code,t.title,c.category_id,c.code,c.title,i.code,i.title,
                   COALESCE(SUM(o.order_price),0) AS total,
                    COALESCE(SUM(o.month_1),0) AS m1,
                    COALESCE(SUM(o.month_2),0) AS m2,
                    COALESCE(SUM(o.month_3),0) AS m3,
                    COALESCE(SUM(o.month_4),0) AS m4,
                    COALESCE(SUM(o.month_5),0) AS m5,
                    COALESCE(SUM(o.month_6),0) AS m6,
                    COALESCE(SUM(o.month_7),0) AS m7,
                    COALESCE(SUM(o.month_8),0) AS m8,
                    COALESCE(SUM(o.month_9),0) AS m9,
                    COALESCE(SUM(o.month_10),0) AS m10,
                    COALESCE(SUM(o.month_11),0) AS m11,
                    COALESCE(SUM(o.month_12),0) AS m12
                    FROM `plan_order` o
                    LEFT JOIN categorise i ON i.code = o.plan_item_id
                    LEFT JOIN categorise c ON c.code = i.category_id
                    LEFT JOIN categorise t ON t.code = c.category_id
                    WHERE i.name = 'plan_item'
                    AND c.name = 'plan_category'
                    AND t.name = 'plan_type'
                    AND c.category_id = 'PER'
                    AND c.code = 'PER_10'";

        $query10 = Yii::$app->db->createCommand($sql10)->queryOne();
        ?>
         
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">เงินค่าใช้จ่ายบุคลากรอื่น</td>
            <td class="text-end"><?=$query10['total']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query10['m10']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query10['m11']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query10['m12']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query10['m1']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query10['m2']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query10['m3']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query10['m4']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query10['m5']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query10['m6']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query10['m7']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query10['m8']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query10['m9']?></td>

        </tr>

          <?php
        $sql11 = "SELECT t.code,t.title,c.category_id,c.code,c.title,i.code,i.title,
                   COALESCE(SUM(o.order_price),0) AS total,
                    COALESCE(SUM(o.month_1),0) AS m1,
                    COALESCE(SUM(o.month_2),0) AS m2,
                    COALESCE(SUM(o.month_3),0) AS m3,
                    COALESCE(SUM(o.month_4),0) AS m4,
                    COALESCE(SUM(o.month_5),0) AS m5,
                    COALESCE(SUM(o.month_6),0) AS m6,
                    COALESCE(SUM(o.month_7),0) AS m7,
                    COALESCE(SUM(o.month_8),0) AS m8,
                    COALESCE(SUM(o.month_9),0) AS m9,
                    COALESCE(SUM(o.month_10),0) AS m10,
                    COALESCE(SUM(o.month_11),0) AS m11,
                    COALESCE(SUM(o.month_12),0) AS m12
                    FROM `plan_order` o
                    LEFT JOIN categorise i ON i.code = o.plan_item_id
                    LEFT JOIN categorise c ON c.code = i.category_id
                    LEFT JOIN categorise t ON t.code = c.category_id
                    WHERE i.name = 'plan_item'
                    AND c.name = 'plan_category'
                    AND t.name = 'plan_type'
                    AND c.category_id = 'PER'
                    AND c.code = 'PER_11'";

        $query11 = Yii::$app->db->createCommand($sql11)->queryOne();
        ?>
        <!-- 0.00 เป็น <?=$query11['m10']?> เริ่มจาก m10-m9 -->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าตอบแทนเบี้ยเลี้ยงเหมาจ่าย (ฉ.10)</td>
            <td class="text-end"><?=$query11['total']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query11['m10']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query11['m11']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$query11['m12']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query11['m1']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query11['m2']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$query11['m3']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query11['m4']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query11['m5']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$query11['m6']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query11['m7']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query11['m8']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$query11['m9']?></td>

        </tr>

        <tr>
            <td width="10px"></td>
            <td colspan="16" class="bg-warning text-dark bg-opacity-25"><i class="fa-solid fa-chevron-right me-1"></i>รายจ่ายจากการดำเนินงาน</td>

        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่ายา</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>

        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าเวชภัณฑ์มิใช่ยา</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>

        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าวัสดุ</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>

        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าสาธารณูปโภค</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>

        </tr>
        <?php
        //ค่าใช้สอย
        $queryExpense = "SELECT t.code,t.title,c.category_id,c.code,c.title,i.code,i.title,
                    SUM(o.order_price) as total,
                    SUM(o.month_1) as m1,
                    SUM(o.month_2) as m2,
                    SUM(o.month_3) as m3,
                    SUM(o.month_4) as m4,
                    SUM(o.month_5) as m5,
                    SUM(o.month_6) as m6,
                    SUM(o.month_7) as m7,
                    SUM(o.month_8) as m8,
                    SUM(o.month_9) as m9,
                    SUM(o.month_10) as m10,
                    SUM(o.month_11) as m11,
                    SUM(o.month_12) as m12
                    FROM `plan_order` o
                    LEFT JOIN categorise i ON i.code = o.plan_item_id
                    LEFT JOIN categorise c ON c.code = i.category_id
                    LEFT JOIN categorise t ON t.code = c.category_id
                    WHERE i.name = 'plan_item'
                    AND c.name = 'plan_category'
                    AND t.name = 'plan_type'
                    AND c.category_id = 'OPS'
                    AND c.code = 'OPS_05'";

        $OPS = Yii::$app->db->createCommand($queryExpense)->queryOne();
        ?>
        <tr class="yellow">

            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าใช้สอย</td>
            <td class="text-end"><?=$OPS['total']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$OPS['m10']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$OPS['m11']?></td>
            <td class="text-end bg-primary text-dark bg-opacity-25"><?=$OPS['m12']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$OPS['m1']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$OPS['m2']?></td>
            <td class="text-end bg-secondary text-dark bg-opacity-25"><?=$OPS['m3']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$OPS['m4']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$OPS['m5']?></td>
            <td class="text-end bg-success text-dark bg-opacity-25"><?=$OPS['m6']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$OPS['m7']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$OPS['m8']?></td>
            <td class="text-end bg-danger text-dark bg-opacity-25"><?=$OPS['m9']?></td>

        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าใช้จ่ายดำเนินงานอื่น</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>

        </tr>

        <tr>
            <td width="10px"></td>
            <td colspan="16" class="bg-warning text-dark bg-opacity-25"><i class="fa-solid fa-chevron-right me-1"></i>รายจ่ายลงทุน</td>
        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าครุภัณฑ์</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>

        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าที่ดินและสิ่งก่อสร้าง</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>

        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">ค่าครุภัณฑ์ต่ำกว่าเกณฑ์</td>
            <td class="text-end">4,500.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">4,500.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>

        </tr>
        <tr>
            <td width="10px"></td>
            <td colspan="16" class="bg-warning text-dark bg-opacity-25"><i class="fa-solid fa-chevron-right me-1"></i>รายจ่ายอื่น</td>
        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายจ่ายสนับสนุน รพ.สต. รพช. รพท. รพศ. สสอ. สสจ.</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายจ่ายอื่นๆ</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
        </tr>
        <tr>
            <td width="10px"></td>
            <td colspan="16" class="bg-warning text-dark bg-opacity-25"><i class="fa-solid fa-chevron-right me-1"></i>รายรับจากการดำเนินงาน</td>
        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลสำหรับโครงการสุขภาพถ้วนหน้า UC</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลสำหรับโครงการสุขภาพถ้วนหน้า UC งบลงทุน</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับจากระบบปฏิบัติการฉุกเฉิน (EMS)</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลเบิกจ่ายตรงกรมบัญชีกลาง</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลผู้ป่วยเบิกต้นสังกัด</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลเบิกจาก อปท.</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลจากกองทุนประกันสังคม</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลแรงงานต่างด้าว</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับค่ารักษาพยาบาลและการบริการอื่น</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr>
            <td width="10px"></td>
            <td colspan="16" class="bg-warning text-dark bg-opacity-25"><i class="fa-solid fa-chevron-right me-1"></i>รายรับอื่น</td>
        </tr>
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับเงินช่วยเหลือ</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับเงินอุดหนุน</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับจากการบริจาค</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับดอกเบี้ยเงินฝากธนาคาร</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับอื่น</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr class="yellow">
            <td width="10px"></td>
            <td width="10px"></td>
            <td colspan="2">รายรับไม่ทราบแหล่งที่มา</td>
            <td class="text-end">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-secondary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
        </tr> <!---->
        <tr>
            <td></td>
            <td colspan="16" class="bg-warning text-dark bg-opacity-25"><i class="fa-solid fa-chevron-right me-1"></i>งบกลาง (ไม่เกินร้อยละ 2-3.5 ของประมาณการรายจ่าย)</td>
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
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-primary text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-success text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
            <td class="text-end bg-danger text-dark bg-opacity-25">0.00</td>
        </tr>
    </tbody>
</table>