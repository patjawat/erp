<?php

use yii\db\Migration;

/**
 * Adds optional GFMIS budget structure code to asset.
 */
class m260513_090000_add_gfmis_to_asset extends Migration
{
    public function safeUp()
    {
        $table = '{{%asset}}';
        $schema = $this->db->getSchema()->getTableSchema($table, true);
        if ($schema !== null && $schema->getColumn('gfmis') !== null) {
            return;
        }

        $this->addColumn($table, 'gfmis', $this->string(100)->null()->comment('รหัสโครงสร้างงบประมาณ(GFMIS)')->after('code'));
    }

    public function safeDown()
    {
        $table = '{{%asset}}';
        $schema = $this->db->getSchema()->getTableSchema($table, true);
        if ($schema !== null && $schema->getColumn('gfmis') !== null) {
            $this->dropColumn($table, 'gfmis');
        }
    }
}
