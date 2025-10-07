<?php

use yii\db\Migration;

class m251007_051359_add_work_type_to_employee extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = '{{%employees}}';
        $schema = Yii::$app->db->getTableSchema($table, true);

        if (!isset($schema->columns['work_type'])) {
            $this->addColumn(
                '{{%employees}}',
                'work_type',
                $this->string(20)
                    ->notNull()
                    ->defaultValue('normal')
                    ->comment('ประเภทเวลาทำงาน: normal=ปกติ, shift=เวร 8 ชั่วโมง')
                    ->after('id')
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {

        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251007_051359_add_work_type_to_employee cannot be reverted.\n";

        return false;
    }
    */
}
