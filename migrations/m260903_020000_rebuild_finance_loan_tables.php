<?php

use yii\db\Migration;

/**
 * โครงสร้างใหม่ของระบบเงินยืม — ยุบตารางแบนตัวเดิม แล้วแยกเป็นหัวสัญญากับลูกสามชุด
 *
 * ทำไมต้องรื้อ ไม่ใช่ต่อเติม
 *
 * ตาราง finance_loan เดิมเก็บทุกอย่างไว้ในแถวเดียว ซึ่งพอสำหรับ "ทะเบียนคุม"
 * แต่ไม่พอสำหรับงานจริงสามเรื่องที่เกิดได้หลายครั้งต่อสัญญาหนึ่งใบ
 *   1) การส่งใช้ — หน้า 2 ของแบบ 8500 มีช่องครั้งที่ 1, 2, 3 พร้อมยอดคงค้างที่ลดลงทีละครั้ง
 *   2) บรรทัดประมาณการ — ใบประมาณการต้องการ "จำนวน × อัตรา" ไม่ใช่ยอดรวมสี่ก้อน
 *      (เบี้ยเลี้ยง 1 คน × 1 วัน × 80 บาท / พาหนะส่วนตัว 538 กม. × 4 บาท)
 *   3) การติดตาม — หนังสือทวงมีเลข "ครั้งที่ 1" "ครั้งที่ 2" จึงเป็นประวัติ ไม่ใช่ธงบูลีน
 *
 * ตอนรื้อไม่มีข้อมูลจริงสักแถว ระบบเงินยืมยังไม่เคยเปิดใช้ ข้อมูลปี 2569 ทั้งปี
 * ยังอยู่ใน Google Sheet และจะนำเข้าทีหลังตามเฟส 5
 *
 * migration ที่สร้างตารางแบนตัวเดิมถูกลบทิ้งไปแล้วเพราะไม่เคย commit และไม่เคย deploy
 * เครื่องใหม่จึงไม่มีตารางนี้ให้ยุบ ส่วนเครื่องที่เคยรันของเดิมไว้ยังมีอยู่ — getTableSchema()
 * จึงเป็นตัวแยกสองกรณีนี้ออกจากกัน
 */
class m260903_020000_rebuild_finance_loan_tables extends Migration
{
    public function safeUp()
    {
        $audit = fn() => [
            'ref' => $this->string(64)->notNull(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ];

        $this->dropLegacyTable();

        // ── ข้อมูลตั้งค่า ─────────────────────────────────────────────
        // ทั้งสามชุดต้องเพิ่มได้จากหน้าตั้งค่า ไม่ผูกเป็นค่าตายตัวในโค้ด
        // เพราะแต่ละโรงพยาบาลมีบัญชีและรายการค่าใช้จ่ายไม่เหมือนกัน

        $this->createTable('{{%finance_loan_expense_type}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(40)->notNull()->comment('รหัสอ้างอิงถาวร ใช้ในโค้ดและตอนนำเข้า'),
            'name' => $this->string(255)->notNull()->comment('ชื่อที่แสดงในฟอร์ม'),
            'due_days' => $this->integer()->notNull()->defaultValue(30)->comment('จำนวนวันที่ต้องส่งใช้'),
            'due_basis' => $this->string(20)->notNull()->defaultValue('activity_end')
                ->comment('นับจากอะไร: activity_end วันที่งานเสร็จ | received วันรับเงิน | borrowed วันยืม'),
            'estimate_form' => $this->string(20)->notNull()->defaultValue('general')
                ->comment('แบบใบประมาณการ: travel ใช้แบบเดินทางไปราชการ | general กรอกรายการเอง'),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
        ]);
        $this->createIndex('uq-loan_expense_type-code', '{{%finance_loan_expense_type}}', 'code', true);

        $this->createTable('{{%finance_loan_item_kind}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(40)->notNull(),
            'name' => $this->string(255)->notNull(),
            // ทะเบียนคุมมียอดแค่สี่ช่อง (เบี้ยเลี้ยง/ที่พัก/พาหนะ/อื่นๆ) รายการที่เพิ่มใหม่
            // จึงต้องบอกว่าไปรวมอยู่ช่องไหน ไม่งั้นพิมพ์ทะเบียนคุมไม่ได้
            'register_column' => $this->string(20)->notNull()->defaultValue('other')
                ->comment('ช่องในทะเบียนคุม: allowance | accommodation | transport | other'),
            'has_persons' => $this->boolean()->notNull()->defaultValue(false)->comment('มีช่องจำนวนคน/ห้อง'),
            'has_units' => $this->boolean()->notNull()->defaultValue(false)->comment('มีช่องจำนวนหน่วย'),
            'unit_name' => $this->string(30)->null()->comment('ชื่อหน่วย เช่น วัน คืน มื้อ กิโลเมตร'),
            'person_unit_name' => $this->string(30)->null()->comment('ชื่อหน่วยของช่องแรก เช่น คน ห้อง'),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
        ]);
        $this->createIndex('uq-loan_item_kind-code', '{{%finance_loan_item_kind}}', 'code', true);

