<?php

use yii\db\Migration;

/** Keeps legacy roster-preparation periods separate from calculated salary runs. */
class m260901_201000_separate_payroll_preparation_period extends Migration
{
    public function safeUp()
    {
        $this->update('{{%payroll_period}}', ['period_type' => 'preparation'], ['status' => 'draft', 'period_type' => 'salary']);
    }

    public function safeDown()
    {
        $this->update('{{%payroll_period}}', ['period_type' => 'salary'], ['status' => 'draft', 'period_type' => 'preparation']);
    }
}
