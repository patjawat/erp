<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * เพิ่มฟิลด์ สูตรคำนวณ + หลักฐาน ให้ KPI แต่ละตัว
 * เช่น สูตร = (จำนวนงานพิธีการ*100/จำนวนงานทั้งหมดที่วางแผนไว้)
 *      หลักฐาน = โครงการแผนงาน หนังสือเชิญ รายงานผลการจัดงาน ภาพกิจกรรม
 */
final class m260730_000001_add_kpi_item_formula_evidence extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%kpi_item}}', 'formula', $this->text()->null()->comment('สูตรคำนวณ')->after('unit'));
        $this->addColumn('{{%kpi_item}}', 'evidence', $this->text()->null()->comment('หลักฐาน/เอกสารอ้างอิง')->after('formula'));
    }

    public function safeDown(): void
    {
        $this->dropColumn('{{%kpi_item}}', 'evidence');
        $this->dropColumn('{{%kpi_item}}', 'formula');
    }
}
