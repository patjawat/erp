<?php

use yii\db\Migration;

/** Creates the hospital-wide default structure for JD templates. */
class m260806_120000_create_jd_structure_default extends Migration
{
    public function safeUp()
    {
        $tableOptions = $this->db->driverName === 'mysql'
            ? 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB'
            : null;

        $this->createTable('{{%jd_structure_default}}', [
            'id' => $this->primaryKey(),
            'section_code' => $this->string(40)->notNull(),
            'title' => $this->string(255)->notNull(),
            'block_type' => $this->string(30)->notNull()->defaultValue('named_items'),
            'help_text' => $this->string(500)->null(),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'is_enabled' => $this->tinyInteger(1)->notNull()->defaultValue(1),
            'is_locked' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->null(),
            'updated_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ], $tableOptions);
        $this->createIndex('uq-jd_structure_default-code', '{{%jd_structure_default}}', 'section_code', true);
        $this->createIndex('idx-jd_structure_default-order', '{{%jd_structure_default}}', 'sort_order');

        $now = date('Y-m-d H:i:s');
        $definitions = \app\modules\jd\models\JdTemplateBlock::definitions();
        $order = 10;
        foreach ($definitions as $code => [$title, $type]) {
            $this->insert('{{%jd_structure_default}}', [
                'section_code' => $code,
                'title' => preg_replace('/^\d+\.\s*/u', '', $title),
                'block_type' => $type,
                'help_text' => null,
                'sort_order' => $order,
                'is_enabled' => 1,
                'is_locked' => $code === 'approval' ? 1 : 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $order += 10;
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%jd_structure_default}}');
    }
}
