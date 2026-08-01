<?php

use yii\db\Migration;
use app\modules\settings\models\OrgUnit;
use app\modules\plan\components\PlanHelper;

/**
 * เติมข้อมูลทะเบียนหน่วยงานครั้งแรก สำหรับปีงบที่เปิดทำแผนปัจจุบัน
 *  - syncStructure  : ดึงหน่วยจากผังโครงสร้าง (tree)
 *  - importLegacyMedsop : ย้ายอักษรย่อเดิม + ทีมประสานจาก medsop
 */
class m260801_000003_populate_org_unit extends Migration
{
    public function safeUp()
    {
        $year = (int) PlanHelper::currentPlanYear();

        $s = OrgUnit::syncStructure($year);
        $l = OrgUnit::importLegacyMedsop($year);

        echo "    > org_unit ปี {$year}: โครงสร้าง +{$s['added']} (มีอยู่ {$s['updated']}), "
            . "อักษรย่อ {$l['orgCodes']}, ทีมประสาน {$l['teams']}\n";
    }

    public function safeDown()
    {
        // ลบเฉพาะข้อมูลที่ migration นี้เติม (ไม่แตะตาราง)
        $this->delete('org_unit');
    }
}
