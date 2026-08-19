<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * seed เกณฑ์กำหนดกรอบอัตรากำลัง สป.สธ. ปี 2565-2569 (edition MOPH-2565-2569)
 *
 * แหล่งอ้างอิง: เอกสาร "เกณฑ์การกำหนดกรอบอัตรากำลัง" กองบริหารทรัพยากรบุคคล สป.สธ.
 * seed ครบทุกระดับ A..F3 เพื่อให้โรงพยาบาลทุกขนาดใช้ได้โดยไม่ต้องป้อนเกณฑ์เอง
 *
 * eligible: 1 = มีกรอบได้, 0 = ไม่มีกรอบ, NULL = ยังไม่ได้ยืนยันจากเอกสาร
 * ค่า NULL ตั้งใจใส่ไว้ตรงช่องที่อ่านจากเอกสารต้นทางไม่ชัดพอ — ระบบต้องแสดงว่า
 * "ยังไม่ยืนยัน" แทนที่จะเดาเป็น 0 แล้วทำให้กรอบหายไปเงียบ ๆ
 *
 * ยังไม่ครอบคลุม: ช่วงจำนวนเตียงย่อยของสายสนับสนุน (A แบ่ง 4 ช่วง, S 5 ช่วง, M1 5 ช่วง ฯลฯ)
 * และเกณฑ์ของ รพ.สต./สสอ. — seed เพิ่มได้ภายหลังโดยไม่กระทบโครงสร้าง
 */
class m260819_090100_seed_workforce_standard_moph extends Migration
{
    private const EDITION = 'MOPH-2565-2569';

    private const ALL_LEVELS = ['A', 'S', 'M1', 'M2', 'F1', 'F2', 'F3'];

    /** ระดับโรงพยาบาลตามการแบ่งของ สป.สธ. */
    private array $levels = [
        ['A', 'A — โรงพยาบาลศูนย์ระดับสูง'],
        ['S', 'S — โรงพยาบาลทั่วไประดับสูง'],
        ['M1', 'M1 — โรงพยาบาลทั่วไปขนาดเล็ก'],
        ['M2', 'M2 — โรงพยาบาลชุมชนแม่ข่าย'],
        ['F1', 'F1 — โรงพยาบาลชุมชนขนาดใหญ่'],
        ['F2', 'F2 — โรงพยาบาลชุมชนขนาดกลาง'],
        ['F3', 'F3 — โรงพยาบาลชุมชนขนาดเล็ก'],
    ];

