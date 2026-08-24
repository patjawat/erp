<?php

use yii\db\Migration;

/** Core schema and RBAC for annual Service Profiles. */
class m260822_180000_create_service_profile_core extends Migration
{
    private array $permissions = [
        'serviceProfileView' => 'ดู Service Profile ของหน่วยงาน',
        'serviceProfileAuthor' => 'ร่วมจัดทำ Service Profile ที่ได้รับมอบหมาย',
        'serviceProfileSubmit' => 'ส่ง Service Profile เข้ากระบวนการพิจารณา',
        'serviceProfileQualityReview' => 'ตรวจและเห็นชอบ Service Profile ในฐานะผู้แทนคุณภาพ',
        'serviceProfileDirectorApprove' => 'อนุมัติ Service Profile ในฐานะผู้อำนวยการ',
        'serviceProfileHeadAcknowledge' => 'รับทราบ Service Profile ในฐานะหัวหน้าหน่วยงาน',
        'serviceProfileTemplateManage' => 'จัดการ Template Service Profile',
        'serviceProfileAdmin' => 'ดูแลระบบ Service Profile',
    ];

    public function safeUp()
    {
        $this->createTable('{{%service_profile_template}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->null(),
            'owner_type' => $this->string(30)->notNull()->defaultValue('department'),
            'owner_id' => $this->integer()->notNull()->comment('tree.id สำหรับ department'),
            'owner_name_snapshot' => $this->string(255)->notNull(),
            'name' => $this->string(255)->notNull(),
            'revision_no' => $this->integer()->notNull()->defaultValue(1),
            'lifecycle_status' => $this->string(20)->notNull()->defaultValue('draft'),
            'effective_fiscal_year' => $this->integer()->notNull(),
            'parent_template_id' => $this->integer()->null(),
            'description' => $this->text()->null(),
            'is_active' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('ux-sp-template-owner-revision', '{{%service_profile_template}}', ['owner_type', 'owner_id', 'revision_no'], true);
        $this->createIndex('idx-sp-template-current', '{{%service_profile_template}}', ['owner_type', 'owner_id', 'is_active', 'effective_fiscal_year']);
        $this->addForeignKey('fk-sp-template-parent', '{{%service_profile_template}}', 'parent_template_id', '{{%service_profile_template}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%service_profile_template_section}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->null(),
            'template_id' => $this->integer()->notNull(),
            'section_code' => $this->string(80)->notNull(),
            'title' => $this->string(255)->notNull(),
            'description' => $this->text()->null(),
            'block_type' => $this->string(50)->notNull()->defaultValue('rich_text'),
            'config_json' => $this->json()->null(),
            'is_required' => $this->boolean()->notNull()->defaultValue(false),
            'is_enabled' => $this->boolean()->notNull()->defaultValue(true),
            'sort_order' => $this->integer()->notNull()->defaultValue(10),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('ux-sp-template-section-code', '{{%service_profile_template_section}}', ['template_id', 'section_code'], true);
        $this->addForeignKey('fk-sp-template-section-template', '{{%service_profile_template_section}}', 'template_id', '{{%service_profile_template}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%service_profile}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->null(),
            'owner_type' => $this->string(30)->notNull()->defaultValue('department'),
            'owner_id' => $this->integer()->notNull(),
            'owner_name_snapshot' => $this->string(255)->notNull(),
            'fiscal_year' => $this->integer()->notNull(),
            'revision_no' => $this->integer()->notNull()->defaultValue(1),
            'template_id' => $this->integer()->null(),
            'template_revision_snapshot' => $this->integer()->null(),
            'status' => $this->string(30)->notNull()->defaultValue('draft'),
            'supersedes_id' => $this->integer()->null(),
            'effective_from' => $this->date()->null(),
            'effective_to' => $this->date()->null(),
            'submitted_at' => $this->dateTime()->null(),
            'published_at' => $this->dateTime()->null(),
            'published_by' => $this->integer()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('ux-sp-owner-year-revision', '{{%service_profile}}', ['owner_type', 'owner_id', 'fiscal_year', 'revision_no'], true);
        $this->createIndex('idx-sp-owner-current', '{{%service_profile}}', ['owner_type', 'owner_id', 'status', 'effective_from']);
        $this->addForeignKey('fk-sp-template', '{{%service_profile}}', 'template_id', '{{%service_profile_template}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk-sp-supersedes', '{{%service_profile}}', 'supersedes_id', '{{%service_profile}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%service_profile_section}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255)->null(),
            'service_profile_id' => $this->integer()->notNull(),
            'template_section_id' => $this->integer()->null(),
            'section_code' => $this->string(80)->notNull(),
            'title' => $this->string(255)->notNull(),
            'block_type' => $this->string(50)->notNull(),
            'content' => $this->text()->null(),
            'data_json' => $this->json()->null(),
            'config_snapshot_json' => $this->json()->null(),
            'is_required' => $this->boolean()->notNull()->defaultValue(false),
            'sort_order' => $this->integer()->notNull()->defaultValue(10),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->createIndex('ux-sp-section-code', '{{%service_profile_section}}', ['service_profile_id', 'section_code'], true);
        $this->addForeignKey('fk-sp-section-profile', '{{%service_profile_section}}', 'service_profile_id', '{{%service_profile}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-sp-section-template-section', '{{%service_profile_section}}', 'template_section_id', '{{%service_profile_template_section}}', 'id', 'SET NULL', 'CASCADE');

        $this->createTable('{{%service_profile_author}}', [
            'id' => $this->primaryKey(),
            'service_profile_id' => $this->integer()->notNull(),
            'employee_id' => $this->integer()->notNull(),
            'role' => $this->string(30)->notNull()->defaultValue('author'),
            'assigned_at' => $this->dateTime()->null(),
            'assigned_by' => $this->integer()->null(),
        ]);
        $this->createIndex('ux-sp-author', '{{%service_profile_author}}', ['service_profile_id', 'employee_id'], true);
        $this->addForeignKey('fk-sp-author-profile', '{{%service_profile_author}}', 'service_profile_id', '{{%service_profile}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%service_profile_quality_reviewer}}', [
            'id' => $this->primaryKey(),
            'owner_type' => $this->string(30)->notNull()->defaultValue('department'),
            'owner_id' => $this->integer()->notNull(),
            'employee_id' => $this->integer()->notNull(),
            'is_lead' => $this->boolean()->notNull()->defaultValue(false),
            'active' => $this->boolean()->notNull()->defaultValue(true),
            'effective_from' => $this->date()->null(),
            'effective_to' => $this->date()->null(),
            'created_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
        ]);
        $this->createIndex('ux-sp-quality-reviewer', '{{%service_profile_quality_reviewer}}', ['owner_type', 'owner_id', 'employee_id'], true);

        $this->createTable('{{%service_profile_review}}', [
            'id' => $this->primaryKey(),
            'service_profile_id' => $this->integer()->notNull(),
            'reviewer_employee_id' => $this->integer()->notNull(),
            'decision' => $this->string(20)->notNull()->defaultValue('commented'),
            'comment' => $this->text()->null(),
            'decided_at' => $this->dateTime()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
        ]);
        $this->createIndex('ux-sp-review-reviewer', '{{%service_profile_review}}', ['service_profile_id', 'reviewer_employee_id'], true);
        $this->addForeignKey('fk-sp-review-profile', '{{%service_profile_review}}', 'service_profile_id', '{{%service_profile}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%service_profile_approval}}', [
            'id' => $this->primaryKey(),
            'service_profile_id' => $this->integer()->notNull(),
            'stage' => $this->string(30)->notNull(),
            'employee_id' => $this->integer()->notNull(),
            'employee_name_snapshot' => $this->string(255)->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('waiting'),
            'comment' => $this->text()->null(),
            'acted_at' => $this->dateTime()->null(),
            'acted_by_user_id' => $this->integer()->null(),
            'data_json' => $this->json()->null(),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
        ]);
        $this->createIndex('ux-sp-approval-stage', '{{%service_profile_approval}}', ['service_profile_id', 'stage'], true);
        $this->addForeignKey('fk-sp-approval-profile', '{{%service_profile_approval}}', 'service_profile_id', '{{%service_profile}}', 'id', 'CASCADE', 'CASCADE');

        $this->createTable('{{%service_profile_activity}}', [
            'id' => $this->primaryKey(),
            'service_profile_id' => $this->integer()->notNull(),
            'section_id' => $this->integer()->null(),
            'action' => $this->string(50)->notNull(),
            'from_status' => $this->string(30)->null(),
            'to_status' => $this->string(30)->null(),
            'message' => $this->text()->null(),
            'data_json' => $this->json()->null(),
            'created_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
        ]);
        $this->createIndex('idx-sp-activity-profile-created', '{{%service_profile_activity}}', ['service_profile_id', 'created_at']);
        $this->addForeignKey('fk-sp-activity-profile', '{{%service_profile_activity}}', 'service_profile_id', '{{%service_profile}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-sp-activity-section', '{{%service_profile_activity}}', 'section_id', '{{%service_profile_section}}', 'id', 'SET NULL', 'CASCADE');

        $auth = Yii::$app->authManager;
        foreach ($this->permissions as $name => $description) {
            if ($auth->getPermission($name) === null) {
                $permission = $auth->createPermission($name);
                $permission->description = $description;
                $auth->add($permission);
            }
        }
        if (($admin = $auth->getRole('admin')) !== null) {
            foreach (array_keys($this->permissions) as $name) {
                $permission = $auth->getPermission($name);
                if ($permission !== null && !$auth->hasChild($admin, $permission)) {
                    $auth->addChild($admin, $permission);
                }
            }
        }
        $auth->invalidateCache();
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        foreach (array_reverse(array_keys($this->permissions)) as $name) {
            if ($permission = $auth->getPermission($name)) {
                $auth->remove($permission);
            }
        }
        $auth->invalidateCache();

        $this->dropTable('{{%service_profile_activity}}');
        $this->dropTable('{{%service_profile_approval}}');
        $this->dropTable('{{%service_profile_review}}');
        $this->dropTable('{{%service_profile_quality_reviewer}}');
        $this->dropTable('{{%service_profile_author}}');
        $this->dropTable('{{%service_profile_section}}');
        $this->dropTable('{{%service_profile}}');
        $this->dropTable('{{%service_profile_template_section}}');
        $this->dropTable('{{%service_profile_template}}');
    }
}
