<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260726_000012_add_checkout_outstanding_amount extends Migration
{
    public function safeUp(): void
    {
        $this->addColumn('{{%housing_checkout}}', 'outstanding_amount', $this->decimal(12, 2)->notNull()->defaultValue(0)->after('water_meter_value'));
    }

    public function safeDown(): void
    {
        $this->dropColumn('{{%housing_checkout}}', 'outstanding_amount');
    }
}
