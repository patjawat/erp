<?php

declare(strict_types=1);

use app\modules\approveV2\models\ApproveLevelSetting;
use yii\db\Migration;

/**
 * สิทธิ์และสายอนุมัติของตารางเวร
 *
 *   role `roster` = หัวหน้ากลุ่มการพยาบาล — ดูตารางเวรทุกหน่วย อนุมัติ และประกาศ
 *   หัวหน้าหน่วย (หัวหน้าหอผู้ป่วย) ไม่ต้องมี role — สิทธิ์มาจากผังองค์กร tree.data_json.leader1
 *
 * สายอนุมัติ 2 ขั้น: หัวหน้าหน่วยเสนอ → หัวหน้ากลุ่มการพยาบาลอนุมัติ
 */
final class m260811_000003_seed_roster_rbac_and_approve extends Migration
{
    private const ROLE = 'roster';
    private const SYSTEM = 'roster';

    public function safeUp(): void
    {
        // ── role ──
        $auth = Yii::$app->authManager;
        if ($auth && !$auth->getRole(self::ROLE)) {
            $role = $auth->createRole(self::ROLE);
            $role->description = 'ตารางเวร (หัวหน้ากลุ่มการพยาบาล) — ดูทุกหน่วย อนุมัติ ประกาศ';
            $auth->add($role);
        }

        // ── สายอนุมัติ ──
        $exists = (new \yii\db\Query())->from('{{%approve_level_setting}}')
            ->where(['system' => self::SYSTEM])->exists();
        if ($exists) {
            return;
        }
        $now = date('Y-m-d H:i:s');
        $rows = [
            [1, 'เสนอ', ApproveLevelSetting::TYPE_ORG_LEADER1, null, 2],
            [2, 'อนุมัติ', ApproveLevelSetting::TYPE_ROLE, self::ROLE, null],
        ];
        foreach ($rows as [$level, $label, $type, $value, $orgLevel]) {
            $this->insert('{{%approve_level_setting}}', [
                'system' => self::SYSTEM,
                'level' => $level,
                'label' => $label,
                'title' => 'ตารางเวร',
                'approver_type' => $type,
                'approver_value' => $value,
                'org_node_level' => $orgLevel,
                'sort_order' => $level,
                'active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function safeDown(): void
    {
        $this->delete('{{%approve_level_setting}}', ['system' => self::SYSTEM]);
        $auth = Yii::$app->authManager;
        if ($auth) {
            $role = $auth->getRole(self::ROLE);
            if ($role) {
                $auth->remove($role);
            }
        }
    }
}