        $this->createTable('{{%finance_loan_account}}', [
            'id' => $this->primaryKey(),
            'account_no' => $this->string(50)->notNull(),
            'name' => $this->string(255)->notNull()->comment('ชื่อบัญชีที่ปรากฏในสัญญา'),
            'bank_name' => $this->string(100)->null(),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
        ]);
        $this->createIndex('uq-loan_account-no', '{{%finance_loan_account}}', 'account_no', true);

        // ── หัวสัญญา ─────────────────────────────────────────────────

        $this->createTable('{{%finance_loan}}', array_merge([
            'id' => $this->primaryKey(),
            'contract_no' => $this->string(50)->notNull()->comment('เลขที่สัญญา เช่น BOR69-0058'),
            'contract_seq' => $this->integer()->null()->comment('เลขลำดับที่แกะจาก contract_no ใช้หาเลขถัดไปของปี'),
            'fiscal_year' => $this->integer()->notNull(),
            'status' => $this->string(30)->notNull()->defaultValue('requested')
                ->comment('requested | reviewed | approved | received | cleared | completed | cancelled'),

            'expense_type_id' => $this->integer()->null(),
            'account_id' => $this->integer()->null()->comment('ยืมจากบัญชี'),

            'borrower_emp_id' => $this->integer()->null()->comment('ผูกทะเบียนบุคลากร เพื่อให้แจ้งเตือน Telegram ได้'),
            'borrower_name' => $this->string(255)->notNull()->comment('เก็บซ้ำไว้ เผื่อผู้ยืมลาออกหรือเป็นคนนอกทะเบียน'),
            'borrower_position' => $this->string(255)->null(),
            'purpose' => $this->text()->notNull()->comment('รายการ/โครงการที่นำไปใช้'),

            // เลขที่บันทึกขออนุมัติ ใช้ขึ้นต้นหนังสือติดตาม
            // "ตามบันทึกข้อความที่ ลย 0033.301/1557 ลงวันที่ 21 พ.ค.2569"
            'request_document_no' => $this->string(100)->null(),
            'request_document_date' => $this->date()->null(),

            'borrowed_at' => $this->date()->notNull(),
            'received_at' => $this->date()->null()->comment('วันที่รับเช็ค'),

            // จุดตั้งต้นนับวันครบกำหนด — ไปราชการนับจากวันกลับ โครงการนับจากวันที่งานเสร็จ
            // ตอนสร้างใบยืมมักยังไม่รู้ จึงปล่อยว่างได้ แล้วให้ตัวกรอง "ไม่ระบุวันครบกำหนด" คอยเตือน
            'activity_start_at' => $this->date()->null(),
            'activity_end_at' => $this->date()->null()->comment('วันกลับ หรือวันสิ้นสุดโครงการ'),

            'due_at' => $this->date()->null()->comment('คำนวณจากกติกา แก้ทับด้วยมือได้'),
            // เก็บกติกาที่ใช้ตอนคำนวณไว้กับตัวสัญญา ถ้าวันหลังมีคนแก้ค่าตั้งค่าเป็น 20 วัน
            // สัญญาเก่าจะไม่ถูกคำนวณใหม่ย้อนหลังจนวันครบกำหนดขยับเอง
            'due_days' => $this->integer()->null(),
            'due_basis' => $this->string(20)->null(),
            'due_is_manual' => $this->boolean()->notNull()->defaultValue(false)->comment('ผู้ใช้แก้วันครบกำหนดเอง ห้ามคำนวณทับ'),

            'approved_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('รวมจาก finance_loan_item'),
            'voucher_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('รวมใบสำคัญจากทุกครั้งที่ส่งใช้'),
            'cash_return_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('รวมเงินสดคืนจากทุกครั้ง'),
            'outstanding_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('คงเหลือ = approved - voucher - cash'),

            'first_settled_at' => $this->date()->null(),
            'last_settled_at' => $this->date()->null(),
            'evidence_sent_at' => $this->date()->null()->comment('วันส่งเงินและหลักฐานให้หน่วยงานผู้เบิก'),
            'disbursement_document_no' => $this->string(100)->null()->comment('เลขที่ บร./บค. ล่าสุด'),

            'followup_count' => $this->integer()->notNull()->defaultValue(0),
            'last_followup_at' => $this->date()->null(),

            // ที่มาของใบยืม เปิดกว้างไว้เพราะวันข้างหน้าจะผูกโครงการในโมดูล pm ด้วย
            'source_ref_type' => $this->string(30)->notNull()->defaultValue('manual')
                ->comment('manual | development ทะเบียนเดินทาง | project โครงการ | import'),
            'source_ref_id' => $this->string(64)->null(),
            'source_event_key' => $this->string(128)->null()->comment('กันนำเข้าซ้ำจากไฟล์เดิม'),

            'import_batch' => $this->string(64)->null(),
            'import_row' => $this->integer()->null(),
            'note' => $this->text()->null(),
        ], $audit()));

