<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * เพิ่มสถานะ "ไม่ระบุตัวตน" (unidentified) ในตาราง asset_status
 * ใช้กับครุภัณฑ์ที่พบในระบบแต่ยังระบุเจ้าของ/หมายเลขทะเบียนไม่ได้
 */
class m260625_140000_insert_asset_status_unidentified extends Migration
{
    private const STATUS_ID = 'unidentified';
    private const FALLBACK_STATUS_ID = 'active';

    public function safeUp()
    {
        $exists = (new Query())
            ->from('asset_status')
            ->where(['id' => self::STATUS_ID])
            ->exists($this->db);

        if (!$exists) {
            $this->insert('asset_status', [
                'id'         => self::STATUS_ID,
                'name'       => 'ไม่ระบุตัวตน',
                'color_css'  => 'secondary',
                'color_code' => '#6c757d',
                'sort_order' => 6,
                'is_active'  => 1,
            ]);
        }
    }

    public function safeDown()
    {
        // FK asset.asset_status เป็น RESTRICT — ย้ายแถวที่อ้างอิงกลับเป็น active ก่อนลบ lookup
        $this->update(
            'asset',
            ['asset_status' => self::FALLBACK_STATUS_ID],
            ['asset_status' => self::STATUS_ID]
        );

        $this->delete('asset_status', ['id' => self::STATUS_ID]);
    }
}
