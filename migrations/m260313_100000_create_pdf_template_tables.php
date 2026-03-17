<?php

use yii\db\Migration;

/**
 * Resolution-independent PDF template positioning.
 * Tables: pdf_templates, pdf_template_fields.
 */
class m260313_100000_create_pdf_template_tables extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%pdf_templates}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull()->comment('Template name'),
            'file_path' => $this->string(512)->notNull()->comment('Path to PDF file'),
            'page_width' => $this->decimal(10, 2)->defaultValue(210)->comment('Page width (mm, A4)'),
            'page_height' => $this->decimal(10, 2)->defaultValue(297)->comment('Page height (mm, A4)'),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ]);

        $this->createTable('{{%pdf_template_fields}}', [
            'id' => $this->primaryKey(),
            'template_id' => $this->integer()->notNull(),
            'field_name' => $this->string(100)->notNull()->comment('Field key, e.g. officer_name'),
            'position_json' => $this->json()->notNull()->comment('{page,x_percent,y_percent,width_percent,height_percent,font_size,alignment}'),
            'sort' => $this->integer()->defaultValue(0),
            'created_at' => $this->dateTime(),
            'updated_at' => $this->dateTime(),
        ]);
        $this->createIndex('idx-pdf_template_fields-template_id', '{{%pdf_template_fields}}', 'template_id');
        $this->addForeignKey(
            'fk-pdf_template_fields-template_id',
            '{{%pdf_template_fields}}',
            'template_id',
            '{{%pdf_templates}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-pdf_template_fields-template_id', '{{%pdf_template_fields}}');
        $this->dropTable('{{%pdf_template_fields}}');
        $this->dropTable('{{%pdf_templates}}');
    }
}
