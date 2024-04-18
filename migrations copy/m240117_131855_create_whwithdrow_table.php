<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%whwithdrow}}`.
 */
class m240117_131855_create_whwithdrow_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%whwithdrow}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(255),
            'withdrow_code' => $this->string(255)->comment('รหัส'),
            'withdrow_date' => $this->date()->comment('วันที่เบิก'),
            'withdrow_store' => $this->string(255)->comment('คลังที่ต้องการเบิก'),
            'withdrow_dep' => $this->string(255)->comment('หน่วยงานที่ขอเบิก'),
            'withdrow_hr' => $this->string(255)->comment('เจ้าหน้าที่เบิก'),
            'withdrow_pay' => $this->date()->comment('วันที่จ่าย'),
            'withdrow_status' => $this->string(50)->comment('สถานะ'),
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
        $this->dropTable('{{%whwithdrow}}');
    }
}
