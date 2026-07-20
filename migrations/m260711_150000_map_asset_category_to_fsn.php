<?php

use yii\db\Migration;

/**
 * ทะเบียนครุภัณฑ์ (asset_group_id = 4): flip ให้ asset.fsn_number เก็บ "หมายเลขเต็ม" เท่ากับ asset.code
 *
 *   asset.fsn_number = asset.code   (code คงไว้เป็น mirror)
 *
 * หมายเหตุ: เดิมเคยมี step rename categorise.code = fsn_number + repoint asset_category_id
 *           แต่ยกเลิกแล้ว เพราะ `categorise` เป็นตารางกลางใช้ร่วมทั้งระบบ — การเปลี่ยน code
 *           (COM/MED → FSN) กระทบ dropdown/รายงาน/ลำดับชั้น asset_type→category ทั้งระบบ
 *           จึง "ไม่แตะ" categorise.code และ asset_category_id (คงรหัสสั้นเดิมไว้)
 *
 * Guard: (fsn_number <> code) → idempotent รันซ้ำได้ปลอดภัย
 * ⚠️ ไม่รองรับ revert อัตโนมัติ — กู้จาก backup ถ้าต้องการ
 */
class m260711_150000_map_asset_category_to_fsn extends Migration
{
    public function safeUp()
    {
        $this->execute("
            UPDATE {{%asset}}
            SET fsn_number = code
            WHERE asset_group_id = 4
              AND code IS NOT NULL AND LENGTH(code) > 0
              AND (fsn_number IS NULL OR fsn_number <> code)
        ");
    }

    public function safeDown()
    {
        echo "m260711_150000_map_asset_category_to_fsn ไม่รองรับการ revert อัตโนมัติ (กู้จาก backup แทน)\n";
        return false;
    }
}
