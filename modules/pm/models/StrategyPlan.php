<?php

namespace app\modules\pm\models;

class StrategyPlan extends StrategyRecord
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    public static function tableName(): string { return '{{%pm_strategy_plan}}'; }

    public function rules(): array
    {
        return [
            [['code', 'name', 'start_year', 'end_year'], 'required'],
            [['start_year', 'end_year', 'version', 'published_by'], 'integer'],
            [['vision', 'source_note'], 'string'],
            [['code'], 'string', 'max' => 50], [['name'], 'string', 'max' => 255],
            [['status'], 'in', 'range' => array_keys(self::statusList())],
            ['end_year', 'compare', 'compareAttribute' => 'start_year', 'operator' => '>=', 'type' => 'number'],
            [['published_at'], 'safe'],
            [['code', 'version'], 'unique', 'targetAttribute' => ['code', 'version']],
        ];
    }

    public function attributeLabels(): array
    {
        return ['code' => 'รหัสชุดแผน', 'name' => 'ชื่อแผนยุทธศาสตร์', 'version' => 'รุ่น', 'start_year' => 'ปีเริ่มต้น (พ.ศ.)', 'end_year' => 'ปีสิ้นสุด (พ.ศ.)', 'vision' => 'วิสัยทัศน์', 'status' => 'สถานะ', 'source_note' => 'แหล่งที่มา/หมายเหตุ'];
    }

    public static function statusList(): array
    {
        return [self::STATUS_DRAFT => 'ฉบับร่าง', self::STATUS_PUBLISHED => 'ประกาศใช้', self::STATUS_ARCHIVED => 'ยกเลิกใช้งาน'];
    }

    public function richTextAttributes(): array { return ['vision', 'source_note']; }

    public function getMissions() { return $this->hasMany(StrategyMission::class, ['plan_id' => 'id'])->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC]); }
    public function getIndicators() { return $this->hasMany(StrategyIndicator::class, ['plan_id' => 'id']); }
    public function getPrograms() { return $this->hasMany(StrategyProgram::class, ['plan_id' => 'id']); }
    public function isEditable(): bool { return $this->status === self::STATUS_DRAFT; }

    /**
     * แผนยุทธศาสตร์ที่ใช้อยู่ในปัจจุบัน
     * เลือกฉบับที่ประกาศใช้และครอบคลุมปีงบประมาณปัจจุบันก่อน ถ้ายังไม่ประกาศใช้จึงใช้ฉบับร่าง
     * และถ้าไม่มีแผนใดครอบคลุมปีนี้เลย ให้ใช้ชุดล่าสุดเพื่อไม่ให้หน้าจอว่างเปล่า
     */
    public static function current(): ?self
    {
        $year = (int) date('Y') + 543 + ((int) date('n') >= 10 ? 1 : 0);
        $covering = static fn (string $status) => self::find()
            ->where(['status' => $status])
            ->andWhere(['<=', 'start_year', $year])
            ->andWhere(['>=', 'end_year', $year])
            ->orderBy(['version' => SORT_DESC]);

        return $covering(self::STATUS_PUBLISHED)->one()
            ?: $covering(self::STATUS_DRAFT)->one()
            ?: self::find()->orderBy(['start_year' => SORT_DESC, 'version' => SORT_DESC])->one();
    }

    /** ปีงบประมาณทั้งหมดที่แผนนี้ครอบคลุม */
    public function fiscalYears(): array { return range((int) $this->start_year, (int) $this->end_year); }
    public function coversYear(int $year): bool { return $year >= (int) $this->start_year && $year <= (int) $this->end_year; }
}
