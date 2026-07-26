<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260726_000013_link_invoice_monthly_account extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%housing_invoice}}', 'monthly_account_id', $this->integer()->after('invoice_no'));
        $this->createIndex('ux_housing_invoice_monthly_account', '{{%housing_invoice}}', 'monthly_account_id', true);
        $this->addForeignKey('fk_housing_invoice_monthly_account', '{{%housing_invoice}}', 'monthly_account_id', '{{%housing_monthly_account}}', 'id', 'RESTRICT', 'CASCADE');
    }

    public function safeDown(): void
    {
        $this->dropForeignKey('fk_housing_invoice_monthly_account', '{{%housing_invoice}}');
        $this->dropIndex('ux_housing_invoice_monthly_account', '{{%housing_invoice}}');
        $this->dropColumn('{{%housing_invoice}}', 'monthly_account_id');
    }
}