    /**
     * สายงานตามเกณฑ์
     *
     * levels  = ระดับที่มีกรอบได้ (นอกจากนี้ถือว่าไม่มีกรอบ)
     * unknown = ระดับที่เอกสารอ่านไม่ชัด ต้องยืนยันก่อนใช้
     */
    private function lines(): array
    {
        return [
            // ── สายวิชาชีพ: เกณฑ์ให้คำนวณ FTE ตามภาระงานเอง ──
            [1, 'professional', 'นายแพทย์', 'fte', null, self::ALL_LEVELS, [], 'คำนวณ FTE ตามภาระงาน (Production line) + Allowance, Service based และ Population based'],
            [2, 'professional', 'ทันตแพทย์', 'fte', null, self::ALL_LEVELS, [], 'คำนวณ FTE ตามภาระงาน (Production line) + Allowance, Service based และ Population based'],
            [3, 'professional', 'เภสัชกร', 'fte', null, self::ALL_LEVELS, [], 'คำนวณ FTE ตามภาระงาน (Production line) + Allowance, Service based และ Population based'],
            [4, 'professional', 'พยาบาลวิชาชีพ / พยาบาลเทคนิค', 'fte', ['ward_bed_size' => 30], self::ALL_LEVELS, [], 'จำนวนแปรตามหอผู้ป่วยใน หอละ 30 เตียง'],
            [5, 'professional', 'นักกายภาพบำบัด / เจ้าพนักงานเวชกรรมฟื้นฟู', 'fte', null, self::ALL_LEVELS, [], 'คำนวณ FTE ตามภาระงาน + Allowance'],
            [6, 'professional', 'นักเทคนิคการแพทย์ / นักวิทยาศาสตร์การแพทย์ / เจ้าพนักงานวิทยาศาสตร์การแพทย์', 'fte', null, self::ALL_LEVELS, [], 'คำนวณ FTE ตามภาระงาน + Allowance'],
            [7, 'professional', 'นักรังสีการแพทย์ / เจ้าพนักงานรังสีการแพทย์ / นักรังสีฟิสิกส์', 'service_based', null, self::ALL_LEVELS, [], 'คำนวณ FTE + Service based ตามเครื่องมือ (SIMULATOR, CT, Linac, PET ฯลฯ)'],

            // ── สายวิชาชีพเฉพาะทาง: มีกรอบได้เฉพาะบางขนาด ──
            [8, 'professional', 'นักกิจกรรมบำบัด / เจ้าพนักงานอาชีวบำบัด', 'fte', null, ['A', 'S', 'M1', 'M2'], [], null],
            [9, 'professional', 'นักกายอุปกรณ์', 'fte', null, ['A', 'S', 'M1'], [], null],
            [10, 'professional', 'ช่างกายอุปกรณ์', 'fte', null, ['A', 'S', 'M1', 'M2'], [], null],
            [11, 'professional', 'นักจิตวิทยา / นักจิตวิทยาคลินิก', 'fte', null, self::ALL_LEVELS, [], null],
            [12, 'professional', 'นักเทคโนโลยีหัวใจและทรวงอก', 'service_based', null, ['A', 'S', 'M1'], [], 'Service based ตามเครื่องมือ: ห้องผ่าตัดหัวใจ, ห้องสวนหัวใจ, Echo, EST'],
            [13, 'professional', 'นักเวชศาสตร์การสื่อความหมาย / เจ้าพนักงานวิทยาศาสตร์การแพทย์ (ความผิดปกติของการสื่อความหมาย)', 'fte', null, ['A', 'S'], [], null],
            [14, 'professional', 'นักสังคมสงเคราะห์', 'fte', null, ['A', 'S', 'M1', 'M2', 'F1'], [], null],
            [15, 'professional', 'แพทย์แผนไทย / เจ้าพนักงานสาธารณสุข (อายุรเวท)', 'fte', null, self::ALL_LEVELS, [], null],
            [16, 'professional', 'นักวิทยาศาสตร์การแพทย์ / เจ้าพนักงานวิทยาศาสตร์การแพทย์ (สาขาเซลล์วิทยา)', 'service_based', null, ['A', 'S', 'M1'], [], null],
            [16, 'professional', 'นักวิทยาศาสตร์การแพทย์ / เจ้าพนักงานวิทยาศาสตร์การแพทย์ (สาขาพยาธิวิทยา)', 'service_based', null, ['A', 'S'], [], 'Service based ตามเครื่องมือ Tissue Processor, Cryostat'],

            // ── สายที่เกณฑ์ให้สูตรตัวเลขตรง ๆ ──
            [17, 'professional', 'นักโภชนาการ / นักกำหนดอาหาร / โภชนากร', 'ratio', [
                'driver' => 'active_bed',
                'per' => 50,
                'floor' => [
                    ['max' => 59, 'min_qty' => 2, 'max_qty' => 2],
                    ['min' => 60, 'max' => 150, 'min_qty' => 2, 'max_qty' => 3],
                ],
            ], self::ALL_LEVELS, [], '1 คน : 50 เตียง (Active bed) และกำหนดขั้นต่ำ Active bed 60-150 = 2-3 คน, Active bed < 60 = 2 คน'],
            [18, 'professional', 'นักวิชาการสาธารณสุข (เวชสถิติ) / เจ้าพนักงานเวชสถิติ', 'fte', null, self::ALL_LEVELS, [], null],
            [19, 'professional', 'นักปฏิบัติการฉุกเฉินการแพทย์ / เจ้าพนักงานสาธารณสุข (เวชกิจฉุกเฉิน) / นักวิชาการสาธารณสุข (เวชกิจฉุกเฉิน)', 'fte', null, self::ALL_LEVELS, [], null],
            [20, 'support', 'นักวิชาการโสตทัศนศึกษา / เจ้าพนักงานโสตทัศนศึกษา', 'fte', null, ['A', 'S', 'M1', 'M2', 'F1'], [], null],
            [21, 'support', 'ช่างภาพการแพทย์', 'fte', null, ['A', 'S'], [], null],
            [22, 'professional', 'นักวิชาการสาธารณสุข / เจ้าพนักงานสาธารณสุข / นักสาธารณสุข', 'population_based', [
                'driver' => 'catchment_population',
                'per' => 1250,
                'scope' => 'cup',
            ], self::ALL_LEVELS, [], 'คิดตาม POP ที่ รพ. รับผิดชอบ 1:1,250 — คิดที่ระดับ CUP ต้องหักกรอบ รพ.สต. ออกก่อนเป็นกรอบของโรงพยาบาล'],
            [23, 'professional', 'นักวิชาการสาธารณสุข (ทันตสาธารณสุข) / เจ้าพนักงานทันตสาธารณสุข', 'population_based', [
                'driver' => 'catchment_population',
                'per' => 7500,
                'scope' => 'cup',
            ], self::ALL_LEVELS, [], 'คิดตาม POP ที่ รพ. รับผิดชอบ 1:7,500 — คิดที่ระดับ CUP'],
            [24, 'professional', 'เจ้าพนักงานเภสัชกรรม', 'fte', null, self::ALL_LEVELS, [], null],
            [25, 'support', 'ช่างทันตกรรม', 'fte', null, ['A', 'S', 'M1'], [], null],
            [26, 'professional', 'ผู้ช่วยพยาบาล', 'fte', null, self::ALL_LEVELS, [], null],
            [27, 'professional', 'นักทัศนมาตร', 'fte', null, ['A', 'S'], [], null],

            // ── สายสนับสนุน (Back Office) ──
            // เอกสารแบ่งย่อยตามช่วงจำนวนเตียงในแต่ละระดับ ยังไม่ได้ seed ระดับช่วง
            // ระดับ A/S/M1/M2 อ่านได้ชัดว่ามีกรอบได้ ส่วน F1-F3 ปล่อยเป็น NULL รอยืนยัน
            [28, 'support', 'นักจัดการงานทั่วไป', 'manual', null, self::ALL_LEVELS, [], null],
            [29, 'support', 'นิติกร', 'manual', null, ['A', 'S', 'M1', 'M2'], ['F1', 'F2', 'F3'], null],
            [30, 'support', 'เจ้าพนักงานธุรการ', 'manual', null, self::ALL_LEVELS, [], null],
            [31, 'support', 'นายช่างเทคนิค', 'manual', null, ['A', 'S', 'M1', 'M2'], ['F1', 'F2', 'F3'], null],
            [32, 'support', 'นายช่างศิลป์', 'manual', null, ['A', 'S', 'M1', 'M2'], ['F1', 'F2', 'F3'], null],
            [33, 'support', 'นักประชาสัมพันธ์', 'manual', null, ['A', 'S', 'M1', 'M2'], ['F1', 'F2', 'F3'], null],
            [34, 'support', 'เจ้าพนักงานเผยแพร่ประชาสัมพันธ์', 'manual', null, ['A', 'S', 'M1', 'M2'], ['F1', 'F2', 'F3'], null],
            [35, 'support', 'นักวิชาการพัสดุ', 'manual', null, ['A', 'S', 'M1', 'M2'], ['F1', 'F2', 'F3'], null],
            [36, 'support', 'เจ้าพนักงานพัสดุ', 'manual', null, ['A', 'S', 'M1', 'M2'], ['F1', 'F2', 'F3'], null],
            [37, 'support', 'วิศวกร / วิศวกรเครื่องกล / วิศวกรไฟฟ้า / วิศวกรโยธา / วิศวกร (ชีวการแพทย์)', 'manual', null, ['A', 'S', 'M1', 'M2'], ['F1', 'F2', 'F3'], null],
            [38, 'support', 'นักทรัพยากรบุคคล', 'manual', null, ['A', 'S', 'M1', 'M2'], ['F1', 'F2', 'F3'], null],
            [39, 'support', 'นักวิชาการเงินและบัญชี', 'manual', null, ['A', 'S', 'M1', 'M2'], ['F1', 'F2', 'F3'], null],
            [40, 'support', 'เจ้าพนักงานการเงินและบัญชี', 'manual', null, ['A', 'S', 'M1', 'M2'], ['F1', 'F2', 'F3'], null],
            [41, 'support', 'นักวิชาการคอมพิวเตอร์', 'manual', null, ['A', 'S', 'M1', 'M2'], ['F1', 'F2', 'F3'], null],
            [42, 'support', 'นักวิชาการสถิติ', 'manual', null, ['A', 'S', 'M1', 'M2'], ['F1', 'F2', 'F3'], null],
            [43, 'support', 'นักวิเคราะห์นโยบายและแผน', 'manual', null, ['A', 'S', 'M1', 'M2'], ['F1', 'F2', 'F3'], null],
            [44, 'support', 'บรรณารักษ์ / เจ้าพนักงานห้องสมุด', 'manual', null, ['A', 'S', 'M1', 'M2'], ['F1', 'F2', 'F3'], null],
            [45, 'support', 'นักเทคโนโลยีสารสนเทศ', 'manual', null, ['A', 'S', 'M1', 'M2'], ['F1', 'F2', 'F3'], null],

            // ── งานบริการพื้นฐาน: เกณฑ์ให้สูตรจากปริมาณงานจริงของโรงพยาบาล ──
            [101, 'service', 'พนักงานเกษตรพื้นฐาน / คนสวน', 'ratio', [
                'driver' => 'garden_rai',
                'per' => 3,
            ], self::ALL_LEVELS, [], 'พื้นที่สวน 3 ไร่ : 1 คน'],
            [102, 'service', 'พนักงานทำความสะอาด', 'ratio', [
                'terms' => [
                    ['driver' => 'ward_count', 'multiply' => 4],
                    ['driver' => 'office_area_sqm', 'per' => 800],
                ],
            ], self::ALL_LEVELS, [], 'หอผู้ป่วย 1 หอ : 4 คน และสำนักงาน 800 ตร.ม. : 1 คน'],
            [103, 'service', 'พนักงานรักษาความปลอดภัย', 'ratio', [
                'driver' => 'security_post',
                'multiply' => 1,
                'per_shift_hours' => 8,
                'shifts_per_day' => null,
            ], self::ALL_LEVELS, [], '1 จุดประจำ : 1 คน : 1 เวร 8 ชม. — จำนวนเวรต่อวันขึ้นกับการจัดของแต่ละโรงพยาบาล ต้องยืนยันก่อนคำนวณ'],
            [104, 'service', 'พนักงานซักฟอก / ช่างตัดเย็บผ้า', 'ratio', [
                'driver' => 'laundry_kg_per_day',
                'per' => 150,
            ], self::ALL_LEVELS, [], 'น้ำหนักผ้าสะอาด 150 กก. : 1 คน : 1 วัน'],
            [105, 'service', 'พนักงานขับรถยนต์', 'ratio', [
                'driver' => 'vehicle_count',
                'multiply' => 0.7,
            ], self::ALL_LEVELS, [], 'ร้อยละ 70 ของจำนวนรถที่ใช้งาน'],
        ];
    }

