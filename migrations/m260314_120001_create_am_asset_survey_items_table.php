<?php

use yii\db\Migration;

/**
 * Creates table `am_asset_survey_items` (per-asset survey results).
 */
class m260314_120001_create_am_asset_survey_items_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%am_asset_survey_items}}', [
            'id' => $this->primaryKey(),
            'survey_id' => $this->integer()->notNull()->comment('FK am_asset_surveys.id'),
            'asset_id' => $this->integer()->null()->comment('FK asset.id when FOUND'),
            'scanned_asset_number' => $this->string(255)->notNull()->comment('หมายเลขที่สแกน/นำเข้า'),
            'found_status' => $this->string(50)->notNull()->comment('FOUND|NOT_FOUND|NEW_ASSET'),
            'location_match' => $this->boolean()->null()->comment('true=ตรง false=ไม่ตรง null=ไม่ตรวจ'),
            'department_match' => $this->boolean()->null()->comment('true=ตรง false=ไม่ตรง null=ไม่ตรวจ'),
            'survey_location_id' => $this->integer()->null()->comment('สถานที่ที่สำรวจ (optional)'),
            'survey_department_id' => $this->integer()->null()->comment('หน่วยงานที่สำรวจ (tree.id)'),
            'survey_method' => $this->string(20)->notNull()->comment('WEB|CSV|QRCODE'),
            'remark' => $this->text()->null()->comment('หมายเหตุ'),
            'scanned_by' => $this->integer()->null()->comment('ผู้สำรวจ'),
            'scanned_at' => $this->dateTime()->null()->comment('วันเวลาสำรวจ'),
        ]);

        $this->createIndex('idx_survey_items_survey_id', '{{%am_asset_survey_items}}', 'survey_id');
        $this->createIndex('idx_survey_items_asset_id', '{{%am_asset_survey_items}}', 'asset_id');
        $this->createIndex('idx_survey_items_scanned', '{{%am_asset_survey_items}}', 'scanned_asset_number');
        $this->createIndex('idx_survey_items_found_status', '{{%am_asset_survey_items}}', 'found_status');
        $this->createIndex('idx_survey_items_survey_asset', '{{%am_asset_survey_items}}', ['survey_id', 'asset_id']);

        $this->addForeignKey(
            'fk_survey_items_survey',
            '{{%am_asset_survey_items}}',
            'survey_id',
            '{{%am_asset_surveys}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'fk_survey_items_asset',
            '{{%am_asset_survey_items}}',
            'asset_id',
            '{{%asset}}',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_survey_items_asset', '{{%am_asset_survey_items}}');
        $this->dropForeignKey('fk_survey_items_survey', '{{%am_asset_survey_items}}');
        $this->dropTable('{{%am_asset_survey_items}}');
    }
}
