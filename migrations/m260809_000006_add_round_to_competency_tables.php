<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * ย้ายการกำหนด "ระดับที่คาดหวัง" และ "ผู้ประเมิน" จากรายปี ไปผูกกับรอบประเมิน
 *
 * แบบฟอร์มเดิมแยกชีต Core_1 / Core_2 คนละรอบ และแต่ละชีตมีระดับที่คาดหวังของตัวเอง
 * เพราะคนที่เลื่อนตำแหน่งกลางปีจะถูกคาดหวังไม่เท่ากันในสองรอบ
 * ส่วนกรณีที่ไม่มีอะไรเปลี่ยน ใช้การคัดลอกจากรอบก่อนแทนการกรอกใหม่
 *
 * assignment ตัด fiscal_year ทิ้ง เพราะ round อ้างปีงบประมาณอยู่แล้ว ไม่ต้องเก็บซ้ำให้เพี้ยนกัน
 *
 * หมายเหตุลำดับคำสั่ง: ต้องสร้าง index ตัวใหม่ก่อนจึงจะลบตัวเก่าได้
 * เพราะ index เดิมขึ้นต้นด้วย emp_id ซึ่งรองรับ foreign key อยู่ MySQL จะไม่ยอมให้ลบทิ้งลอย ๆ
 */
final class m260809_000006_add_round_to_competency_tables extends Migration
{
    public function safeUp(): void
    {
        $this->migrateExpectation();
        $this->migrateAssignment();
    }

