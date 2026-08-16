<?php

use yii\db\Migration;
use yii\db\Query;
use yii\helpers\Json;

/**
 * เติมชุดข้อมูล "แผนบุคลากร" ให้ครบและผูกประเภทบุคลากรไว้กับรายการค่าใช้จ่าย
 *
 * ที่มา: หมวด (plan_category PER_01..PER_11) และประเภทค่าใช้จ่าย (plan_item P1..P11)
 * ของฐานที่ใช้งานจริง ถูกป้อนด้วยมือผ่านหน้าตั้งค่า ไม่ได้อยู่ใน migration ใด
 * (m250815_084655 seed คนละชุด และ seed เฉพาะตอนตาราง plan_item ว่างเท่านั้น)
 * เครื่องที่ติดตั้งไว้นานแล้วจึงเปิดหน้า /me/plan/create-personnel ได้แต่ dropdown ว่าง
 *
 * migration นี้ทำงานซ้ำได้ (idempotent) และไม่แตะข้อมูลเดิม:
 *   - เพิ่มเฉพาะหมวด/รายการที่ยังไม่มี (เทียบด้วยรหัส แล้วเทียบด้วยชื่อในหมวดเดียวกันกันซ้ำ)
 *   - เขียน data_json.employee_type_ids / all_employee_types ให้รายการบุคลากร
 *     (PersonnelPlanTaxonomy อ่านค่านี้แทนการ hardcode รหัสในโค้ด)
 *   - ระบุฐานการจ่ายของ employee_type (รายวัน/รายเดือน) ไว้ใน data_json
 */
class m260817_000001_sync_personnel_plan_taxonomy extends Migration
{
    /** หมวดคำขอบุคลากร: code => [sort, title] */
    private $categories = [
        'PER_01' => [1, '1.1.ค่าจ้างลูกจ้างชั่วคราว / พนักงานกระทรวง'],
        'PER_02' => [2, '1.2.ค่าล่วงเวลางานบริการ / งานสนับสนุน'],
        'PER_03' => [3, '1.3.ค่าตอบแทนการปฏิบัติงานเวรผลัดบ่ายหรือผลัดดึกของเจ้าหน้าที่'],
        'PER_04' => [4, '1.4.ค่าตอบแทนเงินเพิ่มพิเศษไม่ทำเวชปฏิบัติส่วนตัว หรือปฏิบัติงาน รพ.เอกชน'],
        'PER_05' => [5, '1.5.ค่าตอบแทนเบี้ยเลี้ยงเหมาจ่าย (ฉ.11)'],
        'PER_06' => [6, '1.6.ค่าตอบแทนตามผลการปฏิบัติงาน (ฉ.12)'],
        'PER_07' => [7, '1.7.เงินเพิ่ม (พ.ต.ส)'],
        'PER_08' => [8, '1.8.ค่าตอบแทนเจ้าหน้าที่ปฏิบัติงานของเจ้าหน้าที่ (นอกเวลา) ฉ5'],
        'PER_09' => [9, '1.9.ค่าตอบแทนเจ้าหน้าที่ปฏิบัติงานในคลินิกพิเศษเฉพาะทางนอกเวลาราชการ (SMC)'],
        'PER_10' => [10, '1.11.เงินค่าใช้จ่ายบุคลากรอื่น'],
        'PER_11' => [11, '1.12.ค่าตอบแทนเบี้ยเลี้ยงเหมาจ่าย (ฉ.10)'],
    ];