    public function safeUp()
    {
        $this->seedHospitalLevels();
        $this->seedEmployeeTypeFrameFlag();
        $this->seedLinesAndRules();
    }

    public function safeDown()
    {
        $lineIds = (new Query())
            ->select('id')
            ->from('{{%workforce_standard_line}}')
            ->where(['edition' => self::EDITION])
            ->column();

        if ($lineIds !== []) {
            $this->delete('{{%workforce_standard_rule}}', ['line_id' => $lineIds]);
            $this->delete('{{%workforce_standard_line}}', ['id' => $lineIds]);
        }

        $this->delete('{{%categorise}}', ['name' => 'hospital_level']);

        if ($this->db->getTableSchema('{{%employee_type}}', true)->getColumn('counts_in_frame') !== null) {
            $this->dropColumn('{{%employee_type}}', 'counts_in_frame');
        }
    }

    /** ระดับโรงพยาบาลเป็นชุดข้อมูลกลาง แก้เพิ่มได้จากหน้าตั้งค่า */
    private function seedHospitalLevels(): void
    {
        $sort = 1;
        foreach ($this->levels as [$code, $title]) {
            $exists = (new Query())
                ->from('{{%categorise}}')
                ->where(['name' => 'hospital_level', 'code' => $code])
                ->exists();

            if (!$exists) {
                $this->insert('{{%categorise}}', [
                    'name' => 'hospital_level',
                    'code' => $code,
                    'title' => $title,
                    'sort' => (string) $sort,
                    'active' => 1,
                ]);
            }
            $sort++;
        }
    }

