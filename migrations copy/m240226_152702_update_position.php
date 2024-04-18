<?php

use yii\db\Migration;

/**
 * Class m240226_152702_update_position
 */
class m240226_152702_update_position extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        Yii::$app->db->pdo->exec('delete FROM `categorise` WHERE name IN("position_type","position_group","position_name")');
        Yii::$app->db->pdo->exec(file_get_contents(__DIR__ . '/positions/position_type.sql'));
        Yii::$app->db->pdo->exec(file_get_contents(__DIR__ . '/positions/position_group.sql'));
        Yii::$app->db->pdo->exec(file_get_contents(__DIR__ . '/positions/position_name.sql'));

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240226_152702_update_position cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240226_152702_update_position cannot be reverted.\n";

        return false;
    }
    */
}
