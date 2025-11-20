<?php

use yii\db\Migration;

class m251120_020704_add_qty_min_max_to_categorise_table extends Migration
{
   public function safeUp()
    {
        $this->addColumn('{{%categorise}}', 'qty_min', $this->integer()->defaultValue(0));
        $this->addColumn('{{%categorise}}', 'qty_max', $this->integer()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%categorise}}', 'qty_min');
        $this->dropColumn('{{%categorise}}', 'qty_max');
    }
}