    /**
     * ประเภทค่าใช้จ่ายบุคลากร: code => [sort, category, title, ประเภทบุคลากร, รหัสชุดเก่า]
     * ประเภทบุคลากร: array = ดึงเฉพาะประเภทนั้น, 'all' = ทุกประเภทในหน่วยงาน, null = ผู้ใช้เลือกเอง
     * รหัสชุดเก่า = รหัสของรายการเดียวกันในชุด seed เดิม (ถ้าเครื่องนั้นยังใช้อยู่ จะตั้งค่าให้แถวเดิม ไม่สร้างซ้ำ)
     */
    private $items = [
        'P1'  => [1, 'PER_01', 'พกส.', [3], 'PER_01_01'],
        'P2'  => [2, 'PER_01', 'ลูกจ้างชั่วคราว', [4], 'PER_01_02'],
        'P3'  => [3, 'PER_01', 'ลูกจ้างรายคาบ', [5], 'PER_01_03'],
        'P4'  => [4, 'PER_01', 'เงินสมทบประกันสังคมส่วนของนายจ้าง', null, 'PER_01_04'],
        'P5'  => [5, 'PER_01', 'เงินสมทบกองทุนเลี้ยงชีพรายเดือน', null, 'PER_01_05'],
        'P6'  => [6, 'PER_02', 'ค่าตอบแทนนอกเวลาราชการ', null, 'PER_02_01'],
        'P7'  => [7, 'PER_03', 'ค่าเวรบ่าย-ดึก (พยาบาล)', null, 'PER_02_02'],
        'P8'  => [8, 'PER_04', 'ค่าตอบแทนไม่ทำเวชปฏิบัติส่วนตัว', [1], 'PER_02_03'],
        'P9'  => [9, 'PER_05', 'ค่าตอบแทนการปฏิบัติงาน(ฉบับ11)', 'all', 'PER_02_04'],
        'P10' => [10, 'PER_07', 'ค่าตอบแทน พตส.(เงินนอกงบประมาณ)', [1, 2], 'PER_02_05'],
        'P11' => [11, 'PER_09', 'ค่าตอบแทนอื่น(พ.สาขาส่งเสริมพิเศษ) ตกเบิกค่าเสี่ยงภัย', null, 'PER_02_06'],
    ];

    /** ฐานการจ่ายของประเภทบุคลากร: id => [pay_basis, work_days_per_month] */
    private $payBasis = [
        1 => ['monthly', null],
        2 => ['monthly', null],
        3 => ['monthly', null],
        4 => ['monthly', null],
        5 => ['daily', 22],
        6 => ['monthly', null],
    ];

    public function safeUp()
    {
        if ($this->db->getTableSchema('{{%categorise}}', true) === null) {
            echo "  ! ไม่พบตาราง categorise ข้าม migration นี้\n";
            return;
        }

        $this->ensurePlanType();
        $this->ensureCategories();
        $this->ensureItems();
        $this->ensurePayBasis();
    }

    public function safeDown()
    {
        if ($this->db->getTableSchema('{{%categorise}}', true) === null) {
            return;
        }

        // ถอนเฉพาะการตั้งค่าที่ migration นี้เขียนลง data_json
        // (ไม่ลบหมวด/รายการทิ้ง เพราะอาจมีแผนอ้างถึงอยู่แล้ว)
        $codes = array_keys($this->items);
        foreach ($this->items as [, , , , $legacyCode]) {
            if ($legacyCode) {
                $codes[] = $legacyCode;
            }
        }
        foreach ($codes as $code) {
            $row = $this->findRow('plan_item', $code);
            if (!$row) {
                continue;
            }
            $json = $this->decode($row['data_json']);
            unset($json['employee_type_ids'], $json['all_employee_types']);
            $this->update('categorise', ['data_json' => $json ?: null], ['id' => $row['id']]);
        }

        if ($this->db->getTableSchema('{{%employee_type}}', true) !== null) {
            foreach (array_keys($this->payBasis) as $id) {
                $raw = (new Query())->select('data_json')->from('employee_type')->where(['id' => $id])->scalar();
                if ($raw === false || $raw === null) {
                    continue;
                }
                $json = $this->decode($raw);
                unset($json['pay_basis'], $json['work_days_per_month']);
                $this->update('employee_type', ['data_json' => $json ?: null], ['id' => $id]);
            }
        }
    }

    /** ประเภทรายจ่าย "บุคลากร" ต้องมีก่อน หมวดถึงจะโผล่ในหน้าตั้งค่า */
    private function ensurePlanType()
    {
        if ($this->findRow('plan_type', 'PER')) {
            return;
        }
        $this->insert('categorise', [
            'name' => 'plan_type',
            'code' => 'PER',
            'category_id' => null,
            'title' => 'รายจ่ายบุคลากร',
            'sort' => 1,
            'active' => 1,
        ]);
        echo "  + plan_type PER\n";
    }

