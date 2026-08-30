<?php

use yii\db\Migration;

/**
 * ระบบงานมอบหมาย (To Do) — ตารางแกนกลาง
 *
 * แนวคิดหลัก: งานเป็นสมบัติของหน่วยงาน ไม่ใช่ของบัญชีบุคคล
 * owner_unit_id จึงบังคับกรอก ส่วน assignee_emp_id ว่างได้
 * เมื่อผู้รับผิดชอบย้ายหรือลาออก งานยังอยู่กับหน่วยและรอหัวหน้าจ่ายใหม่
 */
class m260830_235220_create_task_tables extends Migration
{
    public function safeUp()
    {
        $audit = function () {
            return [
                'ref' => $this->string(64)->notNull(),
                'created_at' => $this->dateTime()->null(),
                'updated_at' => $this->dateTime()->null(),
                'created_by' => $this->integer()->null(),
                'updated_by' => $this->integer()->null(),
            ];
        };

        $this->createTable('{{%task}}', array_merge([
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull()->comment('ชื่องาน'),
            'detail' => $this->text()->null()->comment('รายละเอียด'),

            // owner_unit_id ต้องเป็น bigint ให้ตรงกับ tree.id
            'owner_unit_id' => $this->bigInteger()->notNull()->comment('หน่วยงานเจ้าของงาน'),
            'assignee_emp_id' => $this->integer()->null()->comment('ผู้รับผิดชอบ ว่างได้เมื่อรอจ่ายงาน'),
            'assigner_emp_id' => $this->integer()->null()->comment('ผู้มอบหมาย'),

            'due_date' => $this->date()->null()->comment('กำหนดเสร็จ'),
            'next_check_date' => $this->date()->null()->comment('จุดตรวจถัดไป ใช้จับงานใกล้ร้อน'),

            'priority' => $this->string(20)->notNull()->defaultValue('normal')->comment('normal | urgent'),
            'status' => $this->string(20)->notNull()->defaultValue('pending')->comment('pending | doing | done | cancelled'),
            'is_waiting' => $this->boolean()->notNull()->defaultValue(false)->comment('ติดรอผู้อื่น แยกจากงานที่ถูกลืม'),
            'postpone_count' => $this->integer()->notNull()->defaultValue(0)->comment('เลื่อนกำหนดมาแล้วกี่ครั้ง'),

            'source_module' => $this->string(32)->null()->comment('dms | manual | pm'),
            'source_id' => $this->string(64)->null()->comment('id ของต้นเรื่อง'),

            // เก็บซ้ำไว้ที่นี่เพื่อให้ query งานใกล้ร้อนไม่ต้อง join task_activity ทุกแถว
            'last_activity_at' => $this->dateTime()->null()->comment('ความเคลื่อนไหวล่าสุด'),

            'completed_at' => $this->dateTime()->null(),
            'completed_by' => $this->integer()->null(),
        ], $audit()));

        $this->createIndex('uq-task-ref', '{{%task}}', 'ref', true);
        // หน้า "งานของฉัน"
        $this->createIndex('idx-task-assignee', '{{%task}}', ['assignee_emp_id', 'status', 'due_date']);
        // หน้าหัวหน้าหน่วย และงานที่รอจ่าย
        $this->createIndex('idx-task-unit', '{{%task}}', ['owner_unit_id', 'status', 'due_date']);
        // ตัวจับงานใกล้ร้อน
        $this->createIndex('idx-task-due', '{{%task}}', ['status', 'due_date']);
        $this->createIndex('idx-task-check', '{{%task}}', ['status', 'next_check_date']);
        // อ้างกลับต้นเรื่อง และกันสร้างงานซ้ำจากหนังสือฉบับเดิม
        $this->createIndex('idx-task-source', '{{%task}}', ['source_module', 'source_id']);

        $this->addForeignKey('fk-task-unit', '{{%task}}', 'owner_unit_id', '{{%tree}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk-task-assignee', '{{%task}}', 'assignee_emp_id', '{{%employees}}', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk-task-assigner', '{{%task}}', 'assigner_emp_id', '{{%employees}}', 'id', 'RESTRICT', 'CASCADE');

        $this->createTable('{{%task_activity}}', [
            'id' => $this->primaryKey(),
            'ref' => $this->string(64)->notNull(),
            'task_id' => $this->integer()->notNull(),
            'emp_id' => $this->integer()->null()->comment('ผู้ทำรายการ'),
            'action' => $this->string(32)->notNull()->comment('create | assign | reassign | start | note | postpone | complete | cancel'),
            'note' => $this->text()->null(),
            'created_at' => $this->dateTime()->null(),
            'created_by' => $this->integer()->null(),
        ]);

        $this->createIndex('uq-task-activity-ref', '{{%task_activity}}', 'ref', true);
        $this->createIndex('idx-task-activity-task', '{{%task_activity}}', ['task_id', 'created_at']);
        $this->addForeignKey('fk-task-activity-task', '{{%task_activity}}', 'task_id', '{{%task}}', 'id', 'CASCADE', 'CASCADE');

        $this->seedRbac();
    }

    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        if ($auth && ($permission = $auth->getPermission(self::PERMISSION_CROSS_UNIT))) {
            $auth->remove($permission);
        }

        $this->dropForeignKey('fk-task-activity-task', '{{%task_activity}}');
        $this->dropTable('{{%task_activity}}');

        $this->dropForeignKey('fk-task-assigner', '{{%task}}');
        $this->dropForeignKey('fk-task-assignee', '{{%task}}');
        $this->dropForeignKey('fk-task-unit', '{{%task}}');
        $this->dropTable('{{%task}}');
    }

    private const PERMISSION_CROSS_UNIT = 'taskAssignCrossUnit';

    /**
     * สิทธิ์มอบหมายงานข้ามหน่วยแบบระบุตัวบุคคล
     *
     * การส่งงานถึง "หน่วยงาน" อื่นโดยไม่ระบุตัวคน ไม่ต้องใช้สิทธิ์นี้
     * ส่วนการเป็นหัวหน้าหน่วย (leader1/leader2) ตรวจแบบพลวัตในโค้ด ไม่ผูกไว้ที่ RBAC
     */
    private function seedRbac(): void
    {
        $auth = Yii::$app->authManager;
        if (!$auth) {
            return;
        }

        $permission = $auth->getPermission(self::PERMISSION_CROSS_UNIT);
        if (!$permission) {
            $permission = $auth->createPermission(self::PERMISSION_CROSS_UNIT);
            $permission->description = 'มอบหมายงานข้ามหน่วยงานโดยระบุตัวผู้รับผิดชอบ';
            $auth->add($permission);
        }

        foreach (['admin', 'document'] as $roleName) {
            $role = $auth->getRole($roleName);
            if ($role && !$auth->hasChild($role, $permission)) {
                $auth->addChild($role, $permission);
            }
        }
    }
}
