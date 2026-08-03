<?php

use yii\db\Migration;

class m260803_000001_make_probation_acknowledgement_per_round extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%probation_acknowledgement}}', 'round_id', $this->integer()->null()->after('case_id'));
        $this->execute(<<<SQL
UPDATE {{%probation_acknowledgement}} a
INNER JOIN {{%probation_round}} r ON r.case_id = a.case_id AND r.month_no = 3
SET a.round_id = r.id
SQL);
        $this->alterColumn('{{%probation_acknowledgement}}', 'round_id', $this->integer()->notNull());

        $this->createIndex('idx-probation_ack-case', '{{%probation_acknowledgement}}', 'case_id');
        $this->dropIndex('case_id', '{{%probation_acknowledgement}}');
        $this->createIndex('uq-probation_ack-round', '{{%probation_acknowledgement}}', 'round_id', true);
        $this->addForeignKey(
            'fk-probation_ack-round',
            '{{%probation_acknowledgement}}',
            'round_id',
            '{{%probation_round}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk-probation_ack-round', '{{%probation_acknowledgement}}');
        $this->dropIndex('uq-probation_ack-round', '{{%probation_acknowledgement}}');
        $this->delete('{{%probation_acknowledgement}}', 'round_id NOT IN (SELECT id FROM {{%probation_round}} WHERE month_no = 3)');
        $this->dropColumn('{{%probation_acknowledgement}}', 'round_id');
        $this->createIndex('case_id', '{{%probation_acknowledgement}}', 'case_id', true);
        $this->dropIndex('idx-probation_ack-case', '{{%probation_acknowledgement}}');
    }
}
