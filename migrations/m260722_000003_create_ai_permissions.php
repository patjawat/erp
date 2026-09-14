<?php

declare(strict_types=1);

use yii\db\Migration;

class m260722_000003_create_ai_permissions extends Migration
{
    private array $permissions = [
        'ai.chat.use' => 'ใช้ AI Chat',
        'ai.hr.summary' => 'ดูข้อมูลสรุปบุคลากรผ่าน AI',
        'ai.leave.summary' => 'ดูข้อมูลสรุประบบลาผ่าน AI',
        'ai.vehicle.summary' => 'ดูข้อมูลจองรถผ่าน AI',
        'ai.meeting.summary' => 'ดูข้อมูลจองห้องประชุมผ่าน AI',
        'ai.stock.summary' => 'ดูข้อมูลคลังสินค้าผ่าน AI',
        'ai.training.summary' => 'ดูข้อมูลอบรมและดูงานผ่าน AI',
        'ai.document.summary' => 'ดูข้อมูลสารบรรณผ่าน AI',
        'ai.health.summary' => 'ดูข้อมูลสุขภาพผ่าน AI',
        'ai.export.excel' => 'Export Excel ผ่าน AI',
        'ai.scope.leave.all' => 'AI data scope: ดูข้อมูลลาทั้งหมด',
        'ai.scope.leave.department' => 'AI data scope: ดูข้อมูลลาตามหน่วยงาน',
        'ai.scope.stock.all' => 'AI data scope: ดูคลังทั้งหมด',
        'ai.scope.document.all' => 'AI data scope: ดูสารบรรณทั้งหมด',
        'ai.scope.health.all' => 'AI data scope: ดูข้อมูลสุขภาพทั้งหมด',
    ];

    public function safeUp(): void
    {
        $auth = Yii::$app->authManager ?? null;
        if ($auth === null) {
            echo "authManager is not configured; skip AI RBAC permission creation.\n";
            return;
        }

        foreach ($this->permissions as $name => $description) {
            if ($auth->getPermission($name) !== null) {
                continue;
            }

            $permission = $auth->createPermission($name);
            $permission->description = $description;
            $auth->add($permission);
        }
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->authManager ?? null;
        if ($auth === null) {
            return;
        }

        foreach (array_keys($this->permissions) as $name) {
            $permission = $auth->getPermission($name);
            if ($permission !== null) {
                $auth->remove($permission);
            }
        }
    }
}