        $this->createIndex('uq-finance_loan-ref', '{{%finance_loan}}', 'ref', true);
        $this->createIndex('uq-finance_loan-contract', '{{%finance_loan}}', 'contract_no', true);
        $this->createIndex('uq-finance_loan-event', '{{%finance_loan}}', 'source_event_key', true);
        // หาเลขที่สัญญาถัดไปของปีงบประมาณ — ไม่ใช้ตัวนับแยก จึงเริ่มใช้กลางปีได้
        $this->createIndex('idx-finance_loan-seq', '{{%finance_loan}}', ['fiscal_year', 'contract_seq']);
        $this->createIndex('idx-finance_loan-year-status', '{{%finance_loan}}', ['fiscal_year', 'status']);
        // ตัวไล่ลูกหนี้ค้างและตัวแจ้งเตือน
        $this->createIndex('idx-finance_loan-due', '{{%finance_loan}}', ['due_at', 'outstanding_amount']);
        $this->createIndex('idx-finance_loan-borrower', '{{%finance_loan}}', ['borrower_emp_id', 'status']);
        $this->createIndex('idx-finance_loan-source', '{{%finance_loan}}', ['source_ref_type', 'source_ref_id']);
        $this->createIndex('idx-finance_loan-import', '{{%finance_loan}}', 'import_batch');

        $this->addForeignKey('fk-finance_loan-type', '{{%finance_loan}}', 'expense_type_id', '{{%finance_loan_expense_type}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk-finance_loan-account', '{{%finance_loan}}', 'account_id', '{{%finance_loan_account}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk-finance_loan-borrower', '{{%finance_loan}}', 'borrower_emp_id', '{{%employees}}', 'id', 'RESTRICT', 'CASCADE');

