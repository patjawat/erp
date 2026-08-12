<?php

declare(strict_types=1);

use yii\db\Migration;

final class m260803_000005_add_owner_text_to_pm_strategy_program extends Migration
{
    public function safeUp(): void { $this->addColumn('{{%pm_strategy_program}}','owner_text',$this->string(255)->null()->after('owner_org_id')); }
    public function safeDown(): void { $this->dropColumn('{{%pm_strategy_program}}','owner_text'); }
}
