<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%asset_disbursement}}`.
 */
class m231220_134906_create_asset_disbursement_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%asset_disbursement}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'wathed_date' => $this->string(255)->comment('วันที่ต้องการ'),
            'bill_number' => $this->string(255)->comment('ใบเบิกเลขที่'),
            'dep_id' => $this->integer()->comment('หน่วยงานที่เบิก'),
            'data_json' => $this->json(),
            'updated_at' => $this->dateTime()->comment('วันเวลาแก้ไข'),
            'created_at' => $this->dateTime()->comment('วันเวลาสร้าง'), 
            'created_by' => $this->integer()->comment('ผู้สร้าง'),
            'updated_by' => $this->integer()->comment('ผู้แก้ไข')
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%asset_disbursement}}');
    }
}
