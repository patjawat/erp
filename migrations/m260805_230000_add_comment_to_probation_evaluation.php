<?php

use yii\db\Migration;

class m260805_230000_add_comment_to_probation_evaluation extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%probation_evaluation}}', 'comment', $this->text()->null()->after('percent_score'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%probation_evaluation}}', 'comment');
    }
}