        // ── บรรทัดประมาณการ ──────────────────────────────────────────

        $this->createTable('{{%finance_loan_item}}', array_merge([
            'id' => $this->primaryKey(),
            'loan_id' => $this->integer()->notNull(),
            'item_kind_id' => $this->integer()->null(),
            // ชื่อรายการเฉพาะกิจ เขียนทับชื่อจากตั้งค่าได้ เช่น "ค่าอาหารกลางวัน มื้อละ 80 บาท"
            'label' => $this->string(255)->null(),
            'persons' => $this->decimal(12, 2)->null()->comment('จำนวนคน หรือจำนวนห้อง'),
            'units' => $this->decimal(12, 2)->null()->comment('จำนวนวัน คืน มื้อ หรือกิโลเมตร'),
            'rate' => $this->decimal(15, 2)->null()->comment('อัตราต่อหน่วย'),
            'amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('ยอดของบรรทัดนี้'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'note' => $this->string(255)->null(),
        ], $audit()));

        $this->createIndex('uq-finance_loan_item-ref', '{{%finance_loan_item}}', 'ref', true);
        $this->createIndex('idx-finance_loan_item-loan', '{{%finance_loan_item}}', ['loan_id', 'sort_order']);
        $this->addForeignKey('fk-finance_loan_item-loan', '{{%finance_loan_item}}', 'loan_id', '{{%finance_loan}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-finance_loan_item-kind', '{{%finance_loan_item}}', 'item_kind_id', '{{%finance_loan_item_kind}}', 'id', 'RESTRICT', 'CASCADE');

        // ── การส่งใช้ ────────────────────────────────────────────────

        $this->createTable('{{%finance_loan_settlement}}', array_merge([
            'id' => $this->primaryKey(),
            'loan_id' => $this->integer()->notNull(),
            'seq' => $this->integer()->notNull()->comment('ครั้งที่ ตรงกับช่องในหน้า 2 ของแบบ 8500'),
            'settled_at' => $this->date()->notNull(),
            'voucher_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('ใบสำคัญ'),
            'cash_amount' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('เงินสด'),
            'balance_after' => $this->decimal(15, 2)->notNull()->defaultValue(0)->comment('คงค้างหลังส่งใช้ครั้งนี้'),
            'receipt_no' => $this->string(100)->null()->comment('เลขที่ บร. สำหรับเงินสด / บค. สำหรับใบสำคัญ'),
            'document_no' => $this->string(100)->null()->comment('เลขที่บันทึกนำส่งหลักฐาน'),
            'receipt_book_no' => $this->string(50)->null()->comment('เล่มที่ใบเสร็จ ใช้ในบันทึกนำส่งหลักฐาน'),
            'receipt_number' => $this->string(50)->null()->comment('เลขที่ใบเสร็จ'),
            'evidence_sent_at' => $this->date()->null(),
            'late_reason' => $this->string(255)->null()->comment('เหตุผลกรณีส่งใช้ล่าช้า หรือใบสำคัญต่ำกว่า 70%'),
            'note' => $this->string(255)->null(),
        ], $audit()));

        $this->createIndex('uq-finance_loan_settle-ref', '{{%finance_loan_settlement}}', 'ref', true);
        $this->createIndex('uq-finance_loan_settle-seq', '{{%finance_loan_settlement}}', ['loan_id', 'seq'], true);
        $this->createIndex('idx-finance_loan_settle-date', '{{%finance_loan_settlement}}', 'settled_at');
        $this->addForeignKey('fk-finance_loan_settle-loan', '{{%finance_loan_settlement}}', 'loan_id', '{{%finance_loan}}', 'id', 'CASCADE', 'CASCADE');

        // ── การติดตาม ────────────────────────────────────────────────

