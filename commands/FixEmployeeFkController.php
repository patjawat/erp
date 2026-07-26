<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * ซ่อม foreign key ของตารางที่อ้าง `employees` แล้วค้างชี้ตารางเก่าที่ถูก rename ไป
 *
 * อาการ: ตาราง `employees` เคยถูก rename (เช่นเป็น `delete_employees`) แล้วสร้างตัวใหม่แทน
 * MySQL ย้าย FK ตามการ rename ไปด้วย ทำให้ FK ค้างชี้ตารางเก่า พนักงานที่ถูกเพิ่มหลังจากนั้น
 * (id เกินช่วงของตารางเก่า) จึง insert เข้าตารางลูกไม่ได้ ได้ ERROR 1452 — ลงเวลาไม่ได้,
 * รับ notify ไม่ได้, ส่ง/รับ appreciation ไม่ได้, ผูก JD ไม่ได้
 *
 * ไม่ได้เกิดทุก DB — เป็นเศษจากการซ่อมข้อมูลด้วยมือในบาง DB เท่านั้น จึงทำเป็นคำสั่งเรียกเมื่อเจอปัญหา
 * ไม่ใช่ migration ที่วิ่งทุกที่
 *
 * ตรวจอย่างเดียว (ค่าเริ่มต้น ไม่แตะฐานข้อมูล):
 *   docker exec dansai php /app/yii fix-employee-fk/check
 *
 * ซ่อมจริง:
 *   docker exec dansai php /app/yii fix-employee-fk/check --apply
 *
 * ซ่อมจริง + ลบตารางเก่าที่ไม่มีใครอ้างแล้วทิ้ง:
 *   docker exec dansai php /app/yii fix-employee-fk/check --apply --dropLegacy
 */
class FixEmployeeFkController extends Controller
{
    /** ลงมือแก้จริง (ค่าเริ่มต้นเป็นตรวจอย่างเดียว) */
    public $apply = false;

    /** ลบตารางเก่า (delete_*) ทิ้งหลังย้าย FK เสร็จ */
    public $dropLegacy = false;

    /** ชื่อ component ฐานข้อมูล */
    public $db = 'db';

    /** FK ที่ต้องชี้ `employees` — [ตารางลูก, ชื่อ constraint, คอลัมน์] */
    private const EMP_FKS = [
        ['appreciation', 'fk-appreciation-from_emp', 'from_emp_id'],
        ['appreciation', 'fk-appreciation-to_emp', 'to_emp_id'],
        ['appreciation_challenge_progress', 'fk-appreciation_challenge_progress-emp', 'emp_id'],
        ['appreciation_like', 'fk-appreciation_like-emp', 'emp_id'],
        ['checkin_record', 'fk_checkin_record_emp', 'emp_id'],
        ['jd_change_request', 'fk-jd_change_request-emp', 'emp_id'],
        ['jd_employee', 'fk-jd_employee-emp', 'emp_id'],
        ['jd_employee_acknowledgement', 'fk-jd_ack-emp', 'emp_id'],
        ['notify', 'fk-notify-recipient_emp', 'recipient_emp_id'],
    ];

