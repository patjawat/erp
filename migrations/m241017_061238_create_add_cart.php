<?php

use yii\db\Migration;

/**
 * Class m241017_061238_create_add_cart
 */
class m241017_061238_create_add_cart extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        if ($this->db->schema->getTableSchema('{{%cart_main}}', true) === null) {
            $this->createTable('{{%cart_main}}', [
                'id' => $this->string(255)->notNull(),
                'user_id' => $this->integer(11),
                'name' => $this->string(255)->notNull(),
                'value' => $this->text()->notNull(),
                'status' => $this->tinyInteger(1)->notNull()->defaultValue(0),
            ]);
            $this->addPrimaryKey('pk_cart_main_id', '{{%cart_main}}', 'id');
        }

        if ($this->db->schema->getTableSchema('{{%cart_sub}}', true) === null) {
        $this->createTable('{{%cart_sub}}', [
            'id' => $this->string(255)->notNull(),
            'user_id' => $this->integer(11),
            'name' => $this->string(255)->notNull(),
            'value' => $this->text()->notNull(),
            'status' => $this->tinyInteger(1)->notNull()->defaultValue(0),
        ]);
        $this->addPrimaryKey('pk_cart_sub_id', '{{%cart_sub}}', 'id');
    }
    
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%cart_main}}', true) !== null) {
            $this->dropTable('{{%cart_main}}');
        } else {
            echo "Table 'cart' does not exist. Skipping drop.\n";
        }
        if ($this->db->schema->getTableSchema('{{%cart_sub}}', true) !== null) {
            $this->dropTable('{{%cart_sub}}');
        } else {
            echo "Table 'cart' does not exist. Skipping drop.\n";
        }
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m241017_061238_create_add_cart cannot be reverted.\n";

        return false;
    }
    */
}