        $this->createTable('{{%finance_loan_followup}}', array_merge([
            'id' => $this->primaryKey(),
            'loan_id' => $this->integer()->notNull(),
            'channel' => $this->string(20)->notNull()->comment('letter หนังสือราชการ | telegram แจ้งเตือนอัตโนมัติ'),
            'stage' => $this->string(20)->null()
                ->comment('before_due | due | overdue_7 | overdue_15 | overdue_30 | weekly | manual'),
            // เลข "ครั้งที่ N" บนหัวหนังสือ นับเฉพาะหนังสือ ไม่นับข้อความแจ้งเตือน
            'letter_seq' => $this->integer()->null(),
            'letter_no' => $this->string(100)->null(),
            'letter_date' => $this->date()->null(),
            'new_due_at' => $this->date()->null()->comment('วันที่กำหนดใหม่ในหนังสือ'),
            'notified_at' => $this->dateTime()->null(),
            'days_overdue' => $this->integer()->null()->comment('เกินกำหนดกี่วัน ณ เวลาที่แจ้ง'),
            'recipient' => $this->string(255)->null()->comment('สรุปผู้รับ ไว้ตรวจย้อนหลัง'),
            // กันส่งซ้ำเมื่อ cron รันหลายรอบในวันเดียว — เขียนแถวก่อนแล้วค่อยส่ง
            // ค่า NULL ซ้ำกันได้ใน unique index ของ MySQL หนังสือจึงออกกี่ฉบับก็ได้
            'dedupe_key' => $this->string(64)->null(),
            'note' => $this->text()->null(),
        ], $audit()));

        $this->createIndex('uq-finance_loan_follow-ref', '{{%finance_loan_followup}}', 'ref', true);
        $this->createIndex('uq-finance_loan_follow-dedupe', '{{%finance_loan_followup}}', 'dedupe_key', true);
        $this->createIndex('idx-finance_loan_follow-loan', '{{%finance_loan_followup}}', ['loan_id', 'notified_at']);
        $this->addForeignKey('fk-finance_loan_follow-loan', '{{%finance_loan_followup}}', 'loan_id', '{{%finance_loan}}', 'id', 'CASCADE', 'CASCADE');

