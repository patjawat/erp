<?php

use yii\db\Migration;

class m260824_100000_create_exit_interview_tables extends Migration
{
    public function safeUp()
    {
        $audit = function () {
            return [
                'ref' => $this->string(64)->notNull(),
                'created_at' => $this->dateTime()->null(),
                'updated_at' => $this->dateTime()->null(),
                'created_by' => $this->integer()->null(),
                'updated_by' => $this->integer()->null(),
            ];
        };

        $this->createTable('{{%exit_interview_template}}', array_merge([
            'id' => $this->primaryKey(),
            'code' => $this->string(64)->notNull(),
            'title' => $this->string(255)->notNull(),
            'description' => $this->text()->null(),
            'status' => $this->string(20)->notNull()->defaultValue('active'),
        ], $audit()));
        $this->createIndex('uq-exit-template-code', '{{%exit_interview_template}}', 'code', true);

        $this->createTable('{{%exit_interview_template_version}}', array_merge([
            'id' => $this->primaryKey(),
            'template_id' => $this->integer()->notNull(),
            'version_no' => $this->integer()->notNull()->defaultValue(1),
            'status' => $this->string(20)->notNull()->defaultValue('draft'),
            'intro_text' => $this->text()->null(),
            'published_at' => $this->dateTime()->null(),
            'published_by' => $this->integer()->null(),
        ], $audit()));
        $this->createIndex('uq-exit-template-version', '{{%exit_interview_template_version}}', ['template_id', 'version_no'], true);
        $this->createIndex('idx-exit-template-version-status', '{{%exit_interview_template_version}}', ['status', 'published_at']);
        $this->addForeignKey('fk-exit-version-template', '{{%exit_interview_template_version}}', 'template_id', '{{%exit_interview_template}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%exit_interview_section}}', array_merge([
            'id' => $this->primaryKey(),
            'version_id' => $this->integer()->notNull(),
            'code' => $this->string(64)->notNull(),
            'title' => $this->string(255)->notNull(),
            'description' => $this->text()->null(),
            'sequence' => $this->integer()->notNull()->defaultValue(1),
            'condition_json' => $this->text()->null(),
        ], $audit()));
        $this->createIndex('uq-exit-section-code', '{{%exit_interview_section}}', ['version_id', 'code'], true);
        $this->createIndex('idx-exit-section-sequence', '{{%exit_interview_section}}', ['version_id', 'sequence']);
        $this->addForeignKey('fk-exit-section-version', '{{%exit_interview_section}}', 'version_id', '{{%exit_interview_template_version}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%exit_interview_question}}', array_merge([
            'id' => $this->primaryKey(),
            'section_id' => $this->integer()->notNull(),
            'code' => $this->string(80)->notNull(),
            'prompt' => $this->text()->notNull(),
            'question_type' => $this->string(30)->notNull(),
            'is_required' => $this->boolean()->notNull()->defaultValue(false),
            'sequence' => $this->integer()->notNull()->defaultValue(1),
            'analytics_key' => $this->string(80)->null(),
            'config_json' => $this->text()->null(),
            'condition_json' => $this->text()->null(),
            'is_hr_only' => $this->boolean()->notNull()->defaultValue(false),
        ], $audit()));
        $this->createIndex('uq-exit-question-code', '{{%exit_interview_question}}', ['section_id', 'code'], true);
        $this->createIndex('idx-exit-question-sequence', '{{%exit_interview_question}}', ['section_id', 'sequence']);
        $this->createIndex('idx-exit-question-analytics', '{{%exit_interview_question}}', 'analytics_key');
        $this->addForeignKey('fk-exit-question-section', '{{%exit_interview_question}}', 'section_id', '{{%exit_interview_section}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%exit_interview_question_option}}', array_merge([
            'id' => $this->primaryKey(),
            'question_id' => $this->integer()->notNull(),
            'value' => $this->string(100)->notNull(),
            'label' => $this->string(255)->notNull(),
            'score' => $this->decimal(8, 2)->null(),
            'sequence' => $this->integer()->notNull()->defaultValue(1),
            'is_other' => $this->boolean()->notNull()->defaultValue(false),
        ], $audit()));
        $this->createIndex('uq-exit-option-value', '{{%exit_interview_question_option}}', ['question_id', 'value'], true);
        $this->addForeignKey('fk-exit-option-question', '{{%exit_interview_question_option}}', 'question_id', '{{%exit_interview_question}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%exit_interview}}', array_merge([
            'id' => $this->primaryKey(),
            'emp_id' => $this->integer()->notNull(),
            'version_id' => $this->integer()->notNull(),
            'status' => $this->string(30)->notNull()->defaultValue('pending'),
            'response_source' => $this->string(30)->notNull()->defaultValue('hr_interview'),
            'exit_type' => $this->string(30)->notNull()->defaultValue('resignation'),
            'exit_date' => $this->date()->null(),
            'interview_date' => $this->date()->null(),
            'interviewer_id' => $this->integer()->null(),
            'employee_name_snapshot' => $this->string(255)->notNull(),
            'department_id_snapshot' => $this->integer()->null(),
            'department_name_snapshot' => $this->string(255)->null(),
            'position_name_snapshot' => $this->string(255)->null(),
            'employee_type_snapshot' => $this->string(255)->null(),
            'join_date_snapshot' => $this->date()->null(),
            'submitted_at' => $this->dateTime()->null(),
            'consent_at' => $this->dateTime()->null(),
        ], $audit()));
        $this->createIndex('idx-exit-interview-status-date', '{{%exit_interview}}', ['status', 'exit_date']);
        $this->createIndex('idx-exit-interview-department', '{{%exit_interview}}', ['department_id_snapshot', 'exit_date']);
        $this->createIndex('idx-exit-interview-employee', '{{%exit_interview}}', ['emp_id', 'exit_date']);
        $this->addForeignKey('fk-exit-interview-employee', '{{%exit_interview}}', 'emp_id', '{{%employees}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk-exit-interview-version', '{{%exit_interview}}', 'version_id', '{{%exit_interview_template_version}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%exit_interview_answer}}', array_merge([
            'id' => $this->primaryKey(),
            'interview_id' => $this->integer()->notNull(),
            'question_id' => $this->integer()->notNull(),
            'question_snapshot' => $this->text()->notNull(),
            'value_text' => $this->text()->null(),
            'value_number' => $this->decimal(12, 4)->null(),
            'value_json' => $this->text()->null(),
        ], $audit()));
        $this->createIndex('uq-exit-answer-question', '{{%exit_interview_answer}}', ['interview_id', 'question_id'], true);
        $this->addForeignKey('fk-exit-answer-interview', '{{%exit_interview_answer}}', 'interview_id', '{{%exit_interview}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-exit-answer-question', '{{%exit_interview_answer}}', 'question_id', '{{%exit_interview_question}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%exit_interview_link}}', array_merge([
            'id' => $this->primaryKey(),
            'interview_id' => $this->integer()->notNull(),
            'token_hash' => $this->char(64)->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('active'),
            'expires_at' => $this->dateTime()->notNull(),
            'first_opened_at' => $this->dateTime()->null(),
            'last_opened_at' => $this->dateTime()->null(),
            'submitted_at' => $this->dateTime()->null(),
        ], $audit()));
        $this->createIndex('uq-exit-link-token', '{{%exit_interview_link}}', 'token_hash', true);
        $this->createIndex('idx-exit-link-interview-status', '{{%exit_interview_link}}', ['interview_id', 'status']);
        $this->addForeignKey('fk-exit-link-interview', '{{%exit_interview_link}}', 'interview_id', '{{%exit_interview}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%exit_interview_audit_log}}', [
            'id' => $this->primaryKey(),
            'interview_id' => $this->integer()->notNull(),
            'action' => $this->string(50)->notNull(),
            'field_name' => $this->string(100)->null(),
            'old_value' => $this->text()->null(),
            'new_value' => $this->text()->null(),
            'reason' => $this->text()->null(),
            'created_at' => $this->dateTime()->notNull(),
            'created_by' => $this->integer()->null(),
        ]);
        $this->createIndex('idx-exit-audit-interview', '{{%exit_interview_audit_log}}', ['interview_id', 'created_at']);
        $this->addForeignKey('fk-exit-audit-interview', '{{%exit_interview_audit_log}}', 'interview_id', '{{%exit_interview}}', 'id', 'CASCADE', 'CASCADE');

        $this->seedTemplate();
        $this->seedPermissions();
    }

