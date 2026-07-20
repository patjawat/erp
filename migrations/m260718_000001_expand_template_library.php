<?php

use yii\db\Migration;

/** Adds versioned template variants and structured document blocks. */
class m260718_000001_expand_template_library extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%jd_template}}', 'template_code', $this->string(80)->null()->after('name'));
        $this->addColumn('{{%jd_template}}', 'template_type', $this->string(20)->notNull()->defaultValue('base')->after('position_code'));
        $this->addColumn('{{%jd_template}}', 'parent_template_id', $this->integer()->null()->after('template_type'));
        $this->addColumn('{{%jd_template}}', 'revision_no', $this->integer()->notNull()->defaultValue(1)->after('parent_template_id'));
        $this->addColumn('{{%jd_template}}', 'document_no', $this->string(100)->null()->after('revision_no'));
        $this->addColumn('{{%jd_template}}', 'effective_date', $this->date()->null()->after('document_no'));
        $this->addColumn('{{%jd_template}}', 'lifecycle_status', $this->string(20)->notNull()->defaultValue('draft')->after('effective_date'));
        $this->addColumn('{{%jd_template}}', 'description', $this->text()->null()->after('lifecycle_status'));
        $this->addColumn('{{%jd_template}}', 'ai_generated_at', $this->dateTime()->null()->after('description'));

        $this->createIndex('idx-jd_template-position-type', '{{%jd_template}}', ['position_code', 'template_type']);
        $this->createIndex('idx-jd_template-parent', '{{%jd_template}}', 'parent_template_id');
        $this->createIndex('idx-jd_template-lifecycle', '{{%jd_template}}', 'lifecycle_status');
        $this->addForeignKey('fk-jd_template-parent', '{{%jd_template}}', 'parent_template_id', '{{%jd_template}}', 'id', 'SET NULL', 'CASCADE');

        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;
        $this->createTable('{{%jd_template_block}}', [
            'id' => $this->primaryKey(),
            'template_id' => $this->integer()->notNull(),
            'section_code' => $this->string(40)->notNull(),
            'title' => $this->string(255)->notNull(),
            'block_type' => $this->string(30)->notNull()->defaultValue('prose'),
            'data_json' => $this->text()->null(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_enabled' => $this->tinyInteger(1)->notNull()->defaultValue(1),
            'updated_at' => $this->dateTime()->null(),
        ], $tableOptions);
        $this->createIndex('uq-jd_template_block-code', '{{%jd_template_block}}', ['template_id', 'section_code'], true);
        $this->addForeignKey('fk-jd_template_block-template', '{{%jd_template_block}}', 'template_id', '{{%jd_template}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{%jd_template_block}}');
        $this->dropForeignKey('fk-jd_template-parent', '{{%jd_template}}');
        $this->dropIndex('idx-jd_template-lifecycle', '{{%jd_template}}');
        $this->dropIndex('idx-jd_template-parent', '{{%jd_template}}');
        $this->dropIndex('idx-jd_template-position-type', '{{%jd_template}}');
        foreach (['ai_generated_at', 'description', 'lifecycle_status', 'effective_date', 'document_no', 'revision_no', 'parent_template_id', 'template_type', 'template_code'] as $column) {
            $this->dropColumn('{{%jd_template}}', $column);
        }
    }
}