    private function migrateExpectation(): void
    {
        $table = '{{%hr_competency_expectation}}';

        if (!$this->hasColumn('hr_competency_expectation', 'round_id')) {
            $this->addColumn($table, 'round_id', $this->integer()->null()->after('emp_id'));
        }

        // ข้อมูลเดิมกำหนดไว้ก่อนมีระบบรอบ — ถือเป็นรอบที่ 1 ของปีนั้น
        $this->execute("
            UPDATE {{%hr_competency_expectation}} x
            JOIN {{%hr_competency_year}} cy ON cy.id = x.competency_year_id
            JOIN {{%hr_appraisal_round}} r ON r.fiscal_year = cy.fiscal_year AND r.round_no = 1
            SET x.round_id = r.id
            WHERE x.round_id IS NULL
        ");
        $this->delete($table, ['round_id' => null]);
        $this->alterColumn($table, 'round_id', $this->integer()->notNull());

        if (!$this->hasIndex('hr_competency_expectation', 'uq-hr_competency_expectation-round_emp_comp')) {
            $this->createIndex('uq-hr_competency_expectation-round_emp_comp', $table,
                ['round_id', 'emp_id', 'competency_year_id'], true);
        }
        // index นี้ต้องมีก่อน จึงจะลบ unique เดิมที่รองรับ FK ของ emp_id ได้
        if (!$this->hasIndex('hr_competency_expectation', 'idx-hr_competency_expectation-emp')) {
            $this->createIndex('idx-hr_competency_expectation-emp', $table, ['emp_id', 'round_id']);
        }
        if ($this->hasIndex('hr_competency_expectation', 'uq-hr_competency_expectation-emp_year')) {
            $this->dropIndex('uq-hr_competency_expectation-emp_year', $table);
        }
        if (!$this->hasForeignKey('hr_competency_expectation', 'fk-hr_competency_expectation-round')) {
            $this->addForeignKey('fk-hr_competency_expectation-round', $table, 'round_id',
                '{{%hr_appraisal_round}}', 'id', 'CASCADE', 'CASCADE');
        }
    }

    private function migrateAssignment(): void
    {
        $table = '{{%hr_competency_assignment}}';

        if (!$this->hasColumn('hr_competency_assignment', 'round_id')) {
            $this->addColumn($table, 'round_id', $this->integer()->null()->after('emp_id'));
        }

        if ($this->hasColumn('hr_competency_assignment', 'fiscal_year')) {
            $this->execute("
                UPDATE {{%hr_competency_assignment}} a
                JOIN {{%hr_appraisal_round}} r ON r.fiscal_year = a.fiscal_year AND r.round_no = 1
                SET a.round_id = r.id
                WHERE a.round_id IS NULL
            ");
        }
        $this->delete($table, ['round_id' => null]);
        $this->alterColumn($table, 'round_id', $this->integer()->notNull());

        if (!$this->hasIndex('hr_competency_assignment', 'uq-hr_competency_assignment-round_emp')) {
            $this->createIndex('uq-hr_competency_assignment-round_emp', $table, ['round_id', 'emp_id'], true);
        }
        if (!$this->hasIndex('hr_competency_assignment', 'idx-hr_competency_assignment-emp')) {
            $this->createIndex('idx-hr_competency_assignment-emp', $table, ['emp_id']);
        }
        if ($this->hasIndex('hr_competency_assignment', 'uq-hr_competency_assignment-emp_year')) {
            $this->dropIndex('uq-hr_competency_assignment-emp_year', $table);
        }
        // ตัวใหม่ต้องมาก่อน เพราะตัวเก่าขึ้นต้นด้วย evaluator_id ซึ่งรองรับ FK ของผู้ประเมินอยู่
        if (!$this->hasIndex('hr_competency_assignment', 'idx-hr_competency_assignment-evaluator_round')) {
            $this->createIndex('idx-hr_competency_assignment-evaluator_round', $table,
                ['evaluator_id', 'round_id', 'status']);
        }
        if ($this->hasIndex('hr_competency_assignment', 'idx-hr_competency_assignment-evaluator')) {
            $this->dropIndex('idx-hr_competency_assignment-evaluator', $table);
        }
        if ($this->hasColumn('hr_competency_assignment', 'fiscal_year')) {
            $this->dropColumn($table, 'fiscal_year');
        }
        if (!$this->hasForeignKey('hr_competency_assignment', 'fk-hr_competency_assignment-round')) {
            $this->addForeignKey('fk-hr_competency_assignment-round', $table, 'round_id',
                '{{%hr_appraisal_round}}', 'id', 'CASCADE', 'CASCADE');
        }
    }

    private function hasColumn(string $table, string $column): bool
    {
        $schema = $this->db->getTableSchema($table, true);
        return $schema !== null && isset($schema->columns[$column]);
    }

    private function hasIndex(string $table, string $name): bool
    {
        return (int) $this->db->createCommand(
            'SELECT COUNT(*) FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = :t AND index_name = :i',
            [':t' => $table, ':i' => $name]
        )->queryScalar() > 0;
    }

    private function hasForeignKey(string $table, string $name): bool
    {
        return (int) $this->db->createCommand(
            'SELECT COUNT(*) FROM information_schema.table_constraints
             WHERE table_schema = DATABASE() AND table_name = :t
               AND constraint_name = :c AND constraint_type = \'FOREIGN KEY\'',
            [':t' => $table, ':c' => $name]
        )->queryScalar() > 0;
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk-hr_competency_assignment-round', '{{%hr_competency_assignment}}');
        $this->addColumn('{{%hr_competency_assignment}}', 'fiscal_year', $this->integer()->null());
        $this->execute("
            UPDATE {{%hr_competency_assignment}} a
            JOIN {{%hr_appraisal_round}} r ON r.id = a.round_id
            SET a.fiscal_year = r.fiscal_year
        ");
        $this->alterColumn('{{%hr_competency_assignment}}', 'fiscal_year', $this->integer()->notNull());
        $this->createIndex('uq-hr_competency_assignment-emp_year', '{{%hr_competency_assignment}}', ['emp_id', 'fiscal_year'], true);
        $this->createIndex('idx-hr_competency_assignment-evaluator', '{{%hr_competency_assignment}}', ['evaluator_id', 'fiscal_year', 'status']);
        $this->dropIndex('uq-hr_competency_assignment-round_emp', '{{%hr_competency_assignment}}');
        $this->dropIndex('idx-hr_competency_assignment-evaluator_round', '{{%hr_competency_assignment}}');
        $this->dropIndex('idx-hr_competency_assignment-emp', '{{%hr_competency_assignment}}');
        $this->dropColumn('{{%hr_competency_assignment}}', 'round_id');

        $this->dropForeignKey('fk-hr_competency_expectation-round', '{{%hr_competency_expectation}}');
        $this->createIndex('uq-hr_competency_expectation-emp_year', '{{%hr_competency_expectation}}', ['emp_id', 'competency_year_id'], true);
        $this->dropIndex('uq-hr_competency_expectation-round_emp_comp', '{{%hr_competency_expectation}}');
        $this->dropIndex('idx-hr_competency_expectation-emp', '{{%hr_competency_expectation}}');
        $this->dropColumn('{{%hr_competency_expectation}}', 'round_id');
    }
}