    private function seedPermissions(): void
    {
        $auth = Yii::$app->authManager;
        $labels = [
            'exitInterviewManage' => 'จัดการรายการ Exit Interview',
            'exitInterviewViewIdentified' => 'ดูคำตอบ Exit Interview แบบระบุตัวตน',
            'exitInterviewViewAnalytics' => 'ดูผลวิเคราะห์ Exit Interview',
            'exitInterviewManageTemplate' => 'จัดการแบบสอบถาม Exit Interview',
            'exitInterviewExportIdentified' => 'ส่งออก Exit Interview แบบระบุตัวตน',
            'exitInterviewImport' => 'นำเข้า Exit Interview',
        ];
        foreach ($labels as $name => $description) {
            if (!$auth->getPermission($name)) {
                $permission = $auth->createPermission($name);
                $permission->description = $description;
                $auth->add($permission);
            }
        }
        $admin = $auth->getRole('admin');
        if ($admin) {
            foreach (array_keys($labels) as $name) {
                $permission = $auth->getPermission($name);
                if ($permission && !$auth->hasChild($admin, $permission)) {
                    $auth->addChild($admin, $permission);
                }
            }
        }
    }

    private function seedTemplate(): void
    {
        $now = date('Y-m-d H:i:s');
        $ref = static fn() => substr(Yii::$app->getSecurity()->generateRandomString(), 10);
        $this->insert('{{%exit_interview_template}}', [
            'code' => 'HOSPITAL_EXIT', 'title' => 'แบบสอบถามความคิดเห็นกรณีลาออก',
            'description' => 'แบบสัมภาษณ์กลางของโรงพยาบาล รองรับเงื่อนไขตามประเภทการออก',
            'status' => 'active', 'ref' => $ref(), 'created_at' => $now, 'updated_at' => $now,
        ]);
        $templateId = (int) $this->db->getLastInsertID();
        $this->insert('{{%exit_interview_template_version}}', [
            'template_id' => $templateId, 'version_no' => 1, 'status' => 'published',
            'intro_text' => 'แบบสอบถามนี้เป็นความลับส่วนบุคคล ใช้เพื่อประเมินและปรับปรุงองค์กรในภาพรวม รายละเอียดรายบุคคลเปิดดูได้เฉพาะผู้ได้รับสิทธิ',
            'published_at' => $now, 'ref' => $ref(), 'created_at' => $now, 'updated_at' => $now,
        ]);
        $versionId = (int) $this->db->getLastInsertID();

        $sections = [
            ['profile', 'ข้อมูลบุคลากร', 'ข้อมูลส่วนนี้ดึงจากทะเบียนบุคลากรและเก็บเป็นข้อมูล ณ วันที่ออก', 1],
            ['reason', 'ปัจจัยที่ทำให้ตัดสินใจออก', 'เลือกได้สูงสุด 3 ข้อ และเรียงลำดับความสำคัญ', 2],
            ['rating', 'ประเมินประสบการณ์การทำงาน', 'โปรดให้คะแนนตามประสบการณ์จริงของท่าน', 3],
            ['reflection', 'ความคิดเห็นต่อหน่วยงาน', 'คำตอบปลายเปิดจะแสดงเฉพาะผู้มีสิทธิ', 4],
            ['suggestion', 'ข้อเสนอแนะ', null, 5],
        ];
        $sectionIds = [];
        foreach ($sections as [$code, $title, $description, $sequence]) {
            $this->insert('{{%exit_interview_section}}', [
                'version_id' => $versionId, 'code' => $code, 'title' => $title,
                'description' => $description, 'sequence' => $sequence,
                'ref' => $ref(), 'created_at' => $now, 'updated_at' => $now,
            ]);
            $sectionIds[$code] = (int) $this->db->getLastInsertID();
        }

        $questions = [
            ['reason', 'exit_reasons', 'ปัจจัยสำคัญที่ทำให้ท่านตัดสินใจออกจากงาน', 'ranking', 1, null, ['max_selections' => 3]],
            ['reason', 'exit_reason_other', 'หากเลือกอื่น ๆ โปรดระบุเหตุผล', 'short_text', 2, null, []],
            ['reason', 'consideration_period', 'ท่านคิดทบทวนอย่างจริงจังมาเป็นระยะเวลาเท่าใด', 'single_choice', 3, null, []],
            ['reflection', 'primary_reason', 'โปรดระบุสาเหตุเบื้องต้นที่ทำให้ท่านคิดออกจากงาน', 'long_text', 1, null, []],
            ['reflection', 'least_liked', 'สิ่งที่ท่านชอบน้อยที่สุดในหน่วยงาน', 'long_text', 2, null, []],
            ['reflection', 'most_liked', 'สิ่งที่ท่านชอบมากที่สุดในหน่วยงาน', 'long_text', 3, null, []],
            ['reflection', 'retention_factor', 'อะไรที่จะทำให้ท่านอยากทำงานกับองค์กรต่อ', 'long_text', 4, null, []],
            ['reflection', 'overall_comment', 'ความคิดเห็นโดยรวมเกี่ยวกับหน่วยงานก่อนตัดสินใจออก', 'long_text', 5, null, []],
            ['suggestion', 'improvement', 'หน่วยงานควรปรับปรุงสิ่งใดให้ดีกว่าปัจจุบัน', 'long_text', 1, null, []],
            ['suggestion', 'additional_comment', 'ความคิดเห็นหรือข้อเสนอแนะเพิ่มเติม', 'long_text', 2, null, []],
            ['rating', 'compensation', 'ค่าตอบแทนและสวัสดิการ', 'rating', 1, 'compensation', ['min' => 1, 'max' => 5]],
            ['rating', 'workload', 'ภาระงานและสมดุลชีวิต', 'rating', 2, 'workload', ['min' => 1, 'max' => 5]],
            ['rating', 'management', 'ความชัดเจนในการบริหารและมอบหมายงาน', 'rating', 3, 'management', ['min' => 1, 'max' => 5]],
            ['rating', 'supervisor', 'ความสัมพันธ์กับหัวหน้างาน', 'rating', 4, 'supervisor', ['min' => 1, 'max' => 5]],
            ['rating', 'colleagues', 'ความสัมพันธ์กับเพื่อนร่วมงาน', 'rating', 5, 'colleagues', ['min' => 1, 'max' => 5]],
            ['rating', 'communication', 'การสื่อสารภายในองค์กร', 'rating', 6, 'communication', ['min' => 1, 'max' => 5]],
            ['rating', 'career', 'โอกาสเติบโตในสายอาชีพ', 'rating', 7, 'career', ['min' => 1, 'max' => 5]],
            ['rating', 'development', 'โอกาสเรียนรู้และพัฒนา', 'rating', 8, 'development', ['min' => 1, 'max' => 5]],
            ['rating', 'mentoring', 'ระบบพี่เลี้ยงและการสอนงาน', 'rating', 9, 'mentoring', ['min' => 1, 'max' => 5]],
            ['rating', 'safety', 'สภาพแวดล้อมและความปลอดภัยในการทำงาน', 'rating', 10, 'safety', ['min' => 1, 'max' => 5]],
            ['rating', 'overall_satisfaction', 'ความพึงพอใจโดยรวม', 'rating', 11, 'overall_satisfaction', ['min' => 1, 'max' => 5]],
            ['rating', 'rehire', 'หากมีโอกาส ท่านจะกลับมาทำงานกับองค์กรอีกหรือไม่', 'single_choice', 12, 'rehire', []],
            ['rating', 'recommend', 'ท่านจะแนะนำองค์กรให้ผู้อื่นมาทำงานหรือไม่', 'rating', 13, 'recommend', ['min' => 1, 'max' => 5]],
        ];
        $questionIds = [];
        foreach ($questions as [$section, $code, $prompt, $type, $sequence, $analytics, $config]) {
            $this->insert('{{%exit_interview_question}}', [
                'section_id' => $sectionIds[$section], 'code' => $code, 'prompt' => $prompt,
                'question_type' => $type, 'is_required' => in_array($code, ['exit_reasons', 'overall_satisfaction'], true),
                'sequence' => $sequence, 'analytics_key' => $analytics,
                'config_json' => json_encode($config, JSON_UNESCAPED_UNICODE),
                'ref' => $ref(), 'created_at' => $now, 'updated_at' => $now,
            ]);
            $questionIds[$code] = (int) $this->db->getLastInsertID();
        }

        $reasons = [
            'job_fit' => 'ไม่ถนัดกับงานที่ได้รับมอบหมาย', 'skills' => 'ไม่เข้าใจหรือขาดทักษะในงาน',
            'career' => 'ไม่เห็นโอกาสความก้าวหน้าในอาชีพ', 'mentoring' => 'ไม่มีพี่เลี้ยงช่วยสอนหรือแนะนำงาน',
            'quality_of_life' => 'คุณภาพชีวิตการทำงานไม่ดี', 'job_variety' => 'งานจำเจหรือไม่มีโอกาสเรียนรู้งานใหม่',
            'safety' => 'งานเสี่ยงอันตราย', 'colleagues' => 'ความสัมพันธ์กับเพื่อนร่วมงาน',
            'supervisor' => 'ความสัมพันธ์กับหัวหน้างาน', 'management' => 'ไม่ได้รับความเป็นธรรมจากผู้บังคับบัญชา',
            'compensation' => 'เงินเดือนหรือค่าตอบแทนน้อยกว่าที่ต้องการ', 'benefits' => 'สวัสดิการหรือสิทธิประโยชน์น้อยกว่าที่ต้องการ',
            'commute' => 'ปัญหาการเดินทาง', 'family' => 'ปัญหาส่วนตัวหรือครอบครัว',
            'health' => 'ปัญหาสุขภาพ', 'education' => 'ศึกษาต่อ', 'business' => 'ประกอบธุรกิจหรืออาชีพส่วนตัว',
            'relocation' => 'กลับภูมิลำเนาเดิม', 'new_job' => 'ได้งานใหม่ที่ชอบมากกว่า', 'other' => 'อื่น ๆ',
        ];
        $sequence = 1;
        foreach ($reasons as $value => $label) {
            $this->insert('{{%exit_interview_question_option}}', [
                'question_id' => $questionIds['exit_reasons'], 'value' => $value, 'label' => $label,
                'sequence' => $sequence++, 'is_other' => $value === 'other',
                'ref' => $ref(), 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        foreach (['lt_1m' => 'น้อยกว่า 1 เดือน', '1_3m' => '1–3 เดือน', '3_12m' => '3–12 เดือน', 'gt_1y' => 'มากกว่า 1 ปี'] as $value => $label) {
            $this->insert('{{%exit_interview_question_option}}', [
                'question_id' => $questionIds['consideration_period'], 'value' => $value, 'label' => $label,
                'sequence' => $sequence++, 'ref' => $ref(), 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        foreach (['yes' => 'พิจารณากลับมาทำงาน', 'maybe' => 'อาจพิจารณา', 'no' => 'ไม่พิจารณา'] as $value => $label) {
            $this->insert('{{%exit_interview_question_option}}', [
                'question_id' => $questionIds['rehire'], 'value' => $value, 'label' => $label,
                'sequence' => $sequence++, 'ref' => $ref(), 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%exit_interview_audit_log}}');
        $this->dropTable('{{%exit_interview_link}}');
        $this->dropTable('{{%exit_interview_answer}}');
        $this->dropTable('{{%exit_interview}}');
        $this->dropTable('{{%exit_interview_question_option}}');
        $this->dropTable('{{%exit_interview_question}}');
        $this->dropTable('{{%exit_interview_section}}');
        $this->dropTable('{{%exit_interview_template_version}}');
        $this->dropTable('{{%exit_interview_template}}');
        $auth = Yii::$app->authManager;
        foreach (['exitInterviewManage', 'exitInterviewViewIdentified', 'exitInterviewViewAnalytics', 'exitInterviewManageTemplate', 'exitInterviewExportIdentified', 'exitInterviewImport'] as $name) {
            if ($permission = $auth->getPermission($name)) $auth->remove($permission);
        }
    }
}