    /** ตารางเก่าที่ลบได้เมื่อไม่มี FK อ้างถึงแล้ว */
    private const LEGACY_TABLES = ['delete_employees', 'delete_employee_detail', 'delete_tree'];

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['apply', 'dropLegacy', 'db']);
    }

    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), ['a' => 'apply']);
    }

    /**
     * ตรวจสภาพ FK แล้วซ่อมเมื่อสั่ง --apply
     */
    public function actionCheck()
    {
        $db = Yii::$app->get($this->db, false);
        if ($db === null) {
            $this->stderr("ไม่พบ db component '{$this->db}'\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $schema = $db->createCommand('SELECT DATABASE()')->queryScalar();

        $this->stdout(($this->apply ? 'ซ่อมจริง' : 'ตรวจอย่างเดียว (ยังไม่แก้)') . " — DB: {$schema}\n\n", Console::FG_CYAN);

        $broken = [];
        foreach (self::EMP_FKS as [$table, $fk, $column]) {
            if (!$this->tableExists($db, $schema, $table)) {
                $this->stdout("  ข้าม  {$table}.{$fk} — ไม่มีตารางนี้ใน DB นี้\n", Console::FG_GREY);
                continue;
            }
            $parent = $this->referencedTable($db, $schema, $table, $fk);
            if ($parent === null) {
                $this->stdout("  ข้าม  {$table}.{$fk} — ไม่มี constraint นี้\n", Console::FG_GREY);
                continue;
            }
            if ($parent === 'employees') {
                $this->stdout("  ผ่าน  {$table}.{$fk} — ชี้ employees ถูกต้อง\n", Console::FG_GREEN);
                continue;
            }
            $orphans = (int)$db->createCommand(
                "SELECT COUNT(*) FROM `{$table}` c
                 LEFT JOIN `employees` e ON e.id = c.`{$column}`
                 WHERE c.`{$column}` IS NOT NULL AND e.id IS NULL"
            )->queryScalar();
            $broken[] = compact('table', 'fk', 'column', 'parent', 'orphans');
            $this->stdout("  ผิด   {$table}.{$fk} — ชี้ {$parent} (orphan เทียบ employees: {$orphans})\n", Console::FG_RED);
        }

        $this->stdout("\n");

        if ($broken === []) {
            $this->stdout("FK ทุกตัวชี้ employees ถูกต้องแล้ว ไม่ต้องซ่อม\n", Console::FG_GREEN);
            return $this->handleLegacyTables($db, $schema);
        }

        $blocked = array_filter($broken, static fn($b) => $b['orphans'] > 0);
        if ($blocked !== []) {
            $this->stderr("หยุด: มีแถวลูกที่หา id ตรงกันใน employees ไม่เจอ ต้องแก้ข้อมูลก่อน\n", Console::FG_RED);
            foreach ($blocked as $b) {
                $this->stderr("  {$b['table']}.{$b['column']}: {$b['orphans']} แถว\n", Console::FG_RED);
            }
            return ExitCode::DATAERR;
        }

        // แสดง SQL ที่จะรัน เพื่อให้ตรวจได้ก่อน หรือก๊อปไปรันมือเองก็ได้
        $this->stdout("SQL ที่จะรัน:\n", Console::FG_YELLOW);
        foreach ($broken as $b) {
            $this->stdout("  ALTER TABLE `{$b['table']}` DROP FOREIGN KEY `{$b['fk']}`;\n");
            $this->stdout(
                "  ALTER TABLE `{$b['table']}` ADD CONSTRAINT `{$b['fk']}` FOREIGN KEY (`{$b['column']}`)"
                . " REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;\n"
            );
        }
        $this->stdout("\n");

        if (!$this->apply) {
            $this->stdout("ยังไม่ได้แก้อะไร สั่งซ่อมด้วย --apply\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }

        foreach ($broken as $b) {
            $db->createCommand()->dropForeignKey($b['fk'], $b['table'])->execute();
            $db->createCommand()->addForeignKey(
                $b['fk'], $b['table'], $b['column'], 'employees', 'id', 'CASCADE', 'CASCADE'
            )->execute();
            $this->stdout("  ย้ายแล้ว {$b['table']}.{$b['fk']}: {$b['parent']} -> employees\n", Console::FG_GREEN);
        }
        $this->stdout("\nย้าย FK เสร็จ " . count($broken) . " ตัว\n", Console::FG_GREEN);

        return $this->handleLegacyTables($db, $schema);
    }

    /**
     * ลบตารางเก่าที่ไม่มี FK อ้างถึงแล้ว (เฉพาะเมื่อสั่ง --apply --dropLegacy)
     */
    private function handleLegacyTables($db, string $schema): int
    {
        $present = array_values(array_filter(
            self::LEGACY_TABLES,
            fn($t) => $this->tableExists($db, $schema, $t)
        ));
        if ($present === []) {
            return ExitCode::OK;
        }

        $this->stdout("\nตารางเก่าที่ยังเหลืออยู่:\n", Console::FG_YELLOW);
        $droppable = [];
        foreach ($present as $table) {
            $refs = $this->referencingConstraints($db, $schema, $table);
            $rows = (int)$db->createCommand("SELECT COUNT(*) FROM `{$table}`")->queryScalar();
            if ($refs !== []) {
                $this->stdout("  {$table} ({$rows} แถว) — ลบไม่ได้ ยังมี FK อ้างถึง: "
                    . implode(', ', $refs) . "\n", Console::FG_RED);
                continue;
            }
            $droppable[] = $table;
            $this->stdout("  {$table} ({$rows} แถว) — ไม่มีอะไรอ้างถึงแล้ว ลบได้\n");
        }

        if ($droppable === []) {
            return ExitCode::OK;
        }
        if (!$this->apply || !$this->dropLegacy) {
            $this->stdout("\nสั่งลบด้วย --apply --dropLegacy (สำรองข้อมูลก่อนลบด้วย ลบแล้วกู้ไม่ได้)\n", Console::FG_YELLOW);
            return ExitCode::OK;
        }
        if ($this->interactive && !$this->confirm('ลบตารางเหล่านี้ถาวร ยืนยัน?')) {
            $this->stdout("ยกเลิก ไม่ได้ลบอะไร\n");
            return ExitCode::OK;
        }

        foreach ($droppable as $table) {
            $db->createCommand()->dropTable($table)->execute();
            $this->stdout("  ลบแล้ว {$table}\n", Console::FG_GREEN);
        }
        return ExitCode::OK;
    }

    private function tableExists($db, string $schema, string $table): bool
    {
        return (int)$db->createCommand(
            'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = :s AND TABLE_NAME = :t',
            [':s' => $schema, ':t' => $table]
        )->queryScalar() > 0;
    }

    /** ตารางที่ constraint นี้อ้างถึง หรือ null ถ้าไม่มี constraint */
    private function referencedTable($db, string $schema, string $table, string $fk): ?string
    {
        $name = $db->createCommand(
            'SELECT REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = :s AND TABLE_NAME = :t AND CONSTRAINT_NAME = :c
               AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1',
            [':s' => $schema, ':t' => $table, ':c' => $fk]
        )->queryScalar();

        return $name === false || $name === null ? null : (string)$name;
    }

    /** @return string[] constraint ที่ยังอ้างถึงตารางนี้ (รูปแบบ "ตารางลูก.ชื่อ constraint") */
    private function referencingConstraints($db, string $schema, string $table): array
    {
        return $db->createCommand(
            "SELECT CONCAT(TABLE_NAME, '.', CONSTRAINT_NAME) FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = :s AND REFERENCED_TABLE_NAME = :t",
            [':s' => $schema, ':t' => $table]
        )->queryColumn();
    }
}
