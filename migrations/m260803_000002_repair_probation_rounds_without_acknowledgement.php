<?php

use yii\db\Migration;

class m260803_000002_repair_probation_rounds_without_acknowledgement extends Migration
{
    public function safeUp()
    {
        $this->execute(<<<SQL
UPDATE {{%probation_round}} r
LEFT JOIN {{%probation_acknowledgement}} a ON a.round_id = r.id
SET r.status = 'waiting_acknowledgement', r.completed_at = NULL
WHERE r.status = 'completed'
  AND r.month_no IN (1, 2)
  AND a.id IS NULL
SQL);
    }

    public function safeDown()
    {
        $this->execute(<<<SQL
UPDATE {{%probation_round}} r
LEFT JOIN {{%probation_acknowledgement}} a ON a.round_id = r.id
SET r.status = 'completed', r.completed_at = COALESCE(r.completed_at, r.updated_at)
WHERE r.status = 'waiting_acknowledgement'
  AND r.month_no IN (1, 2)
  AND a.id IS NULL
SQL);
    }
}