    /**
     * เกณฑ์นับกรอบสายสนับสนุน Back Office เฉพาะ 5 ประเภทการจ้าง
     * (ข้าราชการ พนักงานราชการ พนักงานกระทรวงฯ ลูกจ้างประจำ ลูกจ้างชั่วคราวรายเดือน)
     * ที่เหลือ (รายวัน รายคาบ จ้างเหมา) ไปนับในกรอบ Outsource
     *
     * เก็บเป็นข้อมูล ไม่ hardcode id ในโค้ด เพราะโรงพยาบาลอื่นอาจมีประเภทการจ้างต่างไป
     */
    private function seedEmployeeTypeFrameFlag(): void
    {
        if ($this->db->getTableSchema('{{%employee_type}}', true) === null) {
            return;
        }

        if ($this->db->getTableSchema('{{%employee_type}}', true)->getColumn('counts_in_frame') === null) {
            $this->addColumn(
                '{{%employee_type}}',
                'counts_in_frame',
                $this->tinyInteger(1)->notNull()->defaultValue(1)
                    ->comment('นับรวมในกรอบสายสนับสนุน Back Office (0 = ไปนับในกรอบ Outsource)')
            );
        }

        // ลูกจ้างชั่วคราวรายวัน = ไม่อยู่ใน 5 ประเภทการจ้างที่เกณฑ์ให้นับ
        $this->update('{{%employee_type}}', ['counts_in_frame' => 1], ['id' => [1, 2, 3, 4, 6]]);
        $this->update('{{%employee_type}}', ['counts_in_frame' => 0], ['id' => [5]]);
    }