        $this->seedSettings();
    }

    public function safeDown()
    {
        $this->dropTable('{{%finance_loan_followup}}');
        $this->dropTable('{{%finance_loan_settlement}}');
        $this->dropTable('{{%finance_loan_item}}');
        $this->dropTable('{{%finance_loan}}');
        $this->dropTable('{{%finance_loan_account}}');
        $this->dropTable('{{%finance_loan_item_kind}}');
        $this->dropTable('{{%finance_loan_expense_type}}');
    }

    /** ยุบตารางแบนตัวเดิม เฉพาะเครื่องที่เคยรัน m260902_090000 ไปแล้ว */
    private function dropLegacyTable(): void
    {
        if ($this->db->getTableSchema('{{%finance_loan}}', true) === null) {
            echo "    > ไม่มีตาราง finance_loan เดิม ข้ามขั้นตอนยุบตาราง\n";
            return;
        }
        $rows = (int) $this->db->createCommand('SELECT COUNT(*) FROM {{%finance_loan}}')->queryScalar();
        if ($rows > 0) {
            throw new \RuntimeException(
                "ตาราง finance_loan เดิมมีข้อมูล {$rows} แถว migration นี้ออกแบบมาสำหรับตารางว่างเท่านั้น "
                . 'กรุณาสำรองข้อมูลและวางแผนย้ายข้อมูลก่อน'
            );
        }
        $this->dropTable('{{%finance_loan}}');
    }

    /**
     * ค่าตั้งต้นจากของจริงที่ รพร.ด่านซ้าย ใช้อยู่
     *
     * ทั้งหมดแก้และเพิ่มได้จากหน้าตั้งค่า ที่ seed ไว้คือชุดเริ่มต้นให้ใช้งานได้ทันที
     * ไม่ใช่รายการปิดตาย โรงพยาบาลอื่นลบทิ้งแล้วใส่ของตัวเองได้
     */
    private function seedSettings(): void
    {
        // ไปราชการและฝึกอบรมคืนภายใน 15 วันหลังกลับ ที่เหลือ 30 วันหลังดำเนินการเสร็จ
        $this->batchInsert('{{%finance_loan_expense_type}}',
            ['code', 'name', 'due_days', 'due_basis', 'estimate_form', 'sort_order'], [
            ['travel', 'ค่าใช้จ่ายเดินทางไปราชการ', 15, 'activity_end', 'travel', 10],
            ['project_uc_pp', 'ค่าใช้จ่ายตามโครงการ (UC) (PP)', 30, 'activity_end', 'general', 20],
            ['project_nonbudget', 'ค่าใช้จ่ายตามโครงการ (เงินนอกงบประมาณ)', 30, 'activity_end', 'general', 30],
            ['project_pp_migrant', 'ค่าใช้จ่ายตามโครงการ (P&P) แรงงานต่างด้าว', 30, 'activity_end', 'general', 40],
            ['project_pp_status', 'ค่าใช้จ่ายตามโครงการ (P&P) บุคคลที่มีปัญหาสถานะและสิทธิ', 30, 'activity_end', 'general', 50],
            ['training', 'ค่าใช้จ่ายในการฝึกอบรม', 15, 'activity_end', 'travel', 60],
        ]);

        $this->batchInsert('{{%finance_loan_item_kind}}',
            ['code', 'name', 'register_column', 'has_persons', 'has_units', 'person_unit_name', 'unit_name', 'sort_order'], [
            ['allowance', 'ค่าเบี้ยเลี้ยง', 'allowance', true, true, 'คน', 'วัน', 10],
            ['accommodation', 'ค่าที่พัก', 'accommodation', true, true, 'ห้อง', 'คืน', 20],
            ['transport_public', 'ค่ารถยนต์โดยสาร/เครื่องบิน', 'transport', true, false, 'คน', null, 30],
            ['transport_hire', 'ค่ารถยนต์โดยสารไม่ประจำทาง', 'transport', true, false, 'คน', null, 40],
            ['transport_rail', 'ค่ารถไฟ', 'transport', true, false, 'คน', null, 50],
            ['fuel', 'ค่าน้ำมันเชื้อเพลิงรถยนต์ราชการ', 'transport', false, false, null, null, 60],
            ['mileage', 'ค่าชดเชยพาหนะส่วนตัว', 'transport', false, true, null, 'กิโลเมตร', 70],
            ['registration', 'ค่าลงทะเบียน', 'other', true, false, 'คน', null, 80],
            ['meal', 'ค่าอาหาร', 'other', true, true, 'คน', 'มื้อ', 90],
            ['refreshment', 'ค่าอาหารว่างและเครื่องดื่ม', 'other', true, true, 'คน', 'มื้อ', 100],
            ['speaker', 'ค่าตอบแทนวิทยากร', 'other', true, true, 'คน', 'ชั่วโมง', 110],
            ['other', 'ค่าใช้จ่ายอื่น ๆ', 'other', false, false, null, null, 120],
        ]);

        $this->batchInsert('{{%finance_loan_account}}', ['account_no', 'name', 'sort_order'], [
            ['433-100-7049', 'โรงพยาบาลสมเด็จพระยุพราชด่านซ้าย', 10],
            ['012-332-0004-44', 'เงินบำรุงจากงานประกันสุขภาพ รพร.ด่านซ้าย', 20],
            ['020-056-0455-50', 'โรงพยาบาลสมเด็จพระยุพราชด่านซ้าย', 30],
            ['433-020-0457', 'อุดหนุนผู้มีปัญหาสถานะและสิทธิ', 40],
            ['433-030-7979', 'แพทย์แผนไทย', 50],
            ['758-226-3346', 'เงินบริจาคโรงพยาบาลสมเด็จพระยุพราชด่านซ้าย', 60],
        ]);
    }
}
