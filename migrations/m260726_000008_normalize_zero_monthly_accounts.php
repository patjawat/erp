<?php

use yii\db\Migration;

final class m260726_000008_normalize_zero_monthly_accounts extends Migration
{
    public function safeUp(): void
    {
        $this->update(
            '{{%housing_monthly_account}}',
            ['payment_status' => 'paid', 'balance_amount' => 0],
            ['and', ['status' => 'saved'], ['total_amount' => 0]]
        );
    }

    public function safeDown(): void
    {
        $this->update(
            '{{%housing_monthly_account}}',
            ['payment_status' => 'unpaid'],
            ['and', ['status' => 'saved'], ['total_amount' => 0]]
        );
    }
}
