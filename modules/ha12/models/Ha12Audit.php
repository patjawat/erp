<?php

namespace app\modules\ha12\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * ร่องรอยเหตุการณ์กำกับดูแล HA12-PCT
 *
 * @property int $id
 * @property string $entity_type
 * @property int|null $entity_id
 * @property string $action
 * @property string|null $detail
 * @property string $ref
 * @property string|null $created_at
 * @property int|null $created_by
 */
class Ha12Audit extends ActiveRecord
{
    public static function actionLabels(): array
    {
        return [
            'create' => 'สร้าง', 'update' => 'แก้ไข', 'delete' => 'ลบ', 'restore' => 'กู้คืน',
            'publish' => 'เผยแพร่', 'unpublish' => 'ยกเลิกเผยแพร่', 'close' => 'ปิดรอบ', 'reopen' => 'เปิดรอบใหม่',
            'import' => 'นำเข้าข้อมูล',
        ];
    }

    public static function entityLabels(): array
    {
        return [
            'round' => 'รอบประเมิน', 'assessment' => 'ผลประเมิน', 'review' => 'การทบทวน',
            'med' => 'รายงานยา', 'mrec' => 'เวชระเบียน', 'indicator' => 'ตัวชี้วัด', 'import' => 'นำเข้า',
        ];
    }

    public static function tableName(): string
    {
        return '{{%ha12_audit}}';
    }

    /** บันทึกเหตุการณ์ (ไม่ throw — กันพังการทำงานหลัก) */
    public static function log(string $entityType, ?int $entityId, string $action, ?string $detail = null): void
    {
        $uid = null;
        try {
            if (Yii::$app->has('user') && Yii::$app->user->id !== null) {
                $uid = (int) Yii::$app->user->id;
            }
        } catch (\Throwable $e) {
            // ไม่มี user component (เช่น console) — ปล่อยเป็น null
        }
        try {
            $row = new self([
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'action' => $action,
                'detail' => $detail !== null ? mb_substr($detail, 0, 500) : null,
                'ref' => Yii::$app->security->generateRandomString(24),
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $uid,
            ]);
            $row->save(false);
        } catch (\Throwable $e) {
            Yii::error('Ha12Audit::log ' . $e->getMessage(), 'ha12');
        }
    }

    public function actionLabel(): string
    {
        return self::actionLabels()[$this->action] ?? $this->action;
    }

    public function entityLabel(): string
    {
        return self::entityLabels()[$this->entity_type] ?? $this->entity_type;
    }
}