    private function seedLinesAndRules(): void
    {
        $sort = 0;

        foreach ($this->lines() as [$seq, $category, $title, $method, $formula, $eligibleLevels, $unknownLevels, $note]) {
            $sort += 10;

            $lineId = (new Query())
                ->select('id')
                ->from('{{%workforce_standard_line}}')
                ->where([
                    'edition' => self::EDITION,
                    'org_type' => 'HOSPITAL',
                    'seq' => $seq,
                    'title' => $title,
                ])
                ->scalar();

            if ($lineId === false || $lineId === null) {
                $this->insert('{{%workforce_standard_line}}', [
                    'edition' => self::EDITION,
                    'org_type' => 'HOSPITAL',
                    'seq' => $seq,
                    'category' => $category,
                    'title' => $title,
                    'method' => $method,
                    'formula_json' => $formula === null ? null : $formula,
                    'note' => $note,
                    'sort' => $sort,
                    'active' => 1,
                ]);
                $lineId = $this->db->getLastInsertID();
            }

            foreach (self::ALL_LEVELS as $level) {
                if (in_array($level, $unknownLevels, true)) {
                    $eligible = null;
                } else {
                    $eligible = in_array($level, $eligibleLevels, true) ? 1 : 0;
                }

                $exists = (new Query())
                    ->from('{{%workforce_standard_rule}}')
                    ->where(['line_id' => $lineId, 'level_code' => $level, 'size_band' => null])
                    ->exists();

                if ($exists) {
                    continue;
                }

                $this->insert('{{%workforce_standard_rule}}', [
                    'line_id' => $lineId,
                    'level_code' => $level,
                    'size_band' => null,
                    'eligible' => $eligible,
                    'note' => $eligible === null ? 'อ่านจากเอกสารต้นทางไม่ชัด ต้องยืนยันก่อนใช้' : null,
                ]);
            }
        }
    }
}
