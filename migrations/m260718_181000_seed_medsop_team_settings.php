<?php

use yii\db\Migration;

class m260718_181000_seed_medsop_team_settings extends Migration
{
    public function safeUp()
    {
        $now = date('Y-m-d H:i:s');
        $this->execute(<<<SQL
INSERT INTO {{%medsop_team_setting}}
    (team_group_id, code, active, created_at, updated_at)
SELECT
    t.id,
    LEFT(UPPER(SUBSTRING_INDEX(TRIM(t.title), ' ', 1)), 20),
    1,
    '{$now}',
    '{$now}'
FROM {{%team_group}} t
WHERE t.deleted_at IS NULL
ON DUPLICATE KEY UPDATE
    code = VALUES(code),
    updated_at = VALUES(updated_at)
SQL);
    }

    public function safeDown()
    {
        return false;
    }
}
