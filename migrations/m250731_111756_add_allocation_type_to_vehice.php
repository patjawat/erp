<?php

use yii\db\Migration;

class m250731_111756_add_allocation_type_to_vehice extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = '{{%vehicle}}';
        $schema = Yii::$app->db->getTableSchema($table, true);

            if (!isset($schema->columns['is_shared'])) {
                  $this->addColumn($table, 'is_shared', $this->boolean()->notNull()->defaultValue(false)->comment('การจัดสรรร่วม'));
            }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
      
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250731_111756_add_allocation_type_to_vehice cannot be reverted.\n";

        return false;
    }
    */
}