    private function ensureCategories()
    {
        foreach ($this->categories as $code => [$sort, $title]) {
            if ($this->findRow('plan_category', $code)) {
                continue;
            }
            $this->insert('categorise', [
                'name' => 'plan_category',
                'code' => $code,
                'category_id' => 'PER',
                'title' => $title,
                'sort' => $sort,
                'active' => 1,
            ]);
            echo "  + plan_category $code\n";
        }
    }

    private function ensureItems()
    {
        foreach ($this->items as $code => [$sort, $categoryId, $title, $types, $legacyCode]) {
            // หาแถวเดิมให้เจอก่อนเสมอ: รหัสใหม่ -> รหัสชุดเก่า -> ชื่อรายการ (กันสร้างรายการซ้ำซ้อน)
            $row = $this->findRow('plan_item', $code)
                ?: ($legacyCode ? $this->findRow('plan_item', $legacyCode) : null)
                ?: $this->findRowByTitle($title);

            if (!$row) {
                $this->insert('categorise', [
                    'name' => 'plan_item',
                    'code' => $code,
                    'category_id' => $categoryId,
                    'title' => $title,
                    'sort' => $sort,
                    'active' => 1,
                ]);
                echo "  + plan_item $code ($title)\n";
                $row = $this->findRow('plan_item', $code);
            }

            if ($row && $types !== null) {
                $this->writeMapping($row, $types);
            }
        }
    }

    /** เขียน employee_type_ids / all_employee_types ลง data_json โดยไม่ทับคีย์เดิม และไม่เขียนซ้ำถ้าตั้งค่าไว้แล้ว */
    private function writeMapping(array $row, $types)
    {
        $json = $this->decode($row['data_json']);
        if (array_key_exists('employee_type_ids', $json) || array_key_exists('all_employee_types', $json)) {
            return; // ผู้ดูแลตั้งค่าเองไว้แล้ว ไม่ทับ
        }

        $json['all_employee_types'] = $types === 'all';
        $json['employee_type_ids'] = $types === 'all' ? [] : array_values(array_map('intval', (array) $types));

        // ส่งเป็น array ให้ Yii แปลงเป็น JSON เอง (ถ้าส่งสตริงจะถูก encode ซ้ำจนกลายเป็น json string)
        $this->update('categorise', ['data_json' => $json], ['id' => $row['id']]);
        echo "  ~ plan_item {$row['code']} -> " . Json::encode($json['all_employee_types'] ? 'ทุกประเภท' : $json['employee_type_ids']) . "\n";
    }

    private function ensurePayBasis()
    {
        if ($this->db->getTableSchema('{{%employee_type}}', true) === null) {
            return;
        }

        foreach ($this->payBasis as $id => [$basis, $workDays]) {
            $raw = (new Query())->select('data_json')->from('employee_type')->where(['id' => $id])->scalar();
            if ($raw === false) {
                continue; // ไม่มีประเภทนี้ในเครื่องนี้
            }
            $json = $this->decode($raw);
            if (array_key_exists('pay_basis', $json)) {
                continue;
            }
            $json['pay_basis'] = $basis;
            if ($workDays) {
                $json['work_days_per_month'] = $workDays;
            }
            $this->update('employee_type', ['data_json' => $json], ['id' => $id]);
            echo "  ~ employee_type $id -> $basis\n";
        }
    }

    private function findRow(string $name, string $code)
    {
        return (new Query())
            ->select(['id', 'code', 'data_json'])
            ->from('categorise')
            ->where(['name' => $name, 'code' => $code])
            ->one() ?: null;
    }

    /** เทียบด้วยชื่อรายการ (ไม่จำกัดหมวด เพราะชุดเก่ากับชุดใหม่จัดหมวดต่างกัน) */
    private function findRowByTitle(string $title)
    {
        return (new Query())
            ->select(['id', 'code', 'data_json'])
            ->from('categorise')
            ->where(['name' => 'plan_item', 'title' => $title])
            ->one() ?: null;
    }

    private function decode($raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        $decoded = json_decode((string) $raw, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true); // เผื่อค่าที่เคยถูก encode ซ้ำ
        }

        return is_array($decoded) ? $decoded : [];
    }
}
