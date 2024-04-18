<?php

use yii\db\Schema;

class m160323_150101_tbl_tree extends \yii\db\Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }
        
        $this->createTable('tbl_tree', [
            'id' => $this->primaryKey(),
            'root' => $this->integer(11),
            'lft' => $this->integer(11)->notNull(),
            'rgt' => $this->integer(11)->notNull(),
            'lvl' => $this->smallInteger(5)->notNull(),
            'name' => $this->string(60)->notNull(),
            'icon' => $this->string(255),
            'icon_type' => $this->smallInteger(1)->notNull()->defaultValue(1),
            'active' => $this->smallInteger(1)->notNull()->defaultValue(1),
            'selected' => $this->smallInteger(1)->notNull()->defaultValue(0),
            'disabled' => $this->smallInteger(1)->notNull()->defaultValue(0),
            'readonly' => $this->smallInteger(1)->notNull()->defaultValue(0),
            'visible' => $this->smallInteger(1)->notNull()->defaultValue(1),
            'collapsed' => $this->smallInteger(1)->notNull()->defaultValue(0),
            'movable_u' => $this->smallInteger(1)->notNull()->defaultValue(1),
            'movable_d' => $this->smallInteger(1)->notNull()->defaultValue(1),
            'movable_l' => $this->smallInteger(1)->notNull()->defaultValue(1),
            'movable_r' => $this->smallInteger(1)->notNull()->defaultValue(1),
            'removable' => $this->smallInteger(1)->notNull()->defaultValue(1),
            'removable_all' => $this->smallInteger(1)->notNull()->defaultValue(0),
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('tbl_tree');
    }
}
