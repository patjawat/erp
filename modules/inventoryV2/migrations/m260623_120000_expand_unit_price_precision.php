<?php

use yii\db\Migration;

/**
 * ขยาย stock_detail.unit_price จาก DECIMAL(15,2) → DECIMAL(15,6)
 *
 * เหตุผล:
 *   CSV import มักมี unit_price ที่คำนวณจาก (มูลค่ารวม / จำนวน)
 *   ออกมาเป็นทศนิยมยาว เช่น 12.82643473, 1097.169811, 44.81818182
 *
 *   เดิม DECIMAL(15,2) round ทันทีตอนเก็บ
 *   → 12.82643473 → 12.83
 *   → 1739 × 12.83 = 22,311.37 (ผิดจาก CSV ที่ตั้งใจ 22,305.17 = +6.20 บาท)
 *
 *   ขยายเป็น 6 ทศนิยม
 *   → 12.82643473 → 12.826435 (round ที่ 6 หลัก)
 *   → 1739 × 12.826435 = 22,305.17 ตรงตามต้นทาง (error < 1 สตางค์)
 *
 * Schema change:
 *   - DECIMAL(15,2) → DECIMAL(15,6)
 *   - max value: 9 digits ก่อนจุด = 999,999,999.999999
 *     ใช้กับราคาวัสดุปกติได้สบาย (สูงสุดเดิม < 100M บาท)
 *
 * Backward compat:
 *   - ข้อมูลเดิมที่ round ไปแล้ว ยังอยู่เหมือนเดิม (เก็บไว้ที่ 2 ทศนิยม)
 *     เช่น 12.83 → 12.830000 (รูปแบบเปลี่ยน แต่ค่าเท่าเดิม)
 *   - ถ้าต้องการความแม่นยำ ต้อง re-import ใบรับเข้าใหม่
 *
 * safeDown:
 *   - กลับ DECIMAL(15,6) → DECIMAL(15,2) จะ round ข้อมูล: เสีย precision
 *   - ใส่ comment เตือน
 */
class m260623_120000_expand_unit_price_precision extends Migration
{
    public function safeUp()
    {
        if (!$this->tableExists('stock_detail')) {
            echo "  [SKIP] ตาราง stock_detail ไม่มีอยู่\n";
            return true;
        }
        $this->alterColumn(
            '{{%stock_detail}}',
            'unit_price',
            $this->decimal(15, 6)->comment('ราคาทุนต่อหน่วย (6 ทศนิยมเพื่อรองรับ CSV import)')
        );
        echo "  [OK] stock_detail.unit_price → DECIMAL(15,6)\n";
        return true;
    }

    public function safeDown()
    {
        if (!$this->tableExists('stock_detail')) {
            return true;
        }
        echo "  [WARN] กลับเป็น DECIMAL(15,2) — ข้อมูลทศนิยมเกิน 2 หลักจะถูก round ทิ้ง\n";
        $this->alterColumn(
            '{{%stock_detail}}',
            'unit_price',
            $this->decimal(15, 2)->comment('ราคาทุนต่อหน่วย')
        );
        return true;
    }

    private function tableExists($name)
    {
        return $this->db->getTableSchema($name, true) !== null;
    }
}
